<?php

namespace App\Imports\Hr;

use App\Models\Hr\Department;
use App\Models\Hr\Designation;
use App\Models\Hr\Employee;
use Illuminate\Validation\Rule;
use Maatwebsite\Excel\Concerns\SkipsEmptyRows;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithBatchInserts;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;

class EmployeeImport implements ToModel, WithHeadingRow, WithValidation, SkipsEmptyRows, WithBatchInserts
{
    public function __construct($request)
    {
    }

    public function model(array $row)
    {
        Employee::create([
            'name' => $row['name'],
            'code' => $row['code'],
            'gender' => $row['gender'],
            'dob' => $row['dob'],
            'email' => $row['email'],
            'phone' => $row['phone'],
            'department_id' => Department::where('name', $row['department'])->first()->id ?? null,
            'designation_id' => Designation::where('name', $row['designation'])->first()->id ?? null,
            'joining_date' => $row['joining_date'],
            'marital_status' => $row['marital_status'],
            'citizenship_no' => $row['citizenship_no'],
            'pan_no' => $row['pan_no'],
            'address' => $row['address'],

        ]);
    }

    public function rules(): array
    {
        return [
            '*.name' => ['required', 'string', 'max:255', Rule::unique('employees', 'name')->withoutTrashed()],
            '*.code' => ['required'],
            '*.gender' => ['required', Rule::in(['Male', 'Female', 'Other'])],
            '*.dob' => ['nullable'],
            '*.email' => ['nullable', 'required_with:role_id', 'email', Rule::unique('employees', 'email')->withoutTrashed(), Rule::unique('users', 'email')->withoutTrashed()],
            '*.phone' => ['nullable', Rule::unique('employees', 'phone')->withoutTrashed()],
            '*.department' => ['required', Rule::exists('departments', 'name')->withoutTrashed()],
            '*.designation' => ['required', Rule::exists('designations', 'name')->withoutTrashed()],
            '*.joining_date' => ['nullable'],
            '*.marital_status' => ['nullable'],
            '*.citizenship_no' => ['nullable'],
            '*.pan_no' => ['nullable', 'integer'],
            '*.address' => ['nullable', 'string', 'max:255'],

        ];
    }

    public function batchSize(): int
    {
        return 1000;
    }
}
