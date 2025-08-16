<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Artisan;

class DatabaseSeeder extends Seeder
{
    public function run()
    {
        $this->call([
            PermissionSeeder::class,
            RoleSeeder::class,
            PermissionRoleSeeder::class,
            UserSeeder::class,
            FiscalYearSeeder::class,
            CompanySettingSeeder::class,
            AccountLedgerSeeder::class,
            AccountSettingSeeder::class,
            MonthSeeder::class,
        ]);
        Artisan::call('optimize:clear');
    }
}
