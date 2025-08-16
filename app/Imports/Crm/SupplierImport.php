<?php

namespace App\Imports\Crm;

use App\Models\Account\Ledger;
use App\Models\Crm\Company;
use App\Models\Crm\Supplier;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Maatwebsite\Excel\Concerns\SkipsEmptyRows;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithBatchInserts;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;

class SupplierImport implements ToModel, WithHeadingRow, WithValidation, SkipsEmptyRows, WithBatchInserts
{
    public function __construct($request)
    {
    }

    public function model(array $row)
    {
        if (empty(\accountSetting()->supplier_ledger_group_id)) {
            abort(400, 'Supplier ledger group not mapped in account setting');
        }
        DB::transaction(function () use ($row) {
            $ledger = Ledger::create([
                'ledger_group_id' => \accountSetting()->supplier_ledger_group_id,
                'ledger_name' => $row['supplier_name'],
                'code' => $row['code'],
                'category' => 'Supplier',
                'phone' => $row['phone'],
                'email' => $row['email'],
                'pan_no' => $row['pan_no'],
                'address' => $row['address'],
            ]);

            return Supplier::create([
                'supplier_name' => $row['supplier_name'],
                'code' => $row['code'],
                'phone' => $row['phone'],
                'email' => $row['email'],
                'ledger_id' => $ledger->id,
                'company_id' => Company::where('company_name', $row['company'])->first()->id ?? null,
                'pan_no' => $row['pan_no'],
                'address' => $row['address'],
            ]);
        });
    }

    public function rules(): array
    {
        return [
            '*.supplier_name' => ['required'],
            '*.code' => ['required', Rule::unique('suppliers', 'code')->withoutTrashed()],
            '*.address' => ['nullable'],
            '*.phone' => ['nullable', Rule::unique('suppliers', 'phone')->withoutTrashed()],
            '*.email' => ['nullable', 'email', Rule::unique('suppliers', 'email')->withoutTrashed()],
            '*.company' => ['nullable', Rule::exists('companies', 'company_name')->withoutTrashed()],
            '*.pan_no' => ['nullable', Rule::unique('suppliers', 'pan_no')->withoutTrashed()],
        ];
    }

    public function batchSize(): int
    {
        return 1000;
    }
}
