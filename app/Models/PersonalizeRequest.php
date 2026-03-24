<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PersonalizeRequest extends Model
{
    protected $fillable = [
        'user_id',
        'refno',
        'reply',
        'tnx_id',
        'tracking_no',
        'status',
        'refunded_at',
        'comments',
        'name',
        'nin',
        'tag',
        'resp_code',
    ];
}
