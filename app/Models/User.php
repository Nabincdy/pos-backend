<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use App\Models\Hr\Employee;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens;
    use HasFactory;
    use Notifiable;
    use SoftDeletes;

    protected $dates = [
        'created_at',
        'updated_at',
        'deleted_at',
        'status_at',
    ];

    protected $fillable = [
        'name',
        'email',
        'password',
        'photo',
        'phone',
        'status_at',
        'role_id',
        'employee_id',
    ];

    protected $casts = [
        'role_id' => 'integer',
        'employee_id' => 'integer',
        'email_verified_at' => 'datetime',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    public function role(): BelongsTo
    {
        return $this->belongsTo(Role::class);
    }

    public function setPhotoAttribute($value): void
    {
        if (! empty($value) && ! is_string($value)) {
            $this->attributes['photo'] = $value->store('profile/'.Str::slug($this->attributes['name'], '_'), 'public');
        }
    }

    public function getPhotoUrlAttribute(): string
    {
        return ! empty($this->attributes['photo'])
            ? Storage::disk('public')->url($this->attributes['photo'])
            : asset('images/user_icon.jpg');
    }

    public function setPasswordAttribute($value): void
    {
        if (! empty($value)) {
            $this->attributes['password'] = bcrypt($value);
        }
    }

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }
}
