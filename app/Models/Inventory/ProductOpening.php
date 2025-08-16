<?php

namespace App\Models\Inventory;

use App\Models\Setting\FiscalYear;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphOne;
use Illuminate\Database\Eloquent\SoftDeletes;

class ProductOpening extends Model
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
        'warehouse_id',
        'unit_id',
        'rate',
        'quantity',
        'opening_date',
        'en_opening_date',
        'user_id',
        'batch_no',
        'expiry_date',
        'en_expiry_date',
        'remarks',
    ];

    protected $casts = [
        'warehouse_id' => 'integer',
        'product_id' => 'integer',
        'unit_id' => 'integer',
        'rate' => 'double',
        'quantity' => 'double',
    ];

    public function getAmountAttribute(): float|int
    {
        return $this->attributes['quantity'] * $this->attributes['rate'];
    }

    public function scopeFilter($query, $param = [])
    {
        if (! empty($param['fiscal_year_id'])) {
            $query->where('fiscal_year_id', $param['fiscal_year_id']);
        } else {
            $query->where('fiscal_year_id', runningFiscalYear()->id);
        }
        if (! empty($param['warehouse_id'])) {
            $query->where('warehouse_id', $param['warehouse_id']);
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

    public function warehouse(): BelongsTo
    {
        return $this->belongsTo(Warehouse::class);
    }

    public function unit(): BelongsTo
    {
        return $this->belongsTo(Unit::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function productStock(): MorphOne
    {
        return $this->morphOne(ProductStock::class, 'model');
    }
}
