<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\CandidateController;
use App\Http\Controllers\CandidateImportController;



Route::group(["prefix" => "v0.1"]  , function(){

    // UNPROTECTED ROUTES
    Route::post("/login" , [AuthController::class , "login"]);
    Route::post("/signup" , [AuthController::class , "register"]);


    // AUTHINTICATABLES
    Route::group(["prefix"=>"auth" , "middleware" => "auth:api"] , function(){

    });

    Route::group(["prefix" => "n8n"] , function(){
        Route::get("/candidatesData" , [CandidateController::class , 'getCandidateData']);
    });

    // CANDIDATES
    Route::group(["prefix" => "candidate"] , function(){
        Route::post("/import" , [CandidateImportController::class , "import"]);
        Route::post("/saveMetaData" , [CandidateController::class , "saveMetaData"]);
    });
    
});