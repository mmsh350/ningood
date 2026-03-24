<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class NinValidation extends Model
{
    protected $fillable = [
        'user_id',
        'tnx_id',
        'refno',
        'nin_number',
        'email',
        'description',
        'status',
        'reason',
        'tag',
        'tracking_no',
        'refunded_at',
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
