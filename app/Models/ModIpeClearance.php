<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ModIpeClearance extends Model
{
    protected $fillable = [
        'user_id',
        'nin_number',
        'refno',
        'tnx_id',
        'tracking_id',
        'reason',
        'status',
        'refunded_at',
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
