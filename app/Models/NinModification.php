<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class NinModification extends Model
{
    protected $fillable = [
        'user_id',
        'tnx_id',
        'refno',
        'nin_number',
        'photo',
        'first_name',
        'middle_name',
        'surname',
        'dob',
        'phone_number',
        'address',
        'status',
        'description',
        'reason',
        'origin_address',
        'full_address',
        'documents',
        'affidavit',
        'state',
        'lga',
        'education_qualification',
        'marital_status',
        'father_full_name',
        'father_state_of_origin',
        'father_lga_of_origin',
        'mother_full_name',
        'mother_state_of_origin',
        'mother_lga_of_origin',
        'mother_maiden_name',
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
