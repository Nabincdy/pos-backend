<?php

namespace App\Models\Account;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class JournalParticular extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $dates = [
        'created_at',
        'updated_at',
        'deleted_at',
    ];

    protected $fillable = [
        'journal_id',
        'ledger_id',
        'date',
        'dr_amount',
        'cr_amount',
        'remarks',
        'is_cancelled',
    ];

    protected $casts = [
        'dr_amount' => 'double',
        'cr_amount' => 'double',
        'is_cancelled' => 'boolean',
    ];

    public function scopeFilterData($query, $param = [])
    {
        if (! empty($param['fiscal_year_id'])) {
            $query->whereHas('journal', function ($q) use ($param) {
                $q->where('fiscal_year_id', $param['fiscal_year_id']);
            });
        } else {
            $query->whereHas('journal', function ($q) {
                $q->where('fiscal_year_id', runningFiscalYear()->id);
            });
        }

        if (! empty($param['ledger_id'])) {
            $query->where('ledger_id', $param['ledger_id']);
        }

        if (! empty($param['from_date'])) {
            $query->whereDate('date', '>=', $param['from_date']);
        }
        if (! empty($param['to_date'])) {
            $query->whereDate('date', '<=', $param['to_date']);
        }

        return $query;
    }

    public function journal(): BelongsTo
    {
        return $this->belongsTo(Journal::class);
    }

    public function ledger(): BelongsTo
    {
        return $this->belongsTo(Ledger::class);
    }
}
