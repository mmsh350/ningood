<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Service extends Model
{
    use HasFactory;

    protected $fillable = [
        'service_code',
        'name',
        'category',
        'type',
        'amount',
        'description',
        'status',
    ];

    public function bankServices(): HasMany
    {
        return $this->hasMany(BankService::class);
    }

    public function banks()
    {
        return $this->belongsToMany(Bank::class, 'bank_services')
            ->withPivot('price', 'commission', 'status')
            ->withTimestamps();
    }

    public function userPrices()
    {
        return $this->hasMany(UserServicePrice::class);
    }
}
