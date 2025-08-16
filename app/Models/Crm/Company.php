<?php

namespace App\Models\Crm;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Laravolt\Avatar\Avatar;

class Company extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $dates = [
        'created_at',
        'updated_at',
        'deleted_at',
    ];

    protected $fillable = [
        'company_name',
        'logo',
        'code',
        'phone',
        'email',
        'landline',
        'vat_pan_no',
        'address',
    ];

    public function getLogoUrlAttribute()
    {
        return ! empty($this->attributes['logo'])
            ? Storage::disk('public')->url($this->attributes['logo'])
            : (new Avatar())
                ->create($this->attributes['company_name'])
                ->setTheme(['grayscale-light', 'grayscale-dark'])
                ->setShape('square')
                ->toBase64();
    }

    public function setLogoAttribute($value)
    {
        if (! empty($value) && ! is_string($value)) {
            $this->attributes['logo'] = $value->store('company/'.Str::slug($this->attributes['company_name'], '_').'/logo', 'public');
        }
    }

    public function clients(): HasMany
    {
        return $this->hasMany(Client::class);
    }

    public function suppliers(): HasMany
    {
        return $this->hasMany(Supplier::class);
    }
}
