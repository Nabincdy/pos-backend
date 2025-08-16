<?php

namespace Database\Seeders;

use App\Models\Setting\CompanySetting;
use Illuminate\Database\Seeder;

class CompanySettingSeeder extends Seeder
{
    public function run()
    {
        CompanySetting::create([
            'company_name' => 'Company Name',
            'email' => 'company@gmail.com',
            'address' => 'Company Address',
            'bank_account' => 'BA-',
            'journal_voucher' => 'JV-',
            'payment_voucher' => 'PV-',
            'receipt_voucher' => 'RV-',
            'client_group' => 'CG-',
            'client' => 'CL-',
            'company' => 'CMP-',
            'supplier' => 'SUP-',
            'product' => 'PRD-',
            'product_category' => 'PC-',
            'warehouse' => 'WR-',
            'purchase' => 'PO-',
            'purchase_return' => 'PR-',
            'sales' => 'SI-',
            'sales_return' => 'SR-',
            'quotation' => 'QT-',
            'employee' => 'EMP-',
            'payable_charge' => 'PBC-',
            'payslip' => 'PS-',
            'tax' => 'TX-',
            'supplier_payment' => 'PAY-OUT-',
            'client_payment' => 'PAY-IN-',
        ]);
    }
}
