<?php

namespace App\Models\Hr;

use App\Models\Account\Journal;
use App\Models\Account\Ledger;
use App\Models\Setting\FiscalYear;
use App\Models\Setting\Month;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\Relations\MorphOne;
use Illuminate\Database\Eloquent\SoftDeletes;

class AdvanceSalary extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $dates = [
        'created_at',
        'updated_at',
        'deleted_at',
    ];

    protected $fillable = [
        'fiscal_year_id',
        'employee_id',
        'date',
        'amount',
        'deduct_month_id',
        'remarks',
        'payment_method',
        'cash_bank_ledger_id',
        'create_user_id',
        'is_cancelled',
        'cancelled_reason',
        'cancel_user_id',
    ];

    protected $casts = [
        'employee_id' => 'integer',
        'amount' => 'double',
        'deduct_month_id' => 'integer',
    ];

    public function scopeFilterData($query, $param = [])
    {
        if (! empty($param['fiscal_year_id'])) {
            $query->where('fiscal_year_id', $param['fiscal_year_id']);
        } else {
            $query->where('fiscal_year_id', runningFiscalYear()->id);
        }

        if (! empty($param['employee_id'])) {
            $query->where('employee_id', $param['employee_id']);
        }

        if (! empty($param['deduct_month_id'])) {
            $query->where('deduct_month_id', $param['deduct_month_id']);
        }
        if (! empty($param['advance_salary_status']) && $param['advance_salary_status'] == 'Cancelled') {
            $query->where('is_cancelled', 1);
        } else {
            $query->where('is_cancelled', 0);
        }
    }

    public function fiscalYear(): BelongsTo
    {
        return $this->belongsTo(FiscalYear::class);
    }

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }

    public function deductMonth(): BelongsTo
    {
        return $this->belongsTo(Month::class, 'deduct_month_id');
    }

    public function salaryLedgers(): MorphMany
    {
        return $this->morphMany(SalaryLedger::class, 'model');
    }

    public function cashBankLedger(): BelongsTo
    {
        return $this->belongsTo(Ledger::class, 'cash_bank_ledger_id');
    }

    public function journal(): MorphOne
    {
        return $this->morphOne(Journal::class, 'model');
    }

    public function createUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'create_user_id');
    }

    public function cancelUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'cancel_user_id');
    }
}
