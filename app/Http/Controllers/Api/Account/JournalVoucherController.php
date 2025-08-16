<?php

namespace App\Http\Controllers\Api\Account;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\Account\JournalVoucher\StoreJournalVoucherRequest;
use App\Http\Resources\Account\JournalVoucherResource;
use App\Models\Account\JournalVoucher;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class JournalVoucherController extends Controller
{
    public function journalVoucherCode()
    {
        return companySetting()->journal_voucher.Str::padLeft(JournalVoucher::max('id') + 1, 3, 0);
    }

    public function index()
    {
        $this->checkAuthorization('journalVoucher_access');

        $journalVouchers = JournalVoucher::with(['createUser', 'journal' => function ($query) {
            $query->with('journalParticulars');
            $query->withSum(['journalParticulars' => function ($sub_q) {
                $sub_q->where('is_cancelled', 0);
            }], 'dr_amount');
        }])
            ->filterData()
            ->orderByDesc('voucher_date')
            ->get();

        return JournalVoucherResource::collection($journalVouchers);
    }

    public function store(StoreJournalVoucherRequest $request)
    {
        $this->checkAuthorization('journalVoucher_create');

        if (collect($request->validated()['journalVoucherParticulars'])->sum('dr_amount') !== collect($request->validated()['journalVoucherParticulars'])->sum('cr_amount')) {
            return response()->json([
                'message' => 'Dr Amount & Cr Amount must be equal',
            ], 400);
        }

        $journalVoucher = DB::transaction(function () use ($request) {
            $journalVoucher = JournalVoucher::create($request->validated() + [
                'fiscal_year_id' => runningFiscalYear()->id,
                'create_user_id' => auth()->id(),
            ]);

            $journal = $journalVoucher->journal()->create([
                'fiscal_year_id' => $journalVoucher->fiscal_year_id,
                'journal_no' => $journalVoucher->voucher_no,
                'date' => $journalVoucher->voucher_date,
                'user_id' => auth()->id(),
                'remarks' => $journalVoucher->remarks,
            ]);

            foreach ($request->validated()['journalVoucherParticulars'] as $particular) {
                $journal->journalParticulars()->create([
                    'ledger_id' => $particular['ledger_id'],
                    'date' => $journal->date,
                    'dr_amount' => $particular['dr_amount'] ?? 0,
                    'cr_amount' => $particular['cr_amount'] ?? 0,
                    'remarks' => $particular['remarks'],
                ]);
            }

            return $journalVoucher;
        });

        return response()->json([
            'data' => new JournalVoucherResource(
                $journalVoucher->load(['createUser', 'journal' => function ($query) {
                    $query->withSum('journalParticulars', 'dr_amount');
                }])
            ),
            'message' => 'Journal Voucher Created Successfully',
        ], 201);
    }

    public function show(JournalVoucher $journalVoucher)
    {
        $this->checkAuthorization('journalVoucher_access');

        $journalVoucher->load(['createUser', 'journal' => function ($query) {
            $query->withSum(['journalParticulars' => function ($sub_q) {
                $sub_q->where('is_cancelled', 0);
            }], 'dr_amount');
            $query->with('journalParticulars.ledger');
        }]);

        return JournalVoucherResource::make($journalVoucher);
    }

    public function destroy(Request $request, JournalVoucher $journalVoucher)
    {
        $this->checkAuthorization('journalVoucher_delete');

        $request->validate([
            'cancelled_reason' => 'required',
        ]);

        DB::transaction(function () use ($request, $journalVoucher) {
            $journalVoucher->update([
                'is_cancelled' => 1,
                'cancel_user_id' => auth()->id(),
                'cancelled_reason' => $request->input('cancelled_reason'),
            ]);

            $journalVoucher->journal()->update([
                'is_cancelled' => 1,
            ]);

            $journalVoucher->journal->journalParticulars()->update([
                'is_cancelled' => 1,
            ]);
        });

        return response()->json([
            'data' => '',
            'message' => 'Journal Voucher Cancelled successfully',
        ]);
    }
}
