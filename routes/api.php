<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\HiringManagerController;
use App\Http\Controllers\JobRoleController;
use App\Http\Controllers\RecruiterController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\CandidateController;
use App\Http\Controllers\CandidateImportController;
use App\Http\Controllers\InterviewController;
// use App\Http\Controllers\InterviewController;
use App\Http\Controllers\PipelineController;
use App\Http\Controllers\StageController;

Route::group(["prefix" => "v0.1"], function () {

    // UNPROTECTED ROUTES
    Route::post("/login" , [AuthController::class , "login"]);
    Route::post("/signup" , [AuthController::class , "register"]);
    Route::post('/interviews/create_scorecard', [InterviewController::class, 'createScoreCard']);

    // AUTHENTICATED ROUTES
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


        // CANDIDATES
        Route::group(["prefix" => "candidate"] , function(){
            Route::post("/import" , [CandidateImportController::class , "import"]);
        });

        // INTERVIEW ROUTES
        // All authenticated users can access interviews
        Route::prefix("interviews")->group(function () {
            Route::get("/", [InterviewController::class, "index"]);
            Route::post("/", [InterviewController::class, "store"]);
            Route::get("/{id}", [InterviewController::class, "show"]);
            Route::post("/{id}/complete", [InterviewController::class, "MarkAsComplete"]);
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

    // N8N
    Route::group(["prefix" => "n8n"] , function(){
        Route::get("/candidatesData" , [CandidateController::class , 'getCandidateData']);
        Route::post("/saveMetaData" , [CandidateController::class , "saveMetaData"]);
        Route::get("/createScreening/{candidate_id}" ,[InterviewController::class , 'createScreening']);
    });
    });
});
