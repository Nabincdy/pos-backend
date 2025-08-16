<?php

namespace App\Models\Inventory;

use App\Models\Setting\Tax;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class PurchaseParticular extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $dates = [
        'created_at',
        'updated_at',
        'deleted_at',
    ];

    protected $fillable = [
        'purchase_id',
        'product_id',
        'unit_id',
        'warehouse_id',
        'quantity',
        'product_rate',
        'batch_no',
        'expiry_date',
        'en_expiry_date',
        'purchase_tax_id',
        'purchase_tax_amount',
        'discount_amount',
        'is_cancelled',
    ];

    protected $casts = [
        'quantity' => 'double',
        'product_rate' => 'double',
        'purchase_tax_amount' => 'double',
        'discount_amount' => 'double',
        'is_cancelled' => 'boolean',
    ];

    public function scopeFilterData($query, $param = [])
    {
        if (! empty($param['fiscal_year_id'])) {
            $query->whereHas('purchase', function ($q) use ($param) {
                $q->where('fiscal_year_id', $param['fiscal_year_id']);
            });
        } else {
            $query->whereHas('purchase', function ($q) {
                $q->where('fiscal_year_id', runningFiscalYear()->id);
            });
        }
        if (! empty($param['from_date'])) {
            $query->whereHas('purchase', function ($q) use ($param) {
                $q->whereDate('purchase_date', '>=', $param['from_date']);
            });
        }
        if (! empty($param['to_date'])) {
            $query->whereHas('purchase', function ($q) use ($param) {
                $q->whereDate('purchase_date', '<=', $param['to_date']);
            });
        }

        return $query;
    }

    public function getSubTotalAttribute(): float|int
    {
        return $this->quantity * $this->product_rate;
    }

    public function getAmountExclTaxAttribute()
    {
        return $this->getSubTotalAttribute() - $this->discount_amount;
    }

    public function getTotalAmountAttribute()
    {
        return $this->getSubTotalAttribute() - $this->discount_amount + $this->purchase_tax_amount;
    }

    public function purchase(): BelongsTo
    {
        return $this->belongsTo(Purchase::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function unit(): BelongsTo
    {
        return $this->belongsTo(Unit::class);
    }

    public function warehouse(): BelongsTo
    {
        return $this->belongsTo(Warehouse::class);
    }

    public function purchaseTax(): BelongsTo
    {
        return $this->belongsTo(Tax::class, 'purchase_tax_id');
    }
}
