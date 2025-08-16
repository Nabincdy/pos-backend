<?php

namespace App\Http\Controllers\Api\Hr;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\Hr\PayableCharge\StorePayableChargeRequest;
use App\Http\Resources\Hr\PayableChargeResource;
use App\Models\Hr\Employee;
use App\Models\Hr\PayableCharge;
use App\Models\Hr\SalaryStructure;
use App\Models\Setting\Tax;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class PayableChargeController extends Controller
{
    public function index(Request $request)
    {
        $this->checkAuthorization('payableCharge_access');

        $payableCharges = PayableCharge::with('employee', 'month', 'createUser', 'cancelUser')
            ->withSum('salaryLedgers', 'dr_amount')
            ->withSum('salaryLedgers', 'cr_amount')
            ->withSum(['salaryPaymentParticulars' => function ($query) {
                $query->where('is_cancelled', 0);
            }], 'amount')
            ->filterData($request->all())
            ->orderByDesc('date')
            ->get();

        return PayableChargeResource::collection($payableCharges);
    }

    public function store(StorePayableChargeRequest $request)
    {
        $this->checkAuthorization('payableCharge_create');

        $checkPayableCharge = PayableCharge::where('fiscal_year_id', runningFiscalYear()->id)
            ->where('month_id', $request->input('month_id'))
            ->where('is_cancelled', 0)
            ->whereIn('employee_id', $request->input('employees'))
            ->count();

        if ($checkPayableCharge > 0) {
            return response()->json([
                'message' => 'Salary already charged for selected employees on this month',
            ], 400);
        }

        $checkedEmployees = Employee::with('latestSalary.salaryStructures.payHead.tax')
            ->withSum(['advanceSalaries' => function ($query) use ($request) {
                $query->where('deduct_month_id', $request->input('month_id'));
                $query->where('is_cancelled', 0);
                $query->where('fiscal_year_id', runningFiscalYear()->id);
            }], 'amount')
            ->whereIn('id', $request->input('employees'))
            ->orderBy('rank')
            ->get();

        DB::transaction(function () use ($request, $checkedEmployees) {
            foreach ($checkedEmployees as $employee) {
                $payableCharge = $employee->payableCharges()->create([
                    'fiscal_year_id' => runningFiscalYear()->id,
                    'charge_no' => 'PC-'.Str::padLeft(PayableCharge::max('id') + 1, 3, 0),
                    'month_id' => $request->input('month_id'),
                    'date' => $request->input('date'),
                    'remarks' => null,
                    'create_user_id' => auth()->id(),
                ]);

                //save transaction to journal
                $journal = $payableCharge->journal()->create([
                    'fiscal_year_id' => $payableCharge->fiscal_year_id,
                    'journal_no' => $payableCharge->charge_no,
                    'date' => $payableCharge->date,
                    'user_id' => auth()->id(),
                    'remarks' => "Salary Payable Charge - $payableCharge->charge_no",
                ]);

                $salary_amount_sum = 0;
                $tax_amount_sum = 0;

                foreach ($employee->latestSalary->salaryStructures as $salaryStructure) {
                    $payableCharge->salaryLedgers()->create([
                        'fiscal_year_id' => runningFiscalYear()->id,
                        'employee_id' => $payableCharge->employee_id,
                        'model_type' => SalaryStructure::class,
                        'model_id' => $salaryStructure->id,
                        'month_id' => $request->input('month_id'),
                        'date' => $request->input('date'),
                        'dr_amount' => $salaryStructure->payHead->type === 'Addition' ? $salaryStructure->amount : 0,
                        'cr_amount' => $salaryStructure->payHead->type === 'Deduction' ? $salaryStructure->amount : 0,
                        'remarks' => $salaryStructure->payHead->name,
                        'create_user_id' => auth()->id(),
                    ]);
                    //debit for salary expenses account
                    $journal->journalParticulars()->create([
                        'ledger_id' => $salaryStructure->payHead->ledger_id ?? null,
                        'date' => $journal->date,
                        'dr_amount' => $salaryStructure->amount,
                        'cr_amount' => 0,
                        'remarks' => ($salaryStructure->payHead->name ?? '').' Payable Charge - '.$payableCharge->charge_no,
                    ]);
                    $salary_amount_sum += $salaryStructure->amount;
                    //credit for payable tax
                    if ($salaryStructure->payHead->is_taxable) {
                        $tax_amount = round($salaryStructure->amount * ($salaryStructure->payHead->tax->rate ?? 0) / 100, 2);
                        $journal->journalParticulars()->create([
                            'ledger_id' => $salaryStructure->payHead->tax->ledger_id ?? null,
                            'date' => $journal->date,
                            'dr_amount' => 0,
                            'cr_amount' => $tax_amount,
                            'remarks' => ($salaryStructure->payHead->tax->name ?? '').' Deducted from '.($salaryStructure->payHead->name ?? ''),
                        ]);
                        $tax_amount_sum += $tax_amount;
                        //credit tax amount to salary ledger
                        $payableCharge->salaryLedgers()->create([
                            'fiscal_year_id' => runningFiscalYear()->id,
                            'employee_id' => $payableCharge->employee_id,
                            'model_type' => Tax::class,
                            'model_id' => $salaryStructure->payHead->tax_id ?? null,
                            'month_id' => $request->input('month_id'),
                            'date' => $request->input('date'),
                            'dr_amount' => 0,
                            'cr_amount' => $tax_amount,
                            'remarks' => ($salaryStructure->payHead->tax->name ?? '').' deducted from '.($salaryStructure->payHead->name ?? ''),
                            'create_user_id' => auth()->id(),
                        ]);
                    }
                }
                //credit for advance salary
                if ($employee->advance_salaries_sum_amount > 0) {
                    $journal->journalParticulars()->create([
                        'ledger_id' => accountSetting()->advance_salary_id,
                        'date' => $journal->date,
                        'dr_amount' => 0,
                        'cr_amount' => $employee->advance_salaries_sum_amount,
                        'remarks' => 'Advance Salary Deduction - '.$payableCharge->charge_no,
                    ]);
                }
                //credit for salary payable
                $journal->journalParticulars()->create([
                    'ledger_id' => accountSetting()->salary_payable_ledger_id,
                    'date' => $journal->date,
                    'dr_amount' => 0,
                    'cr_amount' => $salary_amount_sum - $tax_amount_sum - $employee->advance_salaries_sum_amount,
                    'remarks' => "Salary Payable from payroll charge - $payableCharge->charge_no",
                ]);
            }
        });

        return response()->json([
            'message' => 'Payable Salary Charged Successfully',
            'data' => '',
        ], 201);
    }

    public function show(PayableCharge $payableCharge)
    {
        $this->checkAuthorization('payableCharge_access');
    }

    public function update(Request $request, PayableCharge $payableCharge)
    {
        $this->checkAuthorization('payableCharge_edit');
    }

    public function destroy(Request $request, PayableCharge $payableCharge)
    {
        $this->checkAuthorization('payableCharge_delete');

        $request->validate([
            'cancelled_reason' => ['required', 'max:255'],
        ]);

        DB::transaction(function () use ($request, $payableCharge) {
            $payableCharge->salaryLedgers()->update([
                'is_cancelled' => 1,
            ]);

            $payableCharge->journal()->update([
                'is_cancelled' => 1,
            ]);

            $payableCharge->journal?->journalParticulars()->update([
                'is_cancelled' => 1,
            ]);

            $payableCharge->update([
                'is_cancelled' => 1,
                'cancelled_reason' => $request->cancelled_reason,
                'cancel_user_id' => auth()->id(),
            ]);
        });

        return response()->json([
            'data' => '',
            'message' => 'Payable Charge Cancelled Successfully',
        ]);
    }
}
