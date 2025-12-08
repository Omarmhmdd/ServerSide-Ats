<?php

use App\Http\Controllers\AuthController;



Route::group(["prefix" => "v0.1"]  , function(){

    // UNPROTECTED ROUTES
    Route::post("/login" , [AuthController::class , "login"]);
    Route::post("/signup" , [AuthController::class , "signup"]);


    // AUTHINTICATABLES
    Route::group(["prefix"=>"auth" , "middleware" => "auth:api"] , function(){

        // Recruiter
        // Candidates
        // JOB ROLES
        // PIPELINE
        // N8N
        // CANDIDATES
        // OFFERS
        // INTERVIEW    
    });
    
});