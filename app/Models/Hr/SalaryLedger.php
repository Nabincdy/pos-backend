<?php

namespace App\Models\Hr;

use App\Models\Setting\FiscalYear;
use App\Models\Setting\Month;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class SalaryLedger extends Model
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
        'payable_charge_id',
        'model_type',
        'model_id',
        'month_id',
        'date',
        'dr_amount',
        'cr_amount',
        'remarks',
        'create_user_id',
        'is_cancelled',
    ];

    protected $casts = [
        'dr_amount' => 'double',
        'cr_amount' => 'double',
    ];

    public function fiscalYear(): BelongsTo
    {
        return $this->belongsTo(FiscalYear::class);
    }

    public function payableCharge(): BelongsTo
    {
        return $this->belongsTo(PayableCharge::class);
    }

    public function model(): MorphTo
    {
        return $this->morphTo();
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
}
