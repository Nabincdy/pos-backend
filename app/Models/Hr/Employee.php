<?php

namespace App\Models\Hr;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class Employee extends Model
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
        'gender',
        'dob',
        'rank',
        'email',
        'phone',
        'photo',
        'department_id',
        'designation_id',
        'joining_date',
        'marital_status',
        'citizenship_no',
        'pan_no',
        'signature',
        'address',
        'status',
    ];

    protected $casts = [
        'department_id' => 'integer',
        'designation_id' => 'integer',
        'rank' => 'integer',
        'status' => 'boolean',
    ];

    public function getPhotoUrlAttribute(): string
    {
        return ! empty($this->attributes['photo'])
            ? Storage::disk('public')->url($this->attributes['photo'])
            : asset('images/user_icon.jpg');
    }

    public function setPhotoAttribute($value): void
    {
        if (! empty($value) && ! is_string($value)) {
            $this->attributes['photo'] = $value->store('employee/'.Str::slug($this->attributes['name'], '_').'/photo', 'public');
        }
    }

    public function getSignatureUrlAttribute(): string
    {
        return ! empty($this->attributes['signature'])
            ? Storage::disk('public')->url($this->attributes['signature'])
            : '';
    }

    public function setSignatureAttribute($value): void
    {
        if (! empty($value) && ! is_string($value)) {
            $this->attributes['signature'] = $value->store('employee/'.Str::slug($this->attributes['name'], '_').'/signature', 'public');
        }
    }

    public function designation(): BelongsTo
    {
        return $this->belongsTo(Designation::class);
    }

    public function department(): BelongsTo
    {
        return $this->belongsTo(Department::class);
    }

    public function user(): HasOne
    {
        return $this->hasOne(User::class);
    }

    public function employeeAttendances(): HasMany
    {
        return $this->hasMany(EmployeeAttendance::class);
    }

    public function employeeAttendance(): HasOne
    {
        return $this->hasOne(EmployeeAttendance::class);
    }

    public function employeeSalaries(): HasMany
    {
        return $this->hasMany(EmployeeSalary::class);
    }

    public function latestSalary(): HasOne
    {
        return $this->hasOne(EmployeeSalary::class)->latest('effective_from');
    }

    public function payableCharges(): HasMany
    {
        return $this->hasMany(PayableCharge::class);
    }

    public function advanceSalaries(): HasMany
    {
        return $this->hasMany(AdvanceSalary::class);
    }

    public function salaryLedgers(): HasMany
    {
        return $this->hasMany(SalaryLedger::class);
    }
}
