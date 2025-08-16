<?php

namespace App\Models\Setting;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class AccountSetting extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $dates = [
        'created_at',
        'updated_at',
        'deleted_at',
    ];

    protected $fillable = [
        'cash_ledger_id',
        'bank_ledger_group_id',
        'supplier_ledger_group_id',
        'client_ledger_group_id',
        'tax_ledger_group_id',
        'purchase_ledger_id',
        'sales_ledger_id',
        'advance_salary_id',
        'salary_payable_ledger_id',
    ];
}
