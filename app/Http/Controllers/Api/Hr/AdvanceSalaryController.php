<?php

namespace App\Http\Controllers\Api\Hr;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\Hr\AdvanceSalary\StoreAdvanceSalaryRequest;
use App\Http\Requests\Api\Hr\AdvanceSalary\UpdateAdvanceSalaryRequest;
use App\Http\Resources\Hr\AdvanceSalaryResource;
use App\Models\Hr\AdvanceSalary;
use App\Models\Hr\PayableCharge;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class AdvanceSalaryController extends Controller
{
    public function index()
    {
        $this->checkAuthorization('advanceSalary_access');

        $advanceSalaries = AdvanceSalary::with('employee', 'deductMonth')
            ->filterData(request()->all())
            ->get();

        return AdvanceSalaryResource::collection($advanceSalaries);
    }

    public function store(StoreAdvanceSalaryRequest $request)
    {
        $this->checkAuthorization('advanceSalary_create');

        $checkPayableCharges = PayableCharge::where('fiscal_year_id', runningFiscalYear()->id)
            ->where('month_id', $request->input('deduct_month_id'))
            ->where('is_cancelled', 0)
            ->where('employee_id', $request->input('employee_id'))
            ->count();

        if ($checkPayableCharges > 0) {
            abort(400, 'Payroll already generated on selected month');
        }

        if (! accountSetting()->advance_salary_id) {
            abort(400, 'Advance Salary Ledger not mapped in account setting');
        }

        $advanceSalary = DB::transaction(function () use ($request) {
            $advanceSalary = AdvanceSalary::create($request->validated() + [
                'fiscal_year_id' => runningFiscalYear()->id,
                'create_user_id' => auth()->id(),
                'payment_method' => $request->input('payment_method') ?? 'Cash',
                'cash_bank_ledger_id' => $request->input('payment_method') == 'Bank' ? $request->input('bank_account_ledger_id') : \accountSetting()->cash_ledger_id,
            ]);
            $advanceSalary->salaryLedgers()->create([
                'fiscal_year_id' => runningFiscalYear()->id,
                'employee_id' => $advanceSalary->employee_id,
                'month_id' => $advanceSalary->deduct_month_id,
                'date' => $request->input('date'),
                'dr_amount' => 0,
                'cr_amount' => $advanceSalary->amount,
                'remarks' => 'Advance Salary',
                'create_user_id' => auth()->id(),
            ]);

            //save transaction to journal
            $journal = $advanceSalary->journal()->create([
                'fiscal_year_id' => $advanceSalary->fiscal_year_id,
                'journal_no' => 'AS-'.Str::padLeft(AdvanceSalary::max('id') + 1, 3, 0),
                'date' => $advanceSalary->date,
                'user_id' => auth()->id(),
                'remarks' => $advanceSalary->remarks,
            ]);
            //debit for advance salary account
            $journal->journalParticulars()->create([
                'ledger_id' => \accountSetting()->advance_salary_id,
                'date' => $journal->date,
                'dr_amount' => $advanceSalary->amount,
                'cr_amount' => 0,
                'remarks' => 'By-'.($advanceSalary->cashBankLedger->ledger_name ?? ''),
            ]);
            //credit for cash/bank
            $journal->journalParticulars()->create([
                'ledger_id' => $advanceSalary->cash_bank_ledger_id,
                'date' => $journal->date,
                'dr_amount' => 0,
                'cr_amount' => $advanceSalary->amount,
                'remarks' => "By- Advance Salary ($journal->journal_no)",
            ]);

            return $advanceSalary;
        });

        return response()->json([
            'data' => new AdvanceSalaryResource($advanceSalary->load('employee', 'deductMonth')),
            'message' => 'Advance Salary Added Successfully',
        ], 201);
    }

    public function show(AdvanceSalary $advanceSalary)
    {
        $this->checkAuthorization('advanceSalary_access');

        return AdvanceSalaryResource::make($advanceSalary);
    }

    public function update(UpdateAdvanceSalaryRequest $request, AdvanceSalary $advanceSalary)
    {
        $this->checkAuthorization('advanceSalary_edit');

        $advanceSalary->update($request->validated());

        return response()->json([
            'data' => new AdvanceSalaryResource($advanceSalary),
            'message' => 'Advance Salary Updated Successfully',
        ]);
    }

    public function destroy(Request $request, AdvanceSalary $advanceSalary)
    {
        $this->checkAuthorization('advanceSalary_delete');

        $request->validate([
            'cancel_reason' => ['required', 'max:255'],
        ]);

        DB::transaction(function () use ($request, $advanceSalary) {
            $advanceSalary->journal()->update([
                'is_cancelled' => 1,
            ]);

            $advanceSalary->journal?->journalParticulars()->update([
                'is_cancelled' => 1,
            ]);
            $advanceSalary->update([
                'is_cancelled' => 1,
                'cancel_user_id' => auth()->id(),
                'cancelled_reason' => $request->cancel_reason,
            ]);
        });

        return response()->json([
            'data' => '',
            'message' => 'Advance Salary Deleted Successfully',
        ]);
    }
}
