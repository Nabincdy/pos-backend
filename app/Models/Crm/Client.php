<?php

namespace App\Models\Crm;

use App\Models\Account\Ledger;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class Client extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $dates = [
        'created_at',
        'updated_at',
        'deleted_at',
    ];

    protected $fillable = [
        'client_group_id',
        'name',
        'code',
        'phone',
        'email',
        'ledger_id',
        'profile_photo',
        'company_id',
        'pan_no',
        'address',
        'status',
    ];

    protected $casts = [
        'client_group_id' => 'integer',
        'company_id' => 'integer',
        'status' => 'boolean',
    ];

    public function getProfilePhotoUrlAttribute(): string
    {
        return ! empty($this->attributes['profile_photo'])
            ? Storage::disk('public')->url($this->attributes['profile_photo'])
            : asset('images/user_icon.jpg');
    }

    public function setProfilePhotoAttribute($value)
    {
        if (! empty($value) && ! is_string($value)) {
            $this->attributes['profile_photo'] = $value->store('client/'.Str::slug($this->attributes['name'], '_').'/photo', 'public');
        }
    }

    public function clientGroup(): BelongsTo
    {
        return $this->belongsTo(ClientGroup::class);
    }

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function ledger(): BelongsTo
    {
        return $this->belongsTo(Ledger::class);
    }
}
