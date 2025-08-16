<?php

namespace App\Models\Inventory;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Laravolt\Avatar\Avatar;

class ProductCategory extends Model
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
        'image',
        'description',
    ];

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
            $this->attributes['image'] = $value->store('category/'.Str::slug($this->attributes['name'], '_'), 'public');
        }
    }

    public function productCategory(): BelongsTo
    {
        return $this->belongsTo(ProductCategory::class);
    }

    public function productCategories(): HasMany
    {
        return $this->hasMany(ProductCategory::class);
    }

    public function products(): HasMany
    {
        return $this->hasMany(Product::class);
    }
}
