<?php

namespace App\Services;

use App\Models\User;

class HiringManagerServices
{
    /**
     * Create a new class instance.
     */
    public function __construct()
    {
        //
    }

    static function getHiringMangers(){
            $managers = User::where('role_id', 3)->get(['id', 'name']);
        return $managers;
    }
}
