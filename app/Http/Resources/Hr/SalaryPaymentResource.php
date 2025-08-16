<?php

namespace App\Http\Resources\Hr;

use App\Models\Hr\AdvanceSalary;
use App\Models\Hr\SalaryStructure;
use App\Models\Setting\Tax;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Str;
use NumberFormatter;

class SalaryPaymentResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id' => $this->id ?? '',
            'payslip_no' => $this->payslip_no ?? '',
            'month' => $this->whenLoaded('month', function () {
                return $this->month->name ?? '';
            }),
            'payment_date' => $this->payment_date ?? '',
            'payment_method' => $this->payment_method ?? '',
            'paid_amount' => $this->salary_payment_particulars_sum_amount ?? 0,
            'amount_in_words' => Str::headline($this->numberFormatter(round($this->salary_payment_particulars_sum_amount, 2))),
            'remarks' => $this->remarks ?? '',
            'employee_id' => $this->employee_id ?? '',
            'employee' => EmployeeResource::make($this->whenLoaded('employee')),
            'created_by' => $this->createUser->name ?? '',
            'is_cancelled' => $this->is_cancelled ?? false,
            'cancelled_reason' => $this->cancelled_reason ?? '',
            'cancelled_by' => $this->whenLoaded('cancelUser', function () {
                return $this->cancelUser->name ?? '';
            }),
            'payslipDetails' => $this->whenLoaded('salaryPaymentParticulars', function () {
                return $this->mapPayslipParticulars();
            }),
        ];
    }

    private function mapPayslipParticulars(): array
    {
        $earnings = collect();
        $deductions = collect();

        foreach ($this->salaryPaymentParticulars as $salaryPaymentParticular) {
            foreach ($salaryPaymentParticular->payableCharge?->salaryLedgers ?? collect() as $salaryLedger) {
                if ($salaryLedger->model_type == SalaryStructure::class) {
                    $salaryLedger->model->load('payHead');
                    $payHead = $salaryLedger->model->payHead->name;
                    if ($earning = $earnings->get($payHead)) {
                        $earnings->put($payHead, $earning + $salaryLedger->dr_amount);
                    } else {
                        $earnings->put($payHead, $salaryLedger->dr_amount);
                    }
                } elseif ($salaryLedger->model_type == AdvanceSalary::class) {
                    if ($deduction = $deductions->get('Advance Salary')) {
                        $deductions->put('Advance Salary', $deduction + $salaryLedger->cr_amount);
                    } else {
                        $deductions->put('Advance Salary', $salaryLedger->cr_amount);
                    }
                } elseif ($salaryLedger->model_type == Tax::class) {
                    $tax = $salaryLedger->model->name;
                    if ($deduction = $deductions->get($tax)) {
                        $deductions->put($tax, $deduction + $salaryLedger->cr_amount);
                    } else {
                        $deductions->put($tax, $salaryLedger->cr_amount);
                    }
                }
            }
        }

        return [
            'earnings' => $earnings,
            'total_earnings' => $earnings->sum(),
            'deductions' => $deductions,
            'total_deductions' => $deductions->sum(),
        ];
    }

    private function numberFormatter($digit): bool|string
    {
        $format = new \NumberFormatter('en', NumberFormatter::SPELLOUT);

        return $format->format($digit);
    }
}
