<?php

namespace App\Http\Controllers\Api\Account;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\Account\ReceiptVoucher\StoreReceiptVoucherRequest;
use App\Http\Resources\Account\ReceiptVoucherResource;
use App\Models\Account\ReceiptVoucher;
use App\Models\Setting\AccountSetting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class ReceiptVoucherController extends Controller
{
    public function receiptVoucherCode()
    {
        return companySetting()->receipt_voucher.Str::padLeft(ReceiptVoucher::max('id') + 1, 3, 0);
    }

    public function index()
    {
        $this->checkAuthorization('receiptVoucher_access');

        $receiptVouchers = ReceiptVoucher::with(['createUser', 'journal' => function ($query) {
            $query->with('journalParticulars');
            $query->withSum(['journalParticulars' => function ($sub_q) {
                $sub_q->where('is_cancelled', 0);
            }], 'dr_amount');
        }])->filterData()->orderByDesc('receipt_date')->get();

        return ReceiptVoucherResource::collection($receiptVouchers);
    }

    public function store(StoreReceiptVoucherRequest $request)
    {
        $this->checkAuthorization('receiptVoucher_create');

        $data = DB::transaction(function () use ($request) {
            $receiptVoucher = ReceiptVoucher::create($request->validated() + [
                'fiscal_year_id' => runningFiscalYear()->id,
                'create_user_id' => auth()->id(),
            ]);

            $journal = $receiptVoucher->journal()->create([
                'fiscal_year_id' => $receiptVoucher->fiscal_year_id,
                'journal_no' => $receiptVoucher->receipt_no,
                'date' => $receiptVoucher->receipt_date,
                'user_id' => auth()->id(),
            ]);

            //credit for income head or receivable(party name)

            foreach ($request->validated()['voucherParticulars'] as $particular) {
                $journal->journalParticulars()->create([
                    'ledger_id' => $particular['ledger_id'],
                    'date' => $journal->date,
                    'dr_amount' => 0,
                    'cr_amount' => $particular['amount'],
                ]);
            }
            //account setting
            $accountSetting = AccountSetting::first();
            //debit for bank/cash ledger
            $journal->journalParticulars()->create([
                'ledger_id' => $request->input('receipt_method') == 'Bank' ? $request->input('bank_account_ledger_id') : $accountSetting->cash_ledger_id,
                'date' => $journal->date,
                'dr_amount' => $journal->journalParticulars->sum('cr_amount'),
                'cr_amount' => 0,
            ]);

            return $receiptVoucher;
        });

        return response()->json([
            'data' => new ReceiptVoucherResource($data->load(['createUser', 'journal' => function ($query) {
                $query->withSum('journalParticulars', 'dr_amount');
            }])),
            'message' => 'Receipt Voucher Created Successfully',
        ], 201);
    }

    public function show(ReceiptVoucher $receiptVoucher)
    {
        $this->checkAuthorization('receiptVoucher_access');

        $receiptVoucher->load(['createUser', 'journal' => function ($query) {
            $query->withSum(['journalParticulars' => function ($sub_q) {
                $sub_q->where('is_cancelled', 0);
            }], 'dr_amount');
            $query->with('journalParticulars.ledger');
        }]);

        return ReceiptVoucherResource::make($receiptVoucher);
    }

    public function destroy(Request $request, ReceiptVoucher $receiptVoucher)
    {
        $this->checkAuthorization('receiptVoucher_delete');
        $request->validate([
            'cancelled_reason' => 'required',
        ]);

        DB::transaction(function () use ($request, $receiptVoucher) {
            $receiptVoucher->update([
                'is_cancelled' => 1,
                'cancel_user_id' => auth()->id(),
                'cancelled_reason' => $request->input('cancelled_reason'),
            ]);

            $receiptVoucher->journal()->update([
                'is_cancelled' => 1,
            ]);

            $receiptVoucher->journal->journalParticulars()->update([
                'is_cancelled' => 1,
            ]);
        });

        return response()->json([
            'data' => '',
            'message' => 'Receipt Voucher Cancelled Successfully',
        ]);
    }
}
