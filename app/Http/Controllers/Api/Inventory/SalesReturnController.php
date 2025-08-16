<?php

namespace App\Http\Controllers\Api\Inventory;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\Inventory\SalesReturn\StoreSalesReturnRequest;
use App\Http\Resources\Inventory\SalesReturnResource;
use App\Models\Inventory\SalesReturn;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class SalesReturnController extends Controller
{
    public function salesReturnCode()
    {
        return companySetting()->sales_return.Str::padLeft(SalesReturn::max('id') + 1, 3, 0);
    }

    public function allSalesReturns(Request $request)
    {
        $this->checkAuthorization('salesReturn_access');

        $salesReturns = SalesReturn::with('clientLedger', 'salesReturnParticulars')
            ->filterData($request->all())
            ->latest('return_date')
            ->get();

        return SalesReturnResource::collection($salesReturns);
    }

    public function index(Request $request)
    {
        $this->checkAuthorization('salesReturn_access');

        $salesReturns = SalesReturn::with('clientLedger', 'salesReturnParticulars')
            ->filterData($request->all())
            ->latest('return_date')
            ->paginate($request->limit ?? 10);

        return SalesReturnResource::collection($salesReturns);
    }

    public function store(StoreSalesReturnRequest $request)
    {
        $this->checkAuthorization('salesReturn_create');

        $salesReturn = DB::transaction(function () use ($request) {
            $salesReturn = SalesReturn::create($request->validated() + [
                'fiscal_year_id' => runningFiscalYear()->id,
                'create_user_id' => auth()->id(),
            ]);
            //save sales transaction to journal
            $journal = $salesReturn->journal()->create([
                'fiscal_year_id' => $salesReturn->fiscal_year_id,
                'journal_no' => $salesReturn->invoice_no,
                'date' => $salesReturn->return_date,
                'user_id' => auth()->id(),
                'remarks' => $salesReturn->remarks,
            ]);
            //sales particular
            foreach ($request->validated()['salesReturnParticulars'] as $particular) {
                $salesReturnParticular = $salesReturn->salesReturnParticulars()->create($particular);
                //add quantity on stock
                $salesReturn->productStocks()->create([
                    'fiscal_year_id' => $salesReturn->fiscal_year_id,
                    'product_id' => $particular['product_id'],
                    'unit_id' => $particular['unit_id'],
                    'warehouse_id' => $particular['warehouse_id'],
                    'quantity' => $particular['quantity'],
                    'rate' => $particular['rate'],
                    'type' => 'In',
                    'date' => $salesReturn->return_date,
                    'remarks' => "Sales Return - $salesReturn->invoice_no - ".($salesReturn->clientLedger->ledger_name ?? ''),
                ]);
                //debit for sales account
                $journal->journalParticulars()->create([
                    'ledger_id' => \accountSetting()->sales_ledger_id,
                    'date' => $journal->date,
                    'dr_amount' => $salesReturnParticular->amount_excl_tax ?? 0,
                    'cr_amount' => 0,
                    'remarks' => 'To-'.($salesReturn->clientLedger->ledger_name ?? ''),
                ]);
                //debit for tax amount if included
                if ($particular['sales_tax_amount'] > 0) {
                    $journal->journalParticulars()->create([
                        'ledger_id' => $salesReturnParticular->salesTax->ledger_id ?? null,
                        'date' => $journal->date,
                        'dr_amount' => $particular['sales_tax_amount'],
                        'cr_amount' => 0,
                        'remarks' => 'To-'.($salesReturn->clientLedger->ledger_name ?? ''),
                    ]);
                }
            }
            //credit for account receivable/client
            $journal->journalParticulars()->create([
                'ledger_id' => $salesReturn->client_ledger_id,
                'date' => $journal->date,
                'dr_amount' => 0,
                'cr_amount' => $salesReturn->salesReturnParticulars->sum('total_amount'),
                'remarks' => 'To-Sales Account',
            ]);

            return $salesReturn;
        });

        return response()->json([
            'data' => new SalesReturnResource(
                $salesReturn->load('clientLedger', 'salesReturnParticulars')
            ),
            'message' => 'Sales Return Created Successfully',
        ], 201);
    }

    public function show(SalesReturn $salesReturn)
    {
        $this->checkAuthorization('salesReturn_access');

        $salesReturn->load('clientLedger', 'createUser', 'salesReturnParticulars.product', 'salesReturnParticulars.unit');

        return SalesReturnResource::make($salesReturn);
    }

    public function update(Request $request, SalesReturn $salesReturn)
    {
        $this->checkAuthorization('salesReturn_edit');
    }

    public function destroy(SalesReturn $salesReturn)
    {
        $this->checkAuthorization('salesReturn_delete');

        $salesReturn->journal->journalParticulars()->delete();
        $salesReturn->journal()->delete();
        $salesReturn->salesReturnParticulars()->delete();
        $salesReturn->productStocks()->delete();
        $salesReturn->delete();

        return response()->json([
            'data' => '',
            'message' => 'Sales Return Deleted Successfully',
        ]);
    }
}
