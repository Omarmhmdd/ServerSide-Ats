<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Project extends Model
{
    protected $fillable = [
        'candidate_id',
        'title',
        'description',
        'start_date',
        'start_date',
        'end_date',
        'project_url',
        'temp_id'
    ];

    // Casts
    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function skills(){
        return Project::hasMany(ProjectSkills::class , 'project_temp_id' , 'temp_id');
    }
}
