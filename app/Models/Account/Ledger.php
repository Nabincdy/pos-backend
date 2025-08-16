<?php

namespace App\Models\Account;

use App\Models\Inventory\Purchase;
use App\Models\Inventory\Sale;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Ledger extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $dates = [
        'created_at',
        'updated_at',
        'deleted_at',
    ];

    protected $fillable = [
        'ledger_group_id',
        'ledger_id',
        'ledger_name',
        'code',
        'category',
        'address',
        'phone',
        'email',
        'pan_no',
        'status',
        'auto_generated',
    ];

    protected $casts = [
        'ledger_id' => 'integer',
        'ledger_group_id' => 'integer',
        'status' => 'boolean',
        'auto_generated' => 'boolean',
    ];

    public function ledgerGroup(): BelongsTo
    {
        return $this->belongsTo(LedgerGroup::class);
    }

    public function ledger(): BelongsTo
    {
        return $this->belongsTo(Ledger::class);
    }

    public function subLedgers(): HasMany
    {
        return $this->hasMany(Ledger::class, 'ledger_id');
    }

    public function accountOpeningBalances(): HasMany
    {
        return $this->hasMany(AccountOpeningBalance::class);
    }

    public function journalParticulars(): HasMany
    {
        return $this->hasMany(JournalParticular::class);
    }

    public function purchases(): HasMany
    {
        return $this->hasMany(Purchase::class, 'supplier_ledger_id');
    }

    public function sales(): HasMany
    {
        return $this->hasMany(Sale::class, 'client_ledger_id');
    }
}
