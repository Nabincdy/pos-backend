<?php

namespace App\Models\Inventory;

use App\Models\Account\Journal;
use App\Models\Account\Ledger;
use App\Models\Setting\FiscalYear;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\Relations\MorphOne;
use Illuminate\Database\Eloquent\SoftDeletes;

class Sale extends Model
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
        'invoice_no',
        'sales_date',
        'en_sales_date',
        'payment_type',
        'client_ledger_id',
        'create_user_id',
        'is_cancelled',
        'cancelled_reason',
        'cancel_user_id',
        'remarks',
    ];

    protected $appends = [
        'sales_month',
    ];

    protected $casts = [
        'client_ledger_id' => 'integer',
        'is_cancelled' => 'boolean',
    ];

    public function getSalesMonthAttribute(): string
    {
        return explode('-', $this->sales_date)[1] ?? '';
    }

    public function scopeFilterData($query, $param = [])
    {
        if (! empty($param['fiscal_year_id'])) {
            $query->where('fiscal_year_id', $param['fiscal_year_id']);
        } else {
            $query->where('fiscal_year_id', runningFiscalYear()->id);
        }

        if (! empty($param['client_ledger_id'])) {
            $query->where('client_ledger_id', $param['client_ledger_id']);
        }

        if (! empty($param['sale_status']) && $param['sale_status'] == 'Cancelled') {
            $query->where('is_cancelled', 1);
        } else {
            $query->where('is_cancelled', 0);
        }
        if (! empty($param['from_date'])) {
            $query->whereDate('sales_date', '>=', $param['from_date']);
        }
        if (! empty($param['to_date'])) {
            $query->whereDate('sales_date', '<=', $param['to_date']);
        }

        if (! empty($param['search'])) {
            $key = '%'.trim($param['search']).'%';
            $query->where('invoice_no', 'like', $key);
            $query->orWhereHas('clientLedger', function ($q) use ($key) {
                $q->where('ledger_name', 'like', $key);
            });
        }

        return $query;
    }

    public function fiscalYear(): BelongsTo
    {
        return $this->belongsTo(FiscalYear::class);
    }

    public function clientLedger(): BelongsTo
    {
        return $this->belongsTo(Ledger::class, 'client_ledger_id');
    }

    public function createUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'create_user_id');
    }

    public function cancelUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'cancel_user_id');
    }

    public function saleParticulars(): HasMany
    {
        return $this->hasMany(SaleParticular::class);
    }

    public function receiptRecords(): HasMany
    {
        return $this->hasMany(ReceiptRecord::class);
    }

    public function productStocks(): MorphMany
    {
        return $this->morphMany(ProductStock::class, 'model');
    }

    public function journal(): MorphOne
    {
        return $this->morphOne(Journal::class, 'model');
    }
}
