<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\HiringManagerController;
use App\Http\Controllers\JobRoleController;
use App\Http\Controllers\RecruiterController;
use Illuminate\Support\Facades\Route;



Route::group(["prefix" => "v0.1"]  , function(){

    // UNPROTECTED ROUTES
    Route::post("/login" , [AuthController::class , "login"]);
    Route::post("/signup" , [AuthController::class , "register"]);


    // AUTHINTICATABLES
    Route::group(["prefix"=>"auth" , "middleware" => "auth:api"] , function(){
        Route::get("/recruiters", [RecruiterController::class,"getRecruiters"]);
        Route::get("/hiring_managers", [HiringManagerController::class,"getHiringManagers"]);


        // Recruiter
        // Candidates
        // JOB ROLES
        Route::group(["prefix"=>"job_roles"] , function(){
            Route::get("/levels", [JobRoleController::class,"getLevels"]);
            Route::get("/{id?}", [JobRoleController::class,"getJobRoles"]);
            Route::post("/add_update_job_role", [JobRoleController::class,"addOrUpdateJobRole"]);
            Route::post("/delete_role/{id?}", [JobRoleController::class,"deleteJobRole"]);
        });

        // PIPELINE
        // N8N
        // CANDIDATES
        // OFFERS
        // INTERVIEW
    });

});
