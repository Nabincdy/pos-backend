<?php

namespace App\Http\Controllers\Api\Inventory;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\Inventory\Sale\StoreSaleRequest;
use App\Http\Resources\Inventory\SaleResource;
use App\Models\Inventory\ReceiptRecord;
use App\Models\Inventory\Sale;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

use function accountSetting;

class SaleController extends Controller
{
    public function salesCode()
    {
        return companySetting()->sales.Str::padLeft(Sale::max('id') + 1, 3, 0);
    }

    public function allSales(Request $request)
    {
        $this->checkAuthorization('sale_access');

        $sales = Sale::with('clientLedger', 'saleParticulars')
            ->withSum(['receiptRecords' => function ($query) {
                $query->where('is_cancelled', 0);
            }], 'amount')
            ->filterData($request->all())
            ->latest()
            ->get()->filter(function ($sale) use ($request) {
                if ($request->sales_payment_status == 'due') {
                    return ($sale->saleParticulars->sum('total_amount') - $sale->receipt_records_sum_amount) > 0;
                }

                return true;
            });

        return SaleResource::collection($sales);
    }

    public function index(Request $request)
    {
        $this->checkAuthorization('sale_access');

        $sales = Sale::with('clientLedger', 'saleParticulars')
            ->withSum(['receiptRecords' => function ($query) {
                $query->where('is_cancelled', 0);
            }], 'amount')
            ->filterData($request->all())
            ->latest()
            ->paginate($request->limit ?? 10);

        return SaleResource::collection($sales);
    }

    public function store(StoreSaleRequest $request)
    {
        $this->checkAuthorization('sale_create');

        $sale = DB::transaction(function () use ($request) {
            $sale = Sale::create($request->validated() + [
                'fiscal_year_id' => runningFiscalYear()->id,
                'create_user_id' => auth()->id(),
            ]);
            //save sales transaction to journal
            $journal = $sale->journal()->create([
                'fiscal_year_id' => $sale->fiscal_year_id,
                'journal_no' => $sale->invoice_no,
                'date' => $sale->sales_date,
                'user_id' => auth()->id(),
                'remarks' => $sale->remarks,
            ]);
            //sales particular
            foreach ($request->validated()['saleParticulars'] as $particular) {
                $saleParticular = $sale->saleParticulars()->create([
                    'product_id' => $particular['product_id'],
                    'unit_id' => $particular['unit_id'],
                    'warehouse_id' => $particular['warehouse_id'] ?? $request->input('warehouse_id'),
                    'quantity' => $particular['quantity'],
                    'rate' => $particular['rate'],
                    'sales_tax_id' => $particular['sales_tax_id'],
                    'sales_tax_amount' => ! empty($particular['sales_tax_id']) && empty($particular['sales_tax_amount']) ? round(($particular['rate'] * $particular['quantity'] - $particular['discount_amount']) * $particular['sales_tax_rate'] / 100, 2) : ($particular['sales_tax_amount'] ?? 0),
                    'discount_amount' => $particular['discount_amount'],
                ]);
                //remove quantity from stock
                $sale->productStocks()->create([
                    'fiscal_year_id' => $sale->fiscal_year_id,
                    'product_id' => $particular['product_id'],
                    'expiry_date' => $particular['expiry_date'] ?? null,
                    'en_expiry_date' => $particular['en_expiry_date'] ?? null,
                    'batch_no' => $particular['batch_no'] ?? null,
                    'unit_id' => $particular['unit_id'],
                    'warehouse_id' => $particular['warehouse_id'] ?? $request->input('warehouse_id'),
                    'quantity' => -$particular['quantity'],
                    'rate' => $particular['rate'],
                    'type' => 'Out',
                    'date' => $sale->sales_date,
                    'en_date' => $sale->en_sales_date,
                    'remarks' => "Sales - $sale->invoice_no - ".($sale->clientLedger->ledger_name ?? ''),
                ]);
                //credit for sales account
                $journal->journalParticulars()->create([
                    'ledger_id' => accountSetting()->sales_ledger_id,
                    'date' => $journal->date,
                    'dr_amount' => 0,
                    'cr_amount' => $saleParticular->amount_excl_tax ?? 0,
                    'remarks' => 'To-'.($sale->clientLedger->ledger_name ?? ''),
                ]);
                //credit for tax amount if included
                if ($saleParticular->sales_tax_amount > 0) {
                    $journal->journalParticulars()->create([
                        'ledger_id' => $saleParticular->salesTax->ledger_id ?? null,
                        'date' => $journal->date,
                        'dr_amount' => 0,
                        'cr_amount' => $saleParticular->sales_tax_amount ?? 0,
                        'remarks' => 'To-'.($sale->clientLedger->ledger_name ?? ''),
                    ]);
                }
            }
            //debit for account receivable/client
            $journal->journalParticulars()->create([
                'ledger_id' => $sale->client_ledger_id,
                'date' => $journal->date,
                'dr_amount' => $sale->saleParticulars->sum('total_amount'),
                'cr_amount' => 0,
                'remarks' => 'To-Sales Account',
            ]);
            if ($request->input('payment_type') !== 'Credit' && $request->input('paid_amount') > 0) {
                $receiptRecord = $sale->receiptRecords()->create([
                    'fiscal_year_id' => $sale->fiscal_year_id,
                    'invoice_no' => companySetting()->client_payment.Str::padLeft(ReceiptRecord::max('id') + 1, 3, 0),
                    'payment_method' => $request->input('payment_type'),
                    'client_ledger_id' => $sale->client_ledger_id,
                    'cash_bank_ledger_id' => $request->input('payment_type') == 'Bank' ? $request->input('bank_account_ledger_id') : accountSetting()->cash_ledger_id,
                    'receipt_date' => $sale->sales_date,
                    'amount' => $request->input('paid_amount'),
                    'remarks' => $request->input('payment_remarks') ?? $request->input('remarks'),
                    'create_user_id' => auth()->id(),
                ]);
                //credit for account receivable/client ledger after payment
                $journal->journalParticulars()->create([
                    'ledger_id' => $sale->client_ledger_id,
                    'date' => $journal->date,
                    'dr_amount' => 0,
                    'cr_amount' => $receiptRecord->amount,
                    'remarks' => 'By-'.($receiptRecord->cashBankLedger->ledger_name ?? ''),
                ]);
                //debit for cash/bank ledger
                $journal->journalParticulars()->create([
                    'ledger_id' => $receiptRecord->cash_bank_ledger_id,
                    'date' => $journal->date,
                    'dr_amount' => $receiptRecord->amount,
                    'cr_amount' => 0,
                    'remarks' => 'By-'.($sale->clientLedger->ledger_name ?? ''),
                ]);
            }

            return $sale;
        });

        return response()->json([
            'data' => new SaleResource(
                $sale->load('clientLedger', 'saleParticulars')->loadSum('receiptRecords', 'amount')
            ),
            'message' => 'Sales Created Successfully',
        ], 201);
    }

    public function show(Sale $sale)
    {
        $this->checkAuthorization('sale_access');

        $sale->loadSum(['receiptRecords' => function ($query) {
            $query->where('is_cancelled', 0);
        }], 'amount')
            ->load('clientLedger', 'createUser', 'saleParticulars.product', 'saleParticulars.unit');

        return SaleResource::make($sale);
    }

    public function destroy(Request $request, Sale $sale)
    {
        $this->checkAuthorization('sale_delete');

        $request->validate([
            'cancel_reason' => ['required', 'max:255'],
        ]);

        DB::transaction(function () use ($request, $sale) {
            $sale->saleParticulars()->update([
                'is_cancelled' => 1,
            ]);
            $sale->receiptRecords()->update([
                'is_cancelled' => 1,
                'cancel_user_id' => auth()->id(),
                'cancelled_reason' => $request->cancel_reason,
            ]);
            $sale->productStocks()->delete();
            $sale->journal()->update([
                'is_cancelled' => 1,
            ]);

            $sale->journal->journalParticulars()->update([
                'is_cancelled' => 1,
            ]);
            $sale->update([
                'is_cancelled' => 1,
                'cancel_user_id' => auth()->id(),
                'cancelled_reason' => $request->cancel_reason,
            ]);
        });

        return response()->json([
            'data' => '',
            'message' => 'Sale Cancelled Successfully',
        ]);
    }
}
