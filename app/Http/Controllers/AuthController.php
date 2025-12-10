<?php

namespace App\Http\Controllers;

use App\Http\Requests\LoginRequest;
use App\Http\Requests\RegisterRequest;
use App\Services\AuthServices;
use Exception;


class AuthController extends Controller{
     public function login(LoginRequest $request){

        if (! $token = auth()->attempt($request->validated())) {
            return $this->errorResponse("Invalid credentials");
        }
             
        return $this->successResponse(["token" => $token , "user" => auth()->user()]);
    }

    public function register(RegisterRequest $request){
        try{
            $user = AuthServices::createUser($request);
            $token = auth()->login($user);

            return $this->successResponse(["token" => $token]);
        }catch(Exception $ex){
            return $this->errorResponse("Failed to signup");
        }
    }
}
