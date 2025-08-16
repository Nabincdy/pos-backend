<?php

namespace App\Models\Inventory;

use App\Models\Setting\Tax;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class PurchaseReturnParticular extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $dates = [
        'created_at',
        'updated_at',
        'deleted_at',
    ];

    protected $fillable = [
        'purchase_return_id',
        'product_id',
        'unit_id',
        'warehouse_id',
        'quantity',
        'rate',
        'purchase_tax_id',
        'purchase_tax_amount',
        'discount_amount',
    ];

    protected $casts = [
        'quantity' => 'double',
        'rate' => 'double',
        'purchase_tax_amount' => 'double',
        'discount_amount' => 'double',
    ];

    protected $appends = [
        'sub_total',
        'amount_excl_tax',
        'total_amount',
    ];

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
        return $this->getSubTotalAttribute() - $this->discount_amount + $this->purchase_tax_amount;
    }

    public function purchaseReturn(): BelongsTo
    {
        return $this->belongsTo(PurchaseReturn::class);
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
