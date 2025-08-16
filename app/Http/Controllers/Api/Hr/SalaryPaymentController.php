<?php

namespace App\Http\Controllers\Api\Hr;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\Hr\SalaryPayment\StoreSalaryPaymentRequest;
use App\Http\Resources\Hr\SalaryPaymentResource;
use App\Models\Hr\SalaryPayment;
use App\Models\Setting\FiscalYear;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class SalaryPaymentController extends Controller
{
    public function index(Request $request)
    {
        $this->checkAuthorization('salaryPayment_access');

        $salaryPayments = SalaryPayment::withSum('salaryPaymentParticulars', 'amount')
            ->with('month', 'createUser', 'employee', 'cancelUser')
            ->filterData($request->all())
            ->latest('payment_date')
            ->get();

        return SalaryPaymentResource::collection($salaryPayments);
    }

    public function store(StoreSalaryPaymentRequest $request)
    {
        $this->checkAuthorization('salaryPayment_create');

        $salaryPayment = DB::transaction(function () use ($request) {
            $salaryPayment = SalaryPayment::create([
                'payslip_no' => 'PS-'.Str::padLeft(SalaryPayment::max('id') + 1, 3, 0),
                'fiscal_year_id' => FiscalYear::where('is_running', 1)->first()->id,
                'month_id' => $request->input('month_id'),
                'payment_date' => $request->input('payment_date'),
                'employee_id' => $request->input('employee_id'),
                'payment_method' => $request->input('payment_method') ?? 'Cash',
                'remarks' => $request->input('remarks'),
                'create_user_id' => auth()->id(),
            ]);

            foreach ($request->validated()['paymentParticulars'] as $paymentParticular) {
                $salaryPayment->salaryPaymentParticulars()->create([
                    'employee_id' => $salaryPayment->employee_id,
                    'payable_charge_id' => $paymentParticular['payable_charge_id'],
                    'model_type' => null,
                    'model_id' => null,
                    'amount' => $paymentParticular['amount'],
                ]);
            }
            //store in salary ledgers
            $salaryPayment->salaryLedgers()->create([
                'fiscal_year_id' => $salaryPayment->fiscal_year_id,
                'employee_id' => $salaryPayment->employee_id,
                'month_id' => $request->input('month_id'),
                'date' => $salaryPayment->payment_date,
                'dr_amount' => 0,
                'cr_amount' => $salaryPayment->salaryPaymentParticulars->sum('amount'),
                'remarks' => "Salary Payment From Payslip No. : $salaryPayment->payslip_no",
                'create_user_id' => auth()->id(),
            ]);

            //save transaction to journal
            $journal = $salaryPayment->journal()->create([
                'fiscal_year_id' => $salaryPayment->fiscal_year_id,
                'journal_no' => $salaryPayment->payslip_no,
                'date' => $salaryPayment->payment_date,
                'user_id' => auth()->id(),
                'remarks' => 'Salary Payment From Payslip No. : '.$salaryPayment->payslip_no,
            ]);
            //debit for salary payable
            $journal->journalParticulars()->create([
                'ledger_id' => accountSetting()->salary_payable_ledger_id,
                'date' => $journal->date,
                'dr_amount' => $salaryPayment->salaryPaymentParticulars->sum('amount'),
                'cr_amount' => 0,
                'remarks' => 'By-Cash/Bank Account',
            ]);
            //credit for Cash/Bank Account
            $journal->journalParticulars()->create([
                'ledger_id' => $request->input('payment_method') == 'Bank' ? $request->input('bank_account_ledger_id') : \accountSetting()->cash_ledger_id,
                'date' => $journal->date,
                'dr_amount' => 0,
                'cr_amount' => $salaryPayment->salaryPaymentParticulars->sum('amount'),
                'remarks' => 'By-Salary Payable',
            ]);

            return $salaryPayment;
        });

        return response()->json([
            'data' => new SalaryPaymentResource(
                $salaryPayment->load('month', 'createUser')->loadSum('salaryPaymentParticulars', 'amount')
            ),
            'message' => 'Salary Paid Successfully',
        ], 201);
    }

    public function show(SalaryPayment $salaryPayment)
    {
        $this->checkAuthorization('salaryPayment_access');

        $salaryPayment->load('createUser', 'month', 'employee.designation', 'employee.department', 'salaryPaymentParticulars.payableCharge.salaryLedgers.model')
            ->loadSum('salaryPaymentParticulars', 'amount');

        return SalaryPaymentResource::make($salaryPayment);
    }

    public function update(Request $request, SalaryPayment $salaryPayment)
    {
        $this->checkAuthorization('salaryPayment_edit');
    }

    public function destroy(Request $request, SalaryPayment $salaryPayment)
    {
        $this->checkAuthorization('salaryPayment_delete');

        $request->validate([
            'cancelled_reason' => ['required', 'max:255'],
        ]);

        DB::transaction(function () use ($request, $salaryPayment) {
            $salaryPayment->salaryLedgers()->update([
                'is_cancelled' => 1,
            ]);

            $salaryPayment->salaryPaymentParticulars()->update([
                'is_cancelled' => 1,
            ]);
            $salaryPayment->journal()->update([
                'is_cancelled' => 1,
            ]);

            $salaryPayment->journal?->journalParticulars()->update([
                'is_cancelled' => 1,
            ]);

            $salaryPayment->update([
                'is_cancelled' => 1,
                'cancelled_reason' => $request->cancelled_reason,
                'cancel_user_id' => auth()->id(),
            ]);
        });

        return response()->json([
            'data' => '',
            'message' => 'Salary Payment Cancelled Successfully',
        ]);
    }
}
