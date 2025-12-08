<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Offer extends Model
{
    protected $fillable = [
        'candidate_id',
        'created_by',
        'role_id',
        'base_salary',
        'equity',
        'bonus',
        'benifits',
        'start_date',
        'contract_type',
        'status',
        'expiry_date',
        'sent_at',
        'responded_at',
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

}
