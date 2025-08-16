<?php

namespace App\Http\Controllers\Api\Inventory;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\Inventory\PaymentRecord\StorePaymentRecordRequest;
use App\Http\Resources\Inventory\PaymentRecordResource;
use App\Models\Inventory\PaymentRecord;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class PaymentRecordController extends Controller
{
    public function paymentRecordCode()
    {
        return companySetting()->supplier_payment.Str::padLeft(PaymentRecord::max('id') + 1, 3, 0);
    }

    public function allPaymentRecords(Request $request)
    {
        $this->checkAuthorization('paymentRecord_access');

        $paymentRecords = PaymentRecord::with('supplierLedger', 'createUser')
            ->filterData($request->all())
            ->latest('payment_date')
            ->get();

        return PaymentRecordResource::collection($paymentRecords);
    }

    public function index(Request $request)
    {
        $this->checkAuthorization('paymentRecord_access');

        $paymentRecords = PaymentRecord::with('supplierLedger', 'createUser')
            ->filterData($request->all())
            ->latest('payment_date')
            ->paginate($request->limit ?? 10);

        return PaymentRecordResource::collection($paymentRecords);
    }

    public function store(StorePaymentRecordRequest $request)
    {
        $this->checkAuthorization('paymentRecord_create');

        $paymentRecords = collect();

        DB::transaction(function () use ($request, $paymentRecords) {
            foreach ($request->input('purchases') as $purchase) {
                if ($purchase['paid_amount'] > 0) {
                    $paymentRecord = PaymentRecord::create([
                        'invoice_no' => $this->paymentRecordCode(),
                        'fiscal_year_id' => runningFiscalYear()->id,
                        'purchase_id' => $purchase['purchase_id'],
                        'supplier_ledger_id' => $request->input('supplier_ledger_id'),
                        'payment_method' => $request->input('payment_method'),
                        'cash_bank_ledger_id' => $request->input('payment_method') == 'Bank' ? $request->input('bank_account_ledger_id') : \accountSetting()->cash_ledger_id,
                        'payment_date' => $request->input('payment_date'),
                        'paid_amount' => $purchase['paid_amount'] ?? 0,
                        'remarks' => $request->input('remarks'),
                        'create_user_id' => auth()->id(),
                    ]);
                    $paymentRecords->push($paymentRecord);
                    //save to journal
                    $journal = $paymentRecord->journal()->create([
                        'fiscal_year_id' => $paymentRecord->fiscal_year_id,
                        'journal_no' => $paymentRecord->invoice_no,
                        'date' => $paymentRecord->payment_date,
                        'user_id' => auth()->id(),
                        'remarks' => $paymentRecord->remarks,
                    ]);
                    //debit for account payable/supplier ledger after payment
                    $journal->journalParticulars()->create([
                        'ledger_id' => $paymentRecord->supplier_ledger_id,
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
                        'remarks' => 'By-'.($paymentRecord->supplierLedger->ledger_name ?? ''),
                    ]);
                }
            }
        });

        return response()->json([
            'data' => PaymentRecordResource::collection($paymentRecords),
            'message' => 'Supplier Payment Added Successfully',
        ], 201);
    }

    public function show(PaymentRecord $paymentRecord)
    {
        $this->checkAuthorization('paymentRecord_access');

        return PaymentRecordResource::make($paymentRecord->load('supplierLedger', 'createUser', 'cashBankLedger'));
    }

    public function update(Request $request, PaymentRecord $paymentRecord)
    {
        //
    }

    public function destroy(Request $request, PaymentRecord $paymentRecord)
    {
        $request->validate([
            'cancel_reason' => ['required', 'max:255'],
        ]);

        DB::transaction(function () use ($request, $paymentRecord) {
            $paymentRecord->journal()->update([
                'is_cancelled' => 1,
            ]);

            $paymentRecord->journal?->journalParticulars()->update([
                'is_cancelled' => 1,
            ]);
            $paymentRecord->update([
                'is_cancelled' => 1,
                'cancel_user_id' => auth()->id(),
                'cancelled_reason' => $request->cancel_reason,
            ]);
        });

        return response()->json([
            'data' => '',
            'message' => 'Supplier Payment Cancelled Successfully',
        ]);
    }
}
