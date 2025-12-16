<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Repository extends Model
{
    protected $table = 'repositories';

    protected $fillable = [
        'candidate_id',
        'name',
        'title',
        'description',
        'purpose',
        'last_updated',
        'stars',
        'forks',
        'temp_id'
    ];

    // Casts
    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function technologies(){
        return Repository::hasMany(RespositoryTechnologies::class , 'repo_temp_id' , 'temp_id');
    }
}
