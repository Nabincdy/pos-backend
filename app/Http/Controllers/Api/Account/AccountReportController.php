<?php

namespace App\Http\Controllers\Api\Account;

use App\Http\Controllers\Controller;
use App\Http\Resources\Account\JournalParticularResource;
use App\Http\Resources\Account\Report\BalanceSheetResource;
use App\Http\Resources\Account\Report\ProfitLossResource;
use App\Http\Resources\Account\Report\TrialBalanceResource;
use App\Models\Account\AccountHead;
use App\Models\Account\JournalParticular;
use App\Models\Account\Ledger;
use App\Models\Account\LedgerGroup;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class AccountReportController extends Controller
{
    public function generalLedger(Request $request)
    {
        $validated = $request->validate([
            'ledger_id' => ['required', Rule::exists('ledgers', 'id')->withoutTrashed()],
            'from_date' => ['required'],
            'to_date' => ['required', 'after_or_equal:from_date'],
        ]);

        $ledger = Ledger::with([
            'accountOpeningBalances' => function ($query) {
                $query->filterData();
            }, ])->find($validated['ledger_id']);

        $journalParticulars = JournalParticular::with('journal', 'ledger')->filterData($validated)->latest('date')->get();

        return response()->json([
            'data' => JournalParticularResource::collection($journalParticulars),
            'opening_balance' => [
                'dr_amount' => (float) $ledger->accountOpeningBalances->sum('dr_amount'),
                'cr_amount' => (float) $ledger->accountOpeningBalances->sum('cr_amount'),
                'balance' => (float) $ledger->accountOpeningBalances->sum('dr_amount') - (float) $ledger->accountOpeningBalances->sum('cr_amount'),
            ],
        ]);
    }

    public function balanceSheet(Request $request)
    {
        $validated = $request->validate([
            'from_date' => ['required'],
            'to_date' => ['required', 'after_or_equal:from_date'],
        ]);

        $assetHead = AccountHead::where('name', 'Assets')->first();
        $liabilityHead = AccountHead::where('name', 'Liabilities')->first();

        $ledgerGroups = LedgerGroup::with(['ledgerGroups.ledgers' => function ($query) use ($validated) {
            $query->withSum(['journalParticulars as sum_dr_amount' => function ($q) use ($validated) {
                $q->filterData($validated);
            }], 'dr_amount');
            $query->withSum(['journalParticulars as sum_cr_amount' => function ($q) use ($validated) {
                $q->filterData($validated);
            }], 'cr_amount');
        }])
            ->whereIn('account_head_id', [$assetHead->id, $liabilityHead->id])
            ->whereNull('ledger_group_id')
            ->get();

        return response()->json([
            'assets' => BalanceSheetResource::collection($ledgerGroups->where('account_head_id', $assetHead->id)),
            'liabilities' => BalanceSheetResource::collection($ledgerGroups->where('account_head_id', $liabilityHead->id)),
        ]);
    }

    public function profitLoss(Request $request)
    {
        $validated = $request->validate([
            'from_date' => ['required'],
            'to_date' => ['required', 'after_or_equal:from_date'],
        ]);

        $incomeHead = AccountHead::where('name', 'Income')->first();
        $expenseHead = AccountHead::where('name', 'Expense')->first();

        $ledgerGroups = LedgerGroup::with(['ledgerGroups.ledgers' => function ($query) use ($validated) {
            $query->withSum(['journalParticulars as sum_dr_amount' => function ($q) use ($validated) {
                $q->filterData($validated);
            }], 'dr_amount');
            $query->withSum(['journalParticulars as sum_cr_amount' => function ($q) use ($validated) {
                $q->filterData($validated);
            }], 'cr_amount');
        }])
            ->whereIn('account_head_id', [$incomeHead->id, $expenseHead->id])
            ->whereNull('ledger_group_id')
            ->get();

        return response()->json([
            'income' => ProfitLossResource::collection($ledgerGroups->where('account_head_id', $incomeHead->id)),
            'expense' => ProfitLossResource::collection($ledgerGroups->where('account_head_id', $expenseHead->id)),
        ]);
    }

    public function dayBook(Request $request)
    {
        $validated = $request->validate([
            'from_date' => ['required'],
            'to_date' => ['required', 'after_or_equal:from_date'],
        ]);

        $dayBooks = JournalParticular::with('journal', 'ledger')->filterData($validated)->latest('date')->get();

        return JournalParticularResource::collection($dayBooks);
    }

    public function trialBalance(Request $request)
    {
        $validated = $request->validate([
            'from_date' => ['required'],
            'to_date' => ['required', 'after_or_equal:from_date'],
        ]);

        $ledgerGroups = LedgerGroup::with(['ledgerGroups.ledgers' => function ($query) use ($validated) {
            $query->whereNull('ledger_id');
            $this->trialBalanceParticular($query, $validated);
            $query->with(['subLedgers' => function ($q) use ($validated) {
                $this->trialBalanceParticular($q, $validated);
            }]);
        }])
            ->whereNull('ledger_group_id')
            ->orderBy('account_head_id')
            ->get();

        return TrialBalanceResource::collection($ledgerGroups);
    }

    private function trialBalanceParticular($query, $validated)
    {
        $query->withSum(['accountOpeningBalances as sum_opening_dr_amount' => function ($q) {
            $q->filterData();
        }], 'dr_amount');
        $query->withSum(['accountOpeningBalances as sum_opening_cr_amount' => function ($q) {
            $q->filterData();
        }], 'cr_amount');
        $query->withSum(['journalParticulars as sum_dr_amount' => function ($q) use ($validated) {
            $q->filterData($validated);
        }], 'dr_amount');
        $query->withSum(['journalParticulars as sum_cr_amount' => function ($q) use ($validated) {
            $q->filterData($validated);
        }], 'cr_amount');
    }
}
