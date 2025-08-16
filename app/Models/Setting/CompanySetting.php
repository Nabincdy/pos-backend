<?php

namespace App\Models\Setting;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Storage;
use Laravolt\Avatar\Avatar;

class CompanySetting extends Model
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
        'phone',
        'email',
        'address',
        'logo',
        'pan_no',
        'website_url',
        'facebook_url',
        'youtube_url',
        'bank_account',
        'journal_voucher',
        'payment_voucher',
        'receipt_voucher',
        'client_group',
        'client',
        'company',
        'supplier',
        'product',
        'product_category',
        'warehouse',
        'purchase',
        'purchase_return',
        'sales',
        'sales_return',
        'employee',
        'payable_charge',
        'payslip',
        'tax',
        'supplier_payment',
        'client_payment',
        'quotation',
    ];

    public function getLogoUrlAttribute(): string
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
            $this->attributes['logo'] = $value->store('logo', 'public');
        }
    }
}
