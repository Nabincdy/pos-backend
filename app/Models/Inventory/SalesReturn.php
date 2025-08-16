<?php

namespace App\Models\Inventory;

use App\Models\Account\Journal;
use App\Models\Account\Ledger;
use App\Models\Setting\FiscalYear;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\Relations\MorphOne;
use Illuminate\Database\Eloquent\SoftDeletes;

class SalesReturn extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $dates = [
        'created_at',
        'updated_at',
        'deleted_at',
    ];

    protected $fillable = [
        'fiscal_year_id',
        'sale_id',
        'invoice_no',
        'return_date',
        'client_ledger_id',
        'create_user_id',
        'remarks',
    ];

    protected $casts = [
        'client_ledger_id' => 'integer',
    ];

    public function scopeFilterData($query, $param = [])
    {
        if (! empty($param['fiscal_year_id'])) {
            $query->where('fiscal_year_id', $param['fiscal_year_id']);
        } else {
            $query->where('fiscal_year_id', runningFiscalYear()->id);
        }

        if (! empty($param['client_ledger_id'])) {
            $query->where('client_ledger_id', $param['client_ledger_id']);
        }

        if (! empty($param['search'])) {
            $key = '%'.trim($param['search']).'%';
            $query->where('invoice_no', 'like', $key);
            $query->orWhereHas('clientLedger', function ($q) use ($key) {
                $q->where('ledger_name', 'like', $key);
            });
        }

        if (! empty($param['from_date'])) {
            $query->whereDate('return_date', '>=', $param['from_date']);
        }
        if (! empty($param['to_date'])) {
            $query->whereDate('return_date', '<=', $param['to_date']);
        }

        return $query;
    }

    public function fiscalYear(): BelongsTo
    {
        return $this->belongsTo(FiscalYear::class);
    }

    public function clientLedger(): BelongsTo
    {
        return $this->belongsTo(Ledger::class, 'client_ledger_id');
    }

    public function createUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'create_user_id');
    }

    public function salesReturnParticulars(): HasMany
    {
        return $this->hasMany(SalesReturnParticular::class);
    }

    public function productStocks(): MorphMany
    {
        return $this->morphMany(ProductStock::class, 'model');
    }

    public function journal(): MorphOne
    {
        return $this->morphOne(Journal::class, 'model');
    }
}
