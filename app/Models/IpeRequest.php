<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class IpeRequest extends Model
{
    protected $fillable = [
        'user_id',
        'tnx_id',
        'trackingId',
        'reply',
        'status',
        'refunded_at',
        'tag',
        'resp_code',
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function transactions()
    {
        return $this->belongsTo(Transaction::class, 'tnx_id');
    }
}
