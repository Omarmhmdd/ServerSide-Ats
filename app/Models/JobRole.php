<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Notifiable;

class JobRole extends Model
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
        'is_on_site',
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

/**
 * Get job role stages pivot entries
 */


}
