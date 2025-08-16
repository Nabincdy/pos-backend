<?php

namespace App\Http\Controllers\Api\Inventory;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\Inventory\PurchaseReturn\StorePurchaseReturnRequest;
use App\Http\Resources\Inventory\PurchaseReturnResource;
use App\Models\Inventory\PurchaseReturn;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class PurchaseReturnController extends Controller
{
    public function purchaseReturnCode()
    {
        return companySetting()->purchase_return.Str::padLeft(PurchaseReturn::max('id') + 1, 3, 0);
    }

    public function allPurchaseReturns(Request $request)
    {
        $this->checkAuthorization('purchaseReturn_access');

        $purchaseReturns = PurchaseReturn::with('supplierLedger', 'purchaseReturnParticulars')
            ->filterData($request->all())
            ->latest('return_date')
            ->get();

        return PurchaseReturnResource::collection($purchaseReturns);
    }

    public function index(Request $request)
    {
        $this->checkAuthorization('purchaseReturn_access');

        $purchaseReturns = PurchaseReturn::with('supplierLedger', 'purchaseReturnParticulars')
            ->filterData($request->all())
            ->latest('return_date')
            ->paginate($request->limit ?? 10);

        return PurchaseReturnResource::collection($purchaseReturns);
    }

    public function store(StorePurchaseReturnRequest $request)
    {
        $this->checkAuthorization('purchaseReturn_create');

        $purchaseReturn = DB::transaction(function () use ($request) {
            $purchaseReturn = PurchaseReturn::create($request->validated() + [
                'fiscal_year_id' => runningFiscalYear()->id,
                'create_user_id' => auth()->id(),
            ]);
            //save purchase transaction to journal
            $journal = $purchaseReturn->journal()->create([
                'fiscal_year_id' => $purchaseReturn->fiscal_year_id,
                'journal_no' => $purchaseReturn->invoice_no,
                'date' => $purchaseReturn->return_date,
                'user_id' => auth()->id(),
                'remarks' => $purchaseReturn->remarks,
            ]);
            //purchase particulars
            foreach ($request->validated()['purchaseReturnParticulars'] as $particular) {
                $purchaseReturnParticular = $purchaseReturn->purchaseReturnParticulars()->create($particular);
                //quantity in stock
                $purchaseReturn->productStocks()->create([
                    'fiscal_year_id' => $purchaseReturn->fiscal_year_id,
                    'product_id' => $particular['product_id'],
                    'unit_id' => $particular['unit_id'],
                    'warehouse_id' => $particular['warehouse_id'],
                    'quantity' => -$particular['quantity'],
                    'rate' => $particular['rate'],
                    'type' => 'Out',
                    'date' => $purchaseReturn->return_date,
                    'remarks' => "Purchase Return - $purchaseReturn->invoice_no - ".($purchaseReturn->supplierLedger->ledger_name ?? ''),
                ]);
                //credit for purchase account
                $journal->journalParticulars()->create([
                    'ledger_id' => accountSetting()->purchase_ledger_id,
                    'date' => $journal->date,
                    'dr_amount' => 0,
                    'cr_amount' => $purchaseReturnParticular->amount_excl_tax ?? 0,
                    'remarks' => 'To-'.($purchase->supplierLedger->ledger_name ?? ''),
                ]);
                //credit for tax amount if included
                if ($particular['purchase_tax_amount'] > 0) {
                    $journal->journalParticulars()->create([
                        'ledger_id' => $purchaseReturnParticular->purchaseTax->ledger_id ?? null,
                        'date' => $journal->date,
                        'dr_amount' => 0,
                        'cr_amount' => $particular['purchase_tax_amount'],
                        'remarks' => 'To-'.($purchase->supplierLedger->ledger_name ?? ''),
                    ]);
                }
            }
            //debit for account payable/supplier
            $journal->journalParticulars()->create([
                'ledger_id' => $purchaseReturn->supplier_ledger_id,
                'date' => $journal->date,
                'dr_amount' => $purchaseReturn->purchaseReturnParticulars->sum('total_amount'),
                'cr_amount' => 0,
                'remarks' => 'To-Purchase Account',
            ]);

            return $purchaseReturn;
        });

        $purchaseReturn->load('supplierLedger', 'purchaseReturnParticulars');

        return response()->json([
            'data' => new PurchaseReturnResource($purchaseReturn),
            'message' => 'Purchase Return Created Successfully',
        ], 201);
    }

    public function show(PurchaseReturn $purchaseReturn)
    {
        $this->checkAuthorization('purchaseReturn_access');

        $purchaseReturn->load('supplierLedger', 'createUser', 'purchaseReturnParticulars.product', 'purchaseReturnParticulars.unit');

        return PurchaseReturnResource::make($purchaseReturn);
    }

    public function update(Request $request, PurchaseReturn $purchaseReturn)
    {
        $this->checkAuthorization('purchaseReturn_edit');
    }

    public function destroy(PurchaseReturn $purchaseReturn)
    {
        $this->checkAuthorization('purchaseReturn_delete');

        $purchaseReturn->journal->journalParticulars()->delete();
        $purchaseReturn->journal()->delete();
        $purchaseReturn->purchaseReturnParticulars()->delete();
        $purchaseReturn->productStocks()->delete();
        $purchaseReturn->delete();

        return response()->json([
            'data' => '',
            'message' => 'Purchase Return Deleted Successfully',
        ]);
    }
}
