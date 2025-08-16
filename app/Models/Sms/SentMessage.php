<?php

namespace App\Models\Sms;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class SentMessage extends Model
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
        'phone',
        'message',
        'response_data',
    ];

    public function scopeFilterData($query, $param = [])
    {
        if (! empty($param['from_date'])) {
            $query->whereDate('created_at', '>=', $param['from_date']);
        }
        if (! empty($param['to_date'])) {
            $query->whereDate('created_at', '<=', $param['to_date']);
        }

        return $query;
    }
}
