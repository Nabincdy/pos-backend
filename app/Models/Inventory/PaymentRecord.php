<?php

namespace App\Models\Inventory;

use App\Models\Account\Journal;
use App\Models\Account\Ledger;
use App\Models\Setting\FiscalYear;
use App\Models\User;
use App\Traits\NepaliDateConverter;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphOne;
use Illuminate\Database\Eloquent\SoftDeletes;

class PaymentRecord extends Model
{
    use HasFactory;
    use SoftDeletes;
    use NepaliDateConverter;

    protected $dates = [
        'created_at',
        'updated_at',
        'deleted_at',
    ];

    protected $fillable = [
        'purchase_id',
        'fiscal_year_id',
        'invoice_no',
        'supplier_ledger_id',
        'payment_method',
        'cash_bank_ledger_id',
        'payment_date',
        'en_payment_date',
        'paid_amount',
        'remarks',
        'create_user_id',
        'is_cancelled',
        'cancelled_reason',
        'cancel_user_id',
    ];

    protected $casts = [
        'paid_amount' => 'double',
        'is_cancelled' => 'boolean',
    ];

    protected $appends = [
        'en_payment_date',
    ];

    public function getEnPaymentDateAttribute(): string
    {
        $date = explode('-', $this->payment_date);
        $eng_date = $this->get_eng_date($date[0], $date[1], $date[2]);

        return $eng_date['y'].'-'.$eng_date['m'].'-'.$eng_date['d'];
    }

    public function scopeFilterData($query, $param = [])
    {
        if (! empty($param['fiscal_year_id'])) {
            $query->where('fiscal_year_id', $param['fiscal_year_id']);
        } else {
            $query->where('fiscal_year_id', runningFiscalYear()->id);
        }

        if (! empty($param['search'])) {
            $key = '%'.trim($param['search']).'%';
            $query->where('invoice_no', 'like', $key);
            $query->orWhereHas('supplierLedger', function ($q) use ($key) {
                $q->where('ledger_name', 'like', $key);
            });
        }

        if (! empty($param['supplier_ledger_id'])) {
            $query->where('supplier_ledger_id', $param['supplier_ledger_id']);
        }

        if (! empty($param['payment_status']) && $param['payment_status'] == 'Cancelled') {
            $query->where('is_cancelled', 1);
        } else {
            $query->where('is_cancelled', 0);
        }
        if (! empty($param['from_date'])) {
            $query->whereDate('payment_date', '>=', $param['from_date']);
        }
        if (! empty($param['to_date'])) {
            $query->whereDate('payment_date', '<=', $param['to_date']);
        }

        return $query;
    }

    public function purchase(): BelongsTo
    {
        return $this->belongsTo(Purchase::class);
    }

    public function fiscalYear(): BelongsTo
    {
        return $this->belongsTo(FiscalYear::class);
    }

    public function cashBankLedger(): BelongsTo
    {
        return $this->belongsTo(Ledger::class, 'cash_bank_ledger_id');
    }

    public function supplierLedger(): BelongsTo
    {
        return $this->belongsTo(Ledger::class, 'supplier_ledger_id');
    }

    public function createUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'create_user_id');
    }

    public function cancelUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'cancel_user_id');
    }

    public function journal(): MorphOne
    {
        return $this->morphOne(Journal::class, 'model');
    }
}
