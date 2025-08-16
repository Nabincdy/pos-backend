<?php

namespace App\Imports\Crm;

use App\Models\Account\Ledger;
use App\Models\Crm\Client;
use App\Models\Crm\ClientGroup;
use App\Models\Crm\Company;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Maatwebsite\Excel\Concerns\SkipsEmptyRows;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithBatchInserts;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;

class ClientImport implements ToModel, WithHeadingRow, WithValidation, SkipsEmptyRows, WithBatchInserts
{
    public function __construct($request)
    {
    }

    public function model(array $row)
    {
        if (empty(\accountSetting()->client_ledger_group_id)) {
            abort(400, 'Client ledger group not mapped in account setting');
        }
        DB::transaction(function () use ($row) {
            $ledger = Ledger::create([
                'ledger_group_id' => \accountSetting()->client_ledger_group_id,
                'ledger_name' => $row['name'],
                'code' => $row['code'],
                'category' => 'Client',
                'phone' => $row['phone'],
                'email' => $row['email'],
                'pan_no' => $row['pan_no'],
                'address' => $row['address'],
            ]);

            return Client::create([
                'client_group_id' => ClientGroup::where('group_name', $row['client_group'])->first()->id ?? null,
                'name' => $row['name'],
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
            '*.client_group' => ['required', Rule::exists('client_groups', 'group_name')->withoutTrashed()],
            '*.name' => ['required'],
            '*.code' => ['required', Rule::unique('clients', 'code')->withoutTrashed()],
            '*.phone' => ['nullable', Rule::unique('clients', 'phone')->withoutTrashed()],
            '*.email' => ['nullable', 'email', Rule::unique('clients', 'email')->withoutTrashed()],
            '*.company' => ['nullable', Rule::exists('companies', 'company_name')->withoutTrashed()],
            '*.pan_no' => ['nullable', Rule::unique('clients', 'pan_no')->withoutTrashed()],
            '*.address' => ['nullable'],

        ];
    }

    public function batchSize(): int
    {
        return 1000;
    }
}
