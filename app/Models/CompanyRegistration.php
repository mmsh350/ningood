<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CompanyRegistration extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'director_surname',
        'director_firstname',
        'director_othername',
        'director_dob',
        'director_gender',
        'director_email',
        'director_phone',
        'director_nin',
        'res_state',
        'res_lga',
        'res_city',
        'res_house_number',
        'res_street_name',
        'res_description',
        'bus_state',
        'bus_lga',
        'bus_city',
        'bus_house_number',
        'bus_street_name',
        'bus_description',
        'nature_of_business',
        'business_name_1',
        'business_name_2',
        'business_email',
        'witness_surname',
        'witness_firstname',
        'witness_othername',
        'witness_phone',
        'witness_email',
        'witness_nin',
        'witness_address',
        'shareholder_surname',
        'shareholder_firstname',
        'shareholder_othername',
        'shareholder_dob',
        'shareholder_gender',
        'shareholder_nationality',
        'shareholder_phone',
        'shareholder_email',
        'shareholder_nin',
        'shareholder_address',
        'director_signature_path',
        'witness_signature_path',
        'shareholder_signature_path',
        'refno',
        'tnx_id',
        'status',
        'admin_comment',
        'response_documents',
        'refunded_at',
    ];

    protected $casts = [
        'director_dob' => 'date',
        'shareholder_dob' => 'date',
        'refunded_at' => 'datetime',
        'response_documents' => 'array',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function transaction()
    {
        return $this->belongsTo(Transaction::class, 'tnx_id');
    }
}
