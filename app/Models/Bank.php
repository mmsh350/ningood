<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Bank extends Model
{
    protected $fillable = ['name', 'is_active'];

    public function bankServices(): HasMany
    {
        return $this->hasMany(BankService::class);
    }

    public function services()
    {
        return $this->belongsToMany(Service::class, 'bank_services')
            ->withPivot('price', 'commission', 'status')
            ->withTimestamps();
    }
}
