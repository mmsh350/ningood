<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserServicePrice extends Model
{
    protected $fillable = [
        'user_id',
        'service_id',
        'custom_price',
        'valid_from',
        'valid_to',
    ];

    public function service()
    {
        return $this->belongsTo(Service::class);
    }
}
