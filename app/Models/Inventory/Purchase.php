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

class Purchase extends Model
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
        'purchase_date',
        'en_purchase_date',
        'payment_type',
        'supplier_ledger_id',
        'create_user_id',
        'is_cancelled',
        'cancelled_reason',
        'cancel_user_id',
        'remarks',
    ];

    protected $appends = [
        'purchase_month',
    ];

    public function getPurchaseMonthAttribute(): string
    {
        return explode('-', $this->purchase_date)[1] ?? '';
    }

    protected $casts = [
        'supplier_ledger_id' => 'integer',
        'is_cancelled' => 'boolean',
    ];

    public function scopeFilterData($query, $param = [])
    {
        if (! empty($param['fiscal_year_id'])) {
            $query->where('fiscal_year_id', $param['fiscal_year_id']);
        } else {
            $query->where('fiscal_year_id', runningFiscalYear()->id);
        }

        if (! empty($param['search'])) {
            $key = '%'.trim($param['search']).'%';
            $query->where('invoice_no', 'like', $key);
            $query->orWhereHas('supplierLedger', function ($q) use ($key) {
                $q->where('ledger_name', 'like', $key);
            });
        }

        if (! empty($param['supplier_ledger_id'])) {
            $query->where('supplier_ledger_id', $param['supplier_ledger_id']);
        }

        if (! empty($param['purchase_status']) && $param['purchase_status'] == 'Cancelled') {
            $query->where('is_cancelled', 1);
        } else {
            $query->where('is_cancelled', 0);
        }
        if (! empty($param['from_date'])) {
            $query->whereDate('purchase_date', '>=', $param['from_date']);
        }
        if (! empty($param['to_date'])) {
            $query->whereDate('purchase_date', '<=', $param['to_date']);
        }

        return $query;
    }

    public function fiscalYear(): BelongsTo
    {
        return $this->belongsTo(FiscalYear::class);
    }

    public function supplierLedger(): BelongsTo
    {
        return $this->belongsTo(Ledger::class, 'supplier_ledger_id');
    }

    public function createUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'create_user_id');
    }

    public function cancelUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'cancel_user_id');
    }

    public function purchaseParticulars(): HasMany
    {
        return $this->hasMany(PurchaseParticular::class);
    }

    public function paymentRecords(): HasMany
    {
        return $this->hasMany(PaymentRecord::class);
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
