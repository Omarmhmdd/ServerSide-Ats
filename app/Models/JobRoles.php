<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Notifiable;
   use App\Models\CustomStage;
class JobRoles extends Model
{
     /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable;


    protected $fillable = [
        'recruiter_id',
        'level_id',
        'hiring_manager_id',
        'location',
        'title',
        'description',
        'is_remote',
        'is_on_sight',
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];
     public function customStages()
    {
        return $this->hasMany(CustomStage::class, 'job_role_id')->orderBy('order');
    }

    
    public function getFirstCustomStage()
    {
        return $this->customStages()->orderBy('order')->first();
    }
}


