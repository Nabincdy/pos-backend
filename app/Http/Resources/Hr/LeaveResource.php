<?php

namespace App\Http\Resources\Hr;

use Illuminate\Http\Resources\Json\JsonResource;

class LeaveResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id' => $this->id ?? '',
            'employee_id' => $this->employee_id ?? '',
            'employee' => $this->employee->name ?? '',
            'leave_type_id' => $this->leave_type_id ?? '',
            'leave_type' => $this->leaveType->title ?? '',
            'issued_date' => $this->issued_date ?? '',
            'start_date' => $this->start_date ?? '',
            'end_date' => $this->end_date ?? '',
            'reason' => $this->reason ?? '',
            'status' => $this->status ?? '',
            'submit_user_id' => $this->submit_user_id ?? '',
        ];
    }
}
