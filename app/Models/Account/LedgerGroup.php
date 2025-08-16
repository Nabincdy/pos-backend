<?php

namespace App\Models\Account;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class LedgerGroup extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $dates = [
        'created_at',
        'updated_at',
        'deleted_at',
    ];

    protected $fillable = [
        'account_head_id',
        'ledger_group_id',
        'group_name',
        'code',
        'auto_generated',
    ];

    protected $casts = [
        'account_head_id' => 'integer',
        'ledger_group_id' => 'integer',
        'auto_generated' => 'boolean',
    ];

    public function accountHead(): BelongsTo
    {
        return $this->belongsTo(AccountHead::class);
    }

    public function ledgerGroups(): HasMany
    {
        return $this->hasMany(LedgerGroup::class);
    }

    public function ledgerGroup(): BelongsTo
    {
        return $this->belongsTo(LedgerGroup::class);
    }

    public function ledgers(): HasMany
    {
        return $this->hasMany(Ledger::class);
    }
}
