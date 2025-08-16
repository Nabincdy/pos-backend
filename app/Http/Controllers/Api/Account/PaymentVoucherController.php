<?php

namespace App\Http\Controllers\Api\Account;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\Account\PaymentVoucher\StorePaymentVoucherRequest;
use App\Http\Resources\Account\PaymentVoucherResource;
use App\Models\Account\PaymentVoucher;
use App\Models\Setting\AccountSetting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class PaymentVoucherController extends Controller
{
    public function paymentVoucherCode()
    {
        return companySetting()->payment_voucher.Str::padLeft(PaymentVoucher::max('id') + 1, 3, 0);
    }

    public function index()
    {
        $this->checkAuthorization('paymentVoucher_access');

        $paymentVouchers = PaymentVoucher::with(['createUser', 'journal' => function ($query) {
            $query->with('journalParticulars');
            $query->withSum(['journalParticulars' => function ($sub_q) {
                $sub_q->where('is_cancelled', 0);
            }], 'cr_amount');
        }])
            ->filterData()
            ->orderByDesc('payment_date')
            ->get();

        return PaymentVoucherResource::collection($paymentVouchers);
    }

    public function store(StorePaymentVoucherRequest $request)
    {
        $this->checkAuthorization('paymentVoucher_create');

        $paymentVoucher = DB::transaction(function () use ($request) {
            $paymentVoucher = PaymentVoucher::create($request->validated() + [
                'fiscal_year_id' => runningFiscalYear()->id,
                'create_user_id' => auth()->id(),
            ]);

            $journal = $paymentVoucher->journal()->create([
                'fiscal_year_id' => $paymentVoucher->fiscal_year_id,
                'journal_no' => $paymentVoucher->voucher_no,
                'date' => $paymentVoucher->payment_date,
                'user_id' => auth()->id(),
            ]);

            //debit for party or expense ledgers
            foreach ($request->validated()['voucherParticulars'] as $particular) {
                $journal->journalParticulars()->create([
                    'ledger_id' => $particular['ledger_id'],
                    'date' => $journal->date,
                    'dr_amount' => $particular['amount'],
                    'cr_amount' => 0,
                ]);
            }

            //account setting
            $accountSetting = AccountSetting::first();
            //credit for cash/bank account
            $journal->journalParticulars()->create([
                'ledger_id' => $request->input('payment_method') == 'Bank' ? $request->input('bank_account_ledger_id') : $accountSetting->cash_ledger_id,
                'date' => $journal->date,
                'dr_amount' => 0,
                'cr_amount' => $journal->journalParticulars->sum('dr_amount'),
            ]);

            return $paymentVoucher;
        });

        return response()->json([
            'data' => new PaymentVoucherResource(
                $paymentVoucher->load(['createUser', 'journal' => function ($query) {
                    $query->withSum('journalParticulars', 'cr_amount');
                }])
            ),
            'message' => 'Payment Voucher Created Successfully',
        ], 201);
    }

    public function show(PaymentVoucher $paymentVoucher)
    {
        $this->checkAuthorization('paymentVoucher_access');

        $paymentVoucher->load(['createUser', 'journal' => function ($query) {
            $query->withSum(['journalParticulars' => function ($sub_q) {
                $sub_q->where('is_cancelled', 0);
            }], 'cr_amount');
            $query->with('journalParticulars.ledger');
        }]);

        return PaymentVoucherResource::make($paymentVoucher);
    }

    public function destroy(Request $request, PaymentVoucher $paymentVoucher)
    {
        $this->checkAuthorization('paymentVoucher_delete');

        $request->validate([
            'cancelled_reason' => 'required',
        ]);

        DB::transaction(function () use ($request, $paymentVoucher) {
            $paymentVoucher->update([
                'is_cancelled' => 1,
                'cancel_user_id' => auth()->id(),
                'cancelled_reason' => $request->input('cancelled_reason'),
            ]);

            $paymentVoucher->journal()->update([
                'is_cancelled' => 1,
            ]);

            $paymentVoucher->journal->journalParticulars()->update([
                'is_cancelled' => 1,
            ]);
        });

        return response()->json([
            'data' => '',
            'message' => 'Payment Voucher Cancel successfully',
        ]);
    }
}
