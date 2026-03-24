<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Enrollment extends Model
{
    protected $table = 'bvn_enrollments';

    protected $fillable = [
        'user_id',
        'refno',
        'fullname',
        'state',
        'lga',
        'address',
        'city',
        'bvn',
        'account_number',
        'account_name',
        'bank_name',
        'email',
        'phone_number',
        'username',
        'status',
        'reason',
        'tnx_id',
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
