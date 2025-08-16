<?php

namespace App\Models\Hr;

use App\Models\Account\Journal;
use App\Models\Setting\FiscalYear;
use App\Models\Setting\Month;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\Relations\MorphOne;
use Illuminate\Database\Eloquent\SoftDeletes;

class SalaryPayment extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $dates = [
        'created_at',
        'updated_at',
        'deleted_at',
    ];

    protected $fillable = [
        'payslip_no',
        'fiscal_year_id',
        'month_id',
        'payment_date',
        'employee_id',
        'payment_method',
        'remarks',
        'create_user_id',
        'is_cancelled',
        'cancelled_reason',
        'cancel_user_id',
    ];

    protected $casts = [
        'employee_id' => 'integer',
        'month_id' => 'integer',
        'is_cancelled' => 'boolean',
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

        if (! empty($param['month_id'])) {
            $query->where('month_id', $param['month_id']);
        }

        if (! empty($param['from_date'])) {
            $query->whereDate('payment_date', '>=', $param['from_date']);
        }
        if (! empty($param['to_date'])) {
            $query->whereDate('payment_date', '<=', $param['to_date']);
        }

        if (! empty($param['payment_status']) && $param['payment_status'] == 'Cancelled') {
            $query->where('is_cancelled', 1);
        } else {
            $query->where('is_cancelled', 0);
        }

        return $query;
    }

    public function fiscalYear(): BelongsTo
    {
        return $this->belongsTo(FiscalYear::class);
    }

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }

    public function month(): BelongsTo
    {
        return $this->belongsTo(Month::class);
    }

    public function createUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'create_user_id');
    }

    public function cancelUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'cancel_user_id');
    }

    public function salaryLedgers(): MorphMany
    {
        return $this->morphMany(SalaryLedger::class, 'model');
    }

    public function salaryPaymentParticulars(): HasMany
    {
        return $this->hasMany(SalaryPaymentParticular::class);
    }

    public function journal(): MorphOne
    {
        return $this->morphOne(Journal::class, 'model');
    }
}
