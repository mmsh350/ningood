<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class SiteSetting extends Model
{
    use HasFactory;

    protected $fillable = [
        'site_name',
        'short_name',
        'logo',
        'mini_logo',
        'favicon',
        'login_background_image',
        'registration_background_image',
        'home_enabled',
        'login_enabled',
        'register_enabled',
        'nin_mod_enabled',
        'nin_consent',
        'bvn_consent',
        'whatsapp_url',
    ];

    protected static function booted()
    {
        static::saved(function () {
            Cache::forget('site-settings');
        });

        static::deleted(function () {
            Cache::forget('site-settings');
        });
    }
}
