<?php

namespace App\Models\Inventory;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Laravolt\Avatar\Avatar;

class Brand extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $dates = [
        'created_at',
        'updated_at',
        'deleted_at',
    ];

    protected $fillable = [
        'name',
        'code',
        'logo',
    ];

    public function getLogoUrlAttribute()
    {
        return ! empty($this->attributes['logo'])
            ? Storage::disk('public')->url($this->attributes['logo'])
            : (new Avatar())
                ->create($this->attributes['name'])
                ->setTheme(['grayscale-light', 'grayscale-dark'])
                ->setShape('square')
                ->toBase64();
    }

    public function setLogoAttribute($value)
    {
        if (! empty($value) && ! is_string($value)) {
            $this->attributes['logo'] = $value->store('brand/'.Str::slug($this->attributes['name'], '_').'/logo', 'public');
        }
    }
}
