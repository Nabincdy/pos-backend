<?php

namespace App\Models\Inventory;

use App\Models\Setting\Tax;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Laravolt\Avatar\Avatar;

class Product extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $dates = [
        'created_at',
        'updated_at',
        'deleted_at',
    ];

    protected $fillable = [
        'product_category_id',
        'name',
        'code',
        'sku',
        'product_type',
        'reorder_quantity',
        'barcode',
        'unit_id',
        'brand_id',
        'purchase_rate',
        'purchase_tax_id',
        'sales_rate',
        'sales_tax_id',
        'image',
        'description',
        'status',
    ];

    protected $casts = [
        'product_category_id' => 'integer',
        'reorder_quantity' => 'integer',
        'unit_id' => 'integer',
        'brand_id' => 'integer',
        'purchase_ledger_id' => 'integer',
        'purchase_rate' => 'double',
        'purchase_tax_id' => 'integer',
        'sales_ledger_id' => 'integer',
        'sales_rate' => 'double',
        'sales_tax_id' => 'integer',
        'status' => 'boolean',
    ];

    public function scopeFilterData($query, $param = [])
    {
        if (! empty($param['search'])) {
            $key = '%'.trim($param['search']).'%';
            $query->where('name', 'like', $key);
            $query->orWhere('code', 'like', $key);
            $query->orWhere('sku', 'like', $key);
            $query->orWhere('barcode', 'like', $key);
        }

        if (! empty($param['product_category_id'])) {
            $query->where('product_category_id', $param['product_category_id']);
        }

        if (! empty($param['brand_id'])) {
            $query->where('brand_id', $param['brand_id']);
        }

        return $query;
    }

    public function getImageUrlAttribute()
    {
        return ! empty($this->attributes['image'])
            ? Storage::disk('public')->url($this->attributes['image'])
            : (new Avatar())
                ->create($this->attributes['name'])
                ->setTheme(['grayscale-light', 'grayscale-dark'])
                ->setShape('square')
                ->toBase64();
    }

    public function setImageAttribute($value)
    {
        if (! empty($value) && ! is_string($value)) {
            $this->attributes['image'] = $value->store('product/'.Str::slug($this->attributes['name'], '_'), 'public');
        }
    }

    public function productCategory(): BelongsTo
    {
        return $this->belongsTo(ProductCategory::class);
    }

    public function unit(): BelongsTo
    {
        return $this->belongsTo(Unit::class);
    }

    public function brand(): BelongsTo
    {
        return $this->belongsTo(Brand::class);
    }

    public function purchaseTax(): BelongsTo
    {
        return $this->belongsTo(Tax::class, 'purchase_tax_id');
    }

    public function salesTax(): BelongsTo
    {
        return $this->belongsTo(Tax::class, 'sales_tax_id');
    }

    public function productStocks(): HasMany
    {
        return $this->hasMany(ProductStock::class);
    }

    public function purchaseParticulars(): HasMany
    {
        return $this->hasMany(PurchaseParticular::class);
    }

    public function saleParticulars(): HasMany
    {
        return $this->hasMany(SaleParticular::class);
    }
}
