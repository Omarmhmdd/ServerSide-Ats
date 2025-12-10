<?php

use App\Http\Controllers\AuthController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\InterviewController;
use App\Http\Controllers\PipelineController;
use App\Http\Controllers\StageController;

  /*Route::group(["prefix" => "v0.1"]  , function(){

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
    
});*/

Route::group(["prefix" => "v0.1"], function () {

    // UNPROTECTED ROUTES
    Route::post("/login", [AuthController::class, "login"]);
    Route::post("/signup", [AuthController::class, "register"]);

    // AUTHENTICATED ROUTES
    Route::group(["prefix" => "auth", "middleware" => "auth:api"], function () {

        // INTERVIEW ROUTES
        // All authenticated users can access interviews
        Route::prefix("interviews")->group(function () {
            Route::get("/", [InterviewController::class, "index"]);
            Route::post("/", [InterviewController::class, "store"]);
            Route::get("/{id}", [InterviewController::class, "show"]);
            Route::post("/{id}/update", [InterviewController::class, "update"]);
            Route::post("/{id}/delete", [InterviewController::class, "destroy"]);
            Route::get("/candidate/{candidateId}", [InterviewController::class, "getByCandidate"]);
            Route::get("/interviewer/{interviewerId}", [InterviewController::class, "getByInterviewer"]);
            Route::post("/{id}/status", [InterviewController::class, "updateStatus"]);
        });

        // PIPELINE ROUTES
        // All authenticated users can access pipelines
        Route::prefix("pipelines")->group(function () {
            Route::get("/", [PipelineController::class, "index"]);
            Route::post("/", [PipelineController::class, "store"]);
            Route::get("/{id}", [PipelineController::class, "show"]);
            Route::post("/{id}/update", [PipelineController::class, "update"]);
            Route::post("/{id}/delete", [PipelineController::class, "destroy"]);
            Route::get("/job-role/{jobRoleId}", [PipelineController::class, "getByJobRole"]);
            Route::get("/candidate/{candidateId}", [PipelineController::class, "getByCandidate"]);
            Route::get("/stage/{stageId}", [PipelineController::class, "getByStage"]);
            Route::post("/{id}/move-stage", [PipelineController::class, "moveToStage"]);
            Route::get("/job-role/{jobRoleId}/statistics", [PipelineController::class, "getStatistics"]);
        });

        // STAGE ROUTES
        // Admin and Recruiter can manage stages
        Route::prefix("stages")->group(function () {
            Route::get("/", [StageController::class, "index"]);
            Route::post("/", [StageController::class, "store"])->middleware("role:admin,recruiter");
            Route::get("/{id}", [StageController::class, "show"]);
            Route::post("/{id}/update", [StageController::class, "update"])->middleware("role:admin,recruiter");
            Route::post("/{id}/delete", [StageController::class, "destroy"])->middleware("role:admin,recruiter");
              
            
            // Per-role stage routes
       //     Route::get("/job-role/{jobRoleId}", [StageController::class, "getStagesForJobRole"]);
         //   Route::post("/job-role/{jobRoleId}/assign", [StageController::class, "assignStagesToJobRole"])->middleware("role:admin,recruiter");
           // Route::post("/job-role/{jobRoleId}/order", [StageController::class, "updateStageOrderForJobRole"])->middleware("role:admin,recruiter");
        });

        // TODO: Add routes for other modules
        // Recruiter routes
        // Candidates routes
        // JOB ROLES routes
        // OFFERS routes
        // N8N webhook routes
    });
});