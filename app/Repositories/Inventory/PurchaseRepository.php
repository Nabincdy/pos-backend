<?php

namespace App\Repositories\Inventory;

use App\Contracts\Inventory\PurchaseInterface;
use App\Models\Inventory\PaymentRecord;
use App\Models\Inventory\Purchase;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

use function accountSetting;

class PurchaseRepository implements PurchaseInterface
{
    /**
     * generate purchase code
     *
     * @return string
     */
    public function generatePurchaseCode(): string
    {
        return companySetting()->purchase.Str::padLeft(Purchase::max('id') + 1, 3, 0);
    }

    /**
     * return all data of the resource.
     *
     * @param  Request  $request
     * @return Collection
     */
    public function all(Request $request): Collection
    {
        return Purchase::with('supplierLedger', 'purchaseParticulars')
            ->withSum(['paymentRecords' => function ($query) {
                $query->where('is_cancelled', 0);
            }], 'paid_amount')
            ->filterData(request()->all())
            ->latest()
            ->get();
    }

    /**
     * return paginated data of the resource.
     *
     * @param  Request  $request
     * @return LengthAwarePaginator
     */
    public function list(Request $request): LengthAwarePaginator
    {
        return Purchase::with('supplierLedger', 'purchaseParticulars')
            ->withSum(['paymentRecords' => function ($query) {
                $query->where('is_cancelled', 0);
            }], 'paid_amount')
            ->filterData($request->all())
            ->latest()
            ->paginate($request->limit ?? 10);
    }

    /**
     * store newly created resource in storage
     *
     * @param  array  $data
     * @return mixed
     */
    public function store(array $data)
    {
        $purchase = DB::transaction(function () use ($data) {
            $purchase = Purchase::create($data + [
                'fiscal_year_id' => runningFiscalYear()->id,
                'create_user_id' => auth()->id(),
            ]);
            //save purchase transaction to journal
            $journal = $purchase->journal()->create([
                'fiscal_year_id' => $purchase->fiscal_year_id,
                'journal_no' => $purchase->invoice_no,
                'date' => $purchase->purchase_date,
                'user_id' => auth()->id(),
                'remarks' => $purchase->remarks,
            ]);
            //purchase particulars
            foreach ($data['purchaseParticulars'] as $particular) {
                $purchaseParticular = $purchase->purchaseParticulars()->create($particular);
                //quantity in stock
                $purchase->productStocks()->create([
                    'fiscal_year_id' => $purchase->fiscal_year_id,
                    'product_id' => $particular['product_id'],
                    'expiry_date' => $particular['expiry_date'] ?? null,
                    'en_expiry_date' => $particular['en_expiry_date'] ?? null,
                    'batch_no' => $particular['batch_no'] ?? null,
                    'unit_id' => $particular['unit_id'],
                    'warehouse_id' => $particular['warehouse_id'],
                    'quantity' => $particular['quantity'],
                    'rate' => $particular['product_rate'],
                    'type' => 'In',
                    'date' => $purchase->purchase_date,
                    'remarks' => "Purchase - $purchase->invoice_no - ".($purchase->supplierLedger->ledger_name ?? ''),
                ]);
                //debit for purchase account
                $journal->journalParticulars()->create([
                    'ledger_id' => accountSetting()->purchase_ledger_id,
                    'date' => $journal->date,
                    'dr_amount' => $purchaseParticular->amount_excl_tax ?? 0,
                    'cr_amount' => 0,
                    'remarks' => 'To-'.($purchase->supplierLedger->ledger_name ?? ''),
                ]);
                //debit for tax amount if included
                if ($particular['purchase_tax_amount'] > 0) {
                    $journal->journalParticulars()->create([
                        'ledger_id' => $purchaseParticular->purchaseTax->ledger_id ?? null,
                        'date' => $journal->date,
                        'dr_amount' => $particular['purchase_tax_amount'],
                        'cr_amount' => 0,
                        'remarks' => 'To-'.($purchase->supplierLedger->ledger_name ?? ''),
                    ]);
                }
            }
            //credit for account payable/supplier
            $journal->journalParticulars()->create([
                'ledger_id' => $purchase->supplier_ledger_id,
                'date' => $journal->date,
                'dr_amount' => 0,
                'cr_amount' => $purchase->purchaseParticulars->sum('total_amount'),
                'remarks' => 'To-Purchase Account',
            ]);
            if ($data['payment_type'] !== 'Credit' && $data['paid_amount'] > 0) {
                $paymentRecord = $purchase->paymentRecords()->create([
                    'fiscal_year_id' => $purchase->fiscal_year_id,
                    'invoice_no' => companySetting()->supplier_payment.Str::padLeft(PaymentRecord::max('id') + 1, 3, 0),
                    'supplier_ledger_id' => $purchase->supplier_ledger_id,
                    'payment_method' => $data['payment_type'],
                    'cash_bank_ledger_id' => $data['payment_type'] == 'Bank' ? $data['bank_account_ledger_id'] : accountSetting()->cash_ledger_id,
                    'payment_date' => $purchase->purchase_date,
                    'paid_amount' => $data['paid_amount'],
                    'remarks' => $data['remarks'],
                    'create_user_id' => auth()->id(),
                ]);
                //debit for account payable/supplier ledger after payment
                $journal->journalParticulars()->create([
                    'ledger_id' => $purchase->supplier_ledger_id,
                    'date' => $journal->date,
                    'dr_amount' => $paymentRecord->paid_amount,
                    'cr_amount' => 0,
                    'remarks' => 'By-'.($paymentRecord->cashBankLedger->ledger_name ?? ''),
                ]);
                //credit for cash/bank ledger
                $journal->journalParticulars()->create([
                    'ledger_id' => $paymentRecord->cash_bank_ledger_id,
                    'date' => $journal->date,
                    'dr_amount' => 0,
                    'cr_amount' => $paymentRecord->paid_amount,
                    'remarks' => 'By-'.($purchase->supplierLedger->ledger_name ?? ''),
                ]);
            }

            return $purchase;
        });

        return $purchase->load('supplierLedger', 'purchaseParticulars')->loadSum('paymentRecords', 'paid_amount');
    }

    public function show($id): Purchase
    {
        return Purchase::findOrFail($id)->loadSum(['paymentRecords' => function ($query) {
            $query->where('is_cancelled', 0);
        }], 'paid_amount')
            ->load('supplierLedger', 'createUser', 'purchaseParticulars.product', 'purchaseParticulars.unit');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  Request  $request
     * @param $id
     */
    public function destroy(Request $request, $id): void
    {
        $purchase = Purchase::findOrFail($id);

        DB::transaction(function () use ($request, $purchase) {
            $purchase->purchaseParticulars()->update([
                'is_cancelled' => 1,
            ]);
            $purchase->paymentRecords()->update([
                'is_cancelled' => 1,
                'cancel_user_id' => auth()->id(),
                'cancelled_reason' => $request->cancel_reason,
            ]);
            $purchase->journal()->update([
                'is_cancelled' => 1,
            ]);

            $purchase->journal->journalParticulars()->update([
                'is_cancelled' => 1,
            ]);
            $purchase->productStocks()->delete();
            $purchase->update([
                'is_cancelled' => 1,
                'cancel_user_id' => auth()->id(),
                'cancelled_reason' => $request->cancel_reason,
            ]);
        });
    }
}
