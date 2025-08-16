<?php

namespace App\Http\Controllers\Api\Inventory;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\Inventory\Quotation\StoreQuotationRequest;
use App\Http\Resources\Inventory\QuotationResource;
use App\Models\Inventory\Quotation;
use App\Models\Inventory\ReceiptRecord;
use App\Models\Inventory\Sale;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class QuotationController extends Controller
{
    public function quotationCode()
    {
        return companySetting()->quotation.Str::padLeft(Quotation::max('id') + 1, 3, 0);
    }

    public function allQuotations(Request $request)
    {
        $this->checkAuthorization('quotation_access');

        $quotations = Quotation::with('clientLedger', 'createUser', 'quotationParticulars')
            ->filterData($request->all())
            ->orderByDesc('quotation_date')
            ->get();

        return QuotationResource::collection($quotations);
    }

    public function index(Request $request)
    {
        $this->checkAuthorization('quotation_access');

        $quotations = Quotation::with('clientLedger', 'createUser', 'quotationParticulars')
            ->filterData($request->all())
            ->orderByDesc('quotation_date')
            ->paginate($request->limit ?? 10);

        return QuotationResource::collection($quotations);
    }

    public function store(StoreQuotationRequest $request)
    {
        $this->checkAuthorization('quotation_create');

        $quotation = DB::transaction(function () use ($request) {
            $quotation = Quotation::create($request->validated() + [
                'fiscal_year_id' => runningFiscalYear()->id,
                'create_user_id' => auth()->id(),
            ]);
            //quotation particular
            foreach ($request->validated()['quotationParticulars'] as $particular) {
                $quotation->quotationParticulars()->create([
                    'product_id' => $particular['product_id'],
                    'unit_id' => $particular['unit_id'],
                    'warehouse_id' => $particular['warehouse_id'],
                    'quantity' => $particular['quantity'],
                    'rate' => $particular['rate'],
                    'sales_tax_id' => $particular['sales_tax_id'],
                    'sales_tax_amount' => ! empty($particular['sales_tax_id']) && empty($particular['sales_tax_amount']) ? round(($particular['rate'] * $particular['quantity'] - $particular['discount_amount']) * $particular['sales_tax_rate'] / 100, 2) : $particular['sales_tax_amount'],
                    'discount_amount' => $particular['discount_amount'],
                ]);
            }

            return $quotation;
        });

        return response()->json([
            'data' => new QuotationResource($quotation->load('clientLedger', 'createUser', 'quotationParticulars')),
            'message' => 'Quotation Added Successfully',
        ], 201);
    }

    public function show(Quotation $quotation)
    {
        $this->checkAuthorization('quotation_access');

        return QuotationResource::make($quotation->load('clientLedger', 'createUser', 'quotationParticulars.product', 'quotationParticulars.unit'));
    }

    public function update(Request $request, Quotation $quotation)
    {
        $this->checkAuthorization('quotation_edit');
    }

    public function destroy(Quotation $quotation)
    {
        $this->checkAuthorization('quotation_delete');

        if ($quotation->is_converted_to_sale) {
            abort(400, 'Converted quotation can not be deleted');
        }

        $quotation->quotationParticulars()->delete();
        $quotation->delete();

        return response()->json([
            'data' => '',
            'message' => 'Quotation Deleted Successfully',
        ]);
    }

    public function convertToSale(Request $request, Quotation $quotation)
    {
        $this->checkAuthorization('quotation_edit');

        $request->validate([
            'date' => ['required'],
            'payment_type' => ['required', 'in:Credit,Cash,Bank'],
            'bank_account_ledger_id' => ['required_if:payment_type,Bank'],
            'paid_amount' => ['required_if:payment_type,Cash,Bank', 'numeric'],
            'remarks' => ['nullable'],
        ]);

        if ($quotation->is_converted_to_sale) {
            abort(400, 'Quotation already converted to sale');
        }

        DB::transaction(function () use ($request, $quotation) {
            $sale = Sale::create([
                'fiscal_year_id' => runningFiscalYear()->id,
                'invoice_no' => companySetting()->sales.Str::padLeft(Sale::max('id') + 1, 3, 0),
                'sales_date' => $request->input('date'),
                'payment_type' => $request->input('payment_type'),
                'client_ledger_id' => $quotation->client_ledger_id,
                'create_user_id' => auth()->id(),
                'remarks' => $request->input('remarks'),
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
            foreach ($quotation->quotationParticulars as $particular) {
                $saleParticular = $sale->saleParticulars()->create([
                    'product_id' => $particular->product_id,
                    'unit_id' => $particular->unit_id,
                    'warehouse_id' => $particular->warehouse_id,
                    'quantity' => $particular->quantity,
                    'rate' => $particular->rate,
                    'sales_tax_id' => $particular->sales_tax_id,
                    'sales_tax_amount' => ! empty($particular->sales_tax_id) && empty($particular->sales_tax_amount) ? round(($particular->rate * $particular->quantity - $particular->discount_amount) * $particular->sales_tax_rate / 100, 2) : $particular->sales_tax_amount,
                    'discount_amount' => $particular->discount_amount,
                ]);
                //remove quantity from stock
                $sale->productStocks()->create([
                    'fiscal_year_id' => $sale->fiscal_year_id,
                    'product_id' => $particular->product_id,
                    'unit_id' => $particular->unit_id,
                    'warehouse_id' => $particular->warehouse_id,
                    'quantity' => -$particular->quantity,
                    'rate' => $particular->rate,
                    'type' => 'Out',
                    'date' => $sale->sales_date,
                    'remarks' => "Sales - $sale->invoice_no - ".($sale->clientLedger->ledger_name ?? ''),
                ]);
                //credit for sales account
                $journal->journalParticulars()->create([
                    'ledger_id' => \accountSetting()->sales_ledger_id,
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
                        'cr_amount' => $saleParticular->sales_tax_amount,
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
                    'cash_bank_ledger_id' => $request->input('payment_type') == 'Bank' ? $request->input('bank_account_ledger_id') : \accountSetting()->cash_ledger_id,
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

            //update quotation status
            $quotation->update([
                'is_converted_to_sale' => 1,
            ]);
        });

        return response()->json([
            'data' => QuotationResource::make($quotation->load('clientLedger', 'createUser', 'quotationParticulars')),
            'message' => 'Quotation Converted Successfully',
        ]);
    }
}
