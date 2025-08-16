<?php

namespace App\Models\Inventory;

use App\Models\Setting\FiscalYear;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class ProductStock extends Model
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
        'product_id',
        'expiry_date',
        'en_expiry_date',
        'batch_no',
        'model_type',
        'model_id',
        'unit_id',
        'warehouse_id',
        'quantity',
        'rate',
        'type',
        'date',
        'remarks',
    ];

    protected $casts = [
        'product_id' => 'integer',
        'unit_id' => 'integer',
        'warehouse_id' => 'integer',
        'quantity' => 'integer',
        'rate' => 'double',
    ];

    public function getAmountAttribute(): float|int
    {
        return (float) $this->attributes['quantity'] * $this->attributes['rate'];
    }

    public function scopeFilterData($query, $param = [])
    {
        if (! empty($param['fiscal_year_id'])) {
            $query->where('fiscal_year_id', $param['fiscal_year_id']);
        } else {
            $query->where('fiscal_year_id', runningFiscalYear()->id);
        }

        if (! empty($param['product_id'])) {
            $query->where('product_id', $param['product_id']);
        }
        if (! empty($param['warehouse_id'])) {
            $query->where('warehouse_id', $param['warehouse_id']);
        }
        if (! empty($param['from_date'])) {
            $query->whereDate('date', '>=', $param['from_date']);
        }
        if (! empty($param['to_date'])) {
            $query->whereDate('date', '<=', $param['to_date']);
        }

        return $query;
    }

    public function fiscalYear(): BelongsTo
    {
        return $this->belongsTo(FiscalYear::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function model(): MorphTo
    {
        return $this->morphTo();
    }

    public function unit(): BelongsTo
    {
        return $this->belongsTo(Unit::class);
    }

    public function warehouse(): BelongsTo
    {
        return $this->belongsTo(Warehouse::class);
    }
}
