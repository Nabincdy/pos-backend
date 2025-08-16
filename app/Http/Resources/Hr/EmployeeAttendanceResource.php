<?php

namespace App\Http\Resources\Hr;

use Illuminate\Http\Resources\Json\JsonResource;

class EmployeeAttendanceResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'employee_id' => $this->id ?? '',
            'employee_name' => $this->name ?? '',
            'employee_code' => $this->code ?? '',
            'attendance_id' => $this->employeeAttendance->id ?? '',
            'status' => $this->employeeAttendance->status ?? 'Present',
            'in_time' => $this->employeeAttendance->in_time ?? '',
            'out_time' => $this->employeeAttendance->out_time ?? '',
            'remarks' => $this->employeeAttendance->remarks ?? '',
        ];
    }
}
