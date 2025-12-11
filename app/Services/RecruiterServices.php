<?php

namespace App\Services;

use App\Models\User;

class RecruiterServices
{
    /**
     * Create a new class instance.
     */
    public function __construct()
    {
        //
    }

    static function getRecuiters(){
        $recruiters = User::where('role_id', 2)->get(['id', 'name']);
        return $recruiters;
    }
}
