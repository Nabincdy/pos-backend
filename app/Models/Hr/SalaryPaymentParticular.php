<?php

namespace App\Models\Hr;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class SalaryPaymentParticular extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $dates = [
        'created_at',
        'updated_at',
        'deleted_at',
    ];

    protected $fillable = [
        'salary_payment_id',
        'employee_id',
        'payable_charge_id',
        'model_type',
        'model_id',
        'amount',
        'remarks',
        'is_cancelled',
    ];

    protected $casts = [
        'amount' => 'double',
        'is_cancelled' => 'boolean',
    ];

    public function salaryPayment(): BelongsTo
    {
        return $this->belongsTo(SalaryPayment::class);
    }

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }

    public function payableCharge(): BelongsTo
    {
        return $this->belongsTo(PayableCharge::class);
    }

    public function model(): MorphTo
    {
        return $this->morphTo();
    }
}
