<?php

namespace App\Http\Controllers;

use App\Http\Requests\LoginRequest;
use App\Http\Requests\RegisterRequest;
use App\Services\AuthServices;
use Exception;
use Illuminate\Support\Facades\Auth;

class AuthController extends Controller{
     public function login(LoginRequest $request){

        if (!$token = Auth::guard('api')->attempt($request->validated())) {
            return $this->errorResponse("Invalid credentials");
        }

        return $this->successResponse(["token" => $token , "user" => Auth::guard('api')->user()]);
    }

    public function register(RegisterRequest $request){
        try{
            $user = AuthServices::createUser($request);
            $token = Auth::guard('api')->login($user);

            return $this->successResponse(["token" => $token]);
        }catch(Exception $ex){
            return $this->errorResponse("Failed to signup");
        }
    }
}
