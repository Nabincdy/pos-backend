<?php

namespace Database\Seeders;

use App\Models\Setting\AccountSetting;
use Illuminate\Database\Seeder;

class AccountSettingSeeder extends Seeder
{
    public function run()
    {
        AccountSetting::create([
            'cash_ledger_id' => null,
            'bank_ledger_group_id' => null,
            'supplier_ledger_group_id' => null,
            'client_ledger_group_id' => null,
            'tax_ledger_group_id' => null,
        ]);
    }
}
