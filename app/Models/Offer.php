<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Log;

class Offer extends Model
{
        protected static function booted()
    {
        static::updated(function ($offer) {
            // Track status changes
            if ($offer->isDirty('status')) {
                $oldStatus = $offer->getOriginal('status');
                $newStatus = $offer->status;
                
                Log::info('Offer status changed', [
                    'offer_id' => $offer->id,
                    'candidate_id' => $offer->candidate_id,
                    'role_id' => $offer->role_id,
                    'old_status' => $oldStatus,
                    'new_status' => $newStatus,
                    'changed_at' => now()->toDateTimeString()
                ]);
            }
        });
    }

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

     public function candidate()
    {
        return $this->belongsTo(Candidate::class, 'candidate_id');
    }

    
    public function jobRole()
    {
        return $this->belongsTo(JobRole::class, 'role_id');
    }


    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

}
