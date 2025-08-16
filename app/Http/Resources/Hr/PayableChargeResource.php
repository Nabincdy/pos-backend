<?php

namespace App\Http\Resources\Hr;

use Illuminate\Http\Resources\Json\JsonResource;

class PayableChargeResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id' => $this->id ?? '',
            'charge_no' => $this->charge_no ?? '',
            'employee_id' => $this->employee_id ?? '',
            'employee_name' => $this->employee->name ?? '',
            'month_id' => $this->month_id ?? '',
            'month' => $this->month->name ?? '',
            'date' => $this->date ?? '',
            'net_salary_amount' => $this->netSalaryAmount(),
            'paid_amount' => $this->salary_payment_particulars_sum_amount ?? 0,
            'due_amount' => $this->netSalaryAmount() - ($this->salary_payment_particulars_sum_amount ?? 0),
            'payment_status' => $this->paymentStatus(),
            'remarks' => $this->remarks ?? '',
            'created_by' => $this->createUser->name ?? '',
            'is_cancelled' => $this->is_cancelled ?? false,
            'cancelled_reason' => $this->cancelled_reason ?? '',
            'cancelled_by' => $this->cancelUser->name ?? '',
        ];
    }

    private function netSalaryAmount(): float
    {
        return (float) ($this->salary_ledgers_sum_dr_amount ?? 0) - ($this->salary_ledgers_sum_cr_amount ?? 0);
    }

    private function paymentStatus(): string
    {
        if (($this->netSalaryAmount() - $this->salary_payment_particulars_sum_amount) == 0) {
            return 'Paid';
        } elseif ($this->salary_payment_particulars_sum_amount > 0) {
            return 'Partially Paid';
        } else {
            return 'UnPaid';
        }
    }
}
