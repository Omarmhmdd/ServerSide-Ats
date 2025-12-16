<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\HiringManagerController;
use App\Http\Controllers\JobRoleController;
use App\Http\Controllers\OfferController;
use App\Http\Controllers\RecruiterController;
use App\Services\Candidate\CandidateService;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\CandidateController;
use App\Http\Controllers\CandidateImportController;
use App\Http\Controllers\InterviewController;
use App\Http\Controllers\RagCopilotController;
use App\Http\Controllers\PipelineController;
use App\Http\Controllers\CustomStageController;

Route::group(["prefix" => "v0.1"], function () {

    // UNPROTECTED ROUTES
    Route::post("/login" , [AuthController::class , "login"]);
    Route::post("/signup" , [AuthController::class , "register"]);

    // AUTHENTICATED ROUTES
    Route::group(["prefix"=>"auth" , "middleware" => "auth:api"] , function(){

        Route::get("/recruiters", [RecruiterController::class,"getRecruiters"]);
        Route::get("/hiring_managers", [HiringManagerController::class,"getHiringManagers"]);


        // JOB ROLES
        Route::group(["prefix"=>"job_roles"] , function(){
            Route::get("/levels", [JobRoleController::class,"getLevels"]);
            Route::get("/{id?}", [JobRoleController::class,"getJobRoles"]);
            Route::post("/add_update_job_role", [JobRoleController::class,"addOrUpdateJobRole"]);
            Route::post("/delete_role/{id?}", [JobRoleController::class,"deleteJobRole"]);
            Route::get('/getCandidateInEachRole' , [JobRoleController::class , 'getCandidateCountsInEachRole']);
            Route::get('/getCandidateInEachStage' , [JobRoleController::class , "getCandidateInEachStage"]);
        });
        
        // CANDIDATES
        Route::group(["prefix" => "candidate"] , function(){
            Route::post("/import" , [CandidateImportController::class , "import"]);
            Route::get('/getDetails/{candidate_id}' , [CandidateController::class , 'getMetaData']);
            Route::get('/getCandidates/allJobRoles/{recruiter_id}' , [CandidateController::class , 'getCandidatesAllJobRoles']);
            Route::get('/getCandidateProgress/{candidate_id}' , [CandidateController::class , 'getCandidateProgress']);
            Route::get('/getInterviews/{candidate_id}' , [CandidateController::class , 'getCandidateInterview']);
            Route::get('/getStatistics' , [CandidateController::class , 'getStatistics']);
        });

        Route::group(["prefix" => "offer"] , function(){
            Route::post("/create" , [OfferController::class , 'createOffer']);
        });

        // COPILOT
        Route::group(["prefix" => "copilot"] , function(){
            Route::post("/ask" , [RagCopilotController::class , "ask"]);
        });
    });


        // INTERVIEW ROUTES
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
        Route::prefix("pipelines")->group(function () {
            Route::get("/", [PipelineController::class, "index"]);
            Route::post("/", [PipelineController::class, "store"]);
            Route::get("/{id}", [PipelineController::class, "show"]);
            Route::post("/{id}/update", [PipelineController::class, "update"]);
            Route::post("/{id}/delete", [PipelineController::class, "destroy"]);
            Route::get("/job-role/{jobRoleId}", [PipelineController::class, "getByJobRole"]);
            Route::get("/candidate/{candidateId}", [PipelineController::class, "getByCandidate"]);
            Route::get("/stage/{stageId}", [PipelineController::class, "getByStage"]);
         //   Route::post("/{id}/move-stage", [PipelineController::class, "moveToStage"]);
        //    Route::get("/job-role/{jobRoleId}/statistics", [PipelineController::class, "getStatistics"]);
            Route::post("/{id}/move-next", [PipelineController::class, "moveToNext"])->middleware("role:admin,recruiter");
            Route::post("/{id}/reject", [PipelineController::class, "reject"])->middleware("role:admin,recruiter");
            Route::post("/{id}/hire", [PipelineController::class, "hire"])->middleware("role:admin,recruiter");
            Route::get("/job-role/{jobRoleId}/statistics", [PipelineController::class, "getStatistics"])->middleware("role:admin,recruiter,interviewer");
            Route::get("/job-role/{jobRoleId}/kanban", [PipelineController::class, "getKanbanBoard"])->middleware("role:admin,recruiter,interviewer");
        });

            Route::prefix("job-roles")->group(function () {
                Route::get("/{jobRoleId}/stages", [CustomStageController::class, "getStagesForJobRole"])->middleware("role:admin,recruiter,interviewer");
                Route::post("/{jobRoleId}/stages", [CustomStageController::class, "store"])->middleware("role:admin,recruiter");
                Route::post("/{jobRoleId}/stages/reorder", [CustomStageController::class, "reorder"])->middleware("role:admin,recruiter");
            });

            Route::prefix("stages/custom")->group(function () {
            Route::post("/{id}/update", [CustomStageController::class, "update"])->middleware("role:admin,recruiter");
            Route::post("/{id}/delete", [CustomStageController::class, "destroy"])->middleware("role:admin,recruiter");
    });

    // N8N
    Route::group(["prefix" => "n8n"] , function(){
        Route::get("/candidatesData" , [CandidateController::class , 'getCandidateData']);
        Route::post("/saveMetaData" , [CandidateController::class , "saveMetaData"]);
        Route::get("/createScreening/{candidate_id}" ,[InterviewController::class , 'createScreening']);
        // Route::get('/github/{username}' , [GithubController::class , 'analyze']);
    });
});
