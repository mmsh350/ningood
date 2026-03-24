<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BankService extends Model
{
    protected $fillable = [
        'bank_id',
        'service_id',
        'price',
        'commission',
        'is_active',
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'commission' => 'decimal:2',
    ];

    public function bank()
    {
        return $this->belongsTo(Bank::class);
    }

    public function service()
    {
        return $this->belongsTo(Service::class);
    }

    public function getTotalPriceAttribute()
    {
        return $this->price + ($this->commission ?? 0);
    }
}
