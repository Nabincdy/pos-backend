<?php

namespace App\Models\Inventory;

use App\Models\Setting\Tax;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class SaleParticular extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $dates = [
        'created_at',
        'updated_at',
        'deleted_at',
    ];

    protected $fillable = [
        'sale_id',
        'product_id',
        'unit_id',
        'warehouse_id',
        'quantity',
        'rate',
        'sales_tax_id',
        'sales_tax_amount',
        'discount_amount',
        'is_cancelled',
    ];

    protected $casts = [
        'quantity' => 'double',
        'rate' => 'double',
        'sales_tax_amount' => 'double',
        'discount_amount' => 'double',
        'is_cancelled' => 'boolean',
    ];

    protected $appends = [
        'sub_total',
        'amount_excl_tax',
        'total_amount',
    ];

    public function scopeFilterData($query, $param = [])
    {
        if (! empty($param['fiscal_year_id'])) {
            $query->whereHas('sale', function ($q) use ($param) {
                $q->where('fiscal_year_id', $param['fiscal_year_id']);
            });
        } else {
            $query->whereHas('sale', function ($q) {
                $q->where('fiscal_year_id', runningFiscalYear()->id);
            });
        }
        if (! empty($param['from_date'])) {
            $query->whereHas('sale', function ($q) use ($param) {
                $q->whereDate('sales_date', '>=', $param['from_date']);
            });
        }
        if (! empty($param['to_date'])) {
            $query->whereHas('sale', function ($q) use ($param) {
                $q->whereDate('sales_date', '<=', $param['to_date']);
            });
        }

        return $query;
    }

    public function getSubTotalAttribute(): float|int
    {
        return $this->quantity * $this->rate;
    }

    public function getAmountExclTaxAttribute()
    {
        return $this->getSubTotalAttribute() - $this->discount_amount;
    }

    public function getTotalAmountAttribute()
    {
        return $this->getSubTotalAttribute() - $this->discount_amount + $this->sales_tax_amount;
    }

    public function sale(): BelongsTo
    {
        return $this->belongsTo(Sale::class);
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

    public function salesTax(): BelongsTo
    {
        return $this->belongsTo(Tax::class, 'sales_tax_id');
    }
}
