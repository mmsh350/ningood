<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ModificationRequest extends Model
{
    protected $fillable = [
        'user_id',
        'refno',
        'bvn_no',
        'nin_number',
        'bank_id',
        'service_id',
        'modification_data',
        'base_price',
        'commission',
        'total_price',
        'status',
        'reason',
        'refunded_at',
    ];

    protected $casts = [
        'modification_data' => 'array',
        'base_price' => 'decimal:2',
        'commission' => 'decimal:2',
        'total_price' => 'decimal:2',
    ];

    public function bank()
    {
        return $this->belongsTo(Bank::class);
    }

    public function service()
    {
        return $this->belongsTo(Service::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function transactions()
    {
        return $this->belongsTo(Transaction::class, 'refno', 'referenceId');

    }
}
