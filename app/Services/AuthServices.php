<?php

namespace App\Services;

use App\Models\User;
use Hash;

class AuthServices
{
    public static function createUser($request){
        return User::create([
            'name'     => $request->name,
            'email'    => $request->email,
            'password' => Hash::make($request->password),
        ]);
    }
}
