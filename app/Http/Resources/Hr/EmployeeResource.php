<?php

namespace App\Http\Resources\Hr;

use Illuminate\Http\Resources\Json\JsonResource;

class EmployeeResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id' => $this->id ?? '',
            'name' => $this->name ?? '',
            'dob' => $this->dob ?? '',
            'code' => $this->code ?? '',
            'gender' => $this->gender ?? '',
            'email' => $this->email ?? '',
            'rank' => $this->rank ?? '',
            'role_id' => $this->whenLoaded('user', function () {
                return $this->user->role_id ?? '';
            }),
            'phone' => $this->phone ?? '',
            'photo_url' => $this->photo_url ?? '',
            'joining_date' => $this->joining_date ?? '',
            'marital_status' => $this->marital_status ?? '',
            'citizenship_no' => $this->citizenship_no ?? '',
            'pan_no' => $this->pan_no ?? '',
            'signature_url' => $this->signature_url ?? '',
            'address' => $this->address ?? '',
            'status' => $this->status ?? false,
            'department_id' => $this->department_id ?? '',
            'designation_id' => $this->designation_id ?? '',
            'department_name' => $this->whenLoaded('department', function () {
                return $this->department->name ?? '';
            }),
            'designation_name' => $this->whenLoaded('designation', function () {
                return $this->designation->name ?? '';
            }),
            'latestSalary' => EmployeeSalaryResource::make($this->whenLoaded('latestSalary')),
        ];
    }
}
