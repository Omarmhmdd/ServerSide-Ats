<?php

namespace App\Services\Candidate;

use App\Models\CustomStage;
use App\Models\Interview;
use App\Models\JobRole;
use App\Models\Pipeline;
use App\Services\Candidate\BuildCandidateMetaDataService;
use App\Services\CV\CVExtractionService;
use App\Models\Candidate;
use App\Services\GitHubService;
use App\Services\InterviewService;
use App\Services\MetaDataService\CertificationMetaData;
use App\Services\MetaDataService\DetectedLanguagesMetaData;
use App\Services\MetaDataService\DetectedSkillsMetaData;
use App\Services\MetaDataService\EducationMetaData;
use App\Services\MetaDataService\MetaDataService;
use App\Services\MetaDataService\ProjectMetaData;
use App\Services\MetaDataService\RepositoryMetaData;
use DB;
use Exception;

class CandidateService{
    public static function saveMetaData(array $allMetaData){

        if(!$allMetaData || count($allMetaData) === 0){
            throw new Exception("Failed to extract user data, please make sure to enter valid urls");
        }

        $tablesInsertion = BuildCandidateMetaDataService::buildInsertStrings($allMetaData);
        foreach($tablesInsertion as $table => $data){
            BuildCandidateMetaDataService::bulkInsert($table , $data);
        }

        self::scheduleScreening($allMetaData);
    }

    public static function getMetaData(int $candidate_id){
        $candidate = Candidate::where('id' , $candidate_id)->first();
        if(!$candidate) throw new Exception("Candidate not found");

        $domains = [
            "repositories" => RepositoryMetaData::class,
            "projects" => ProjectMetaData::class,
            "education" => EducationMetaData::class,
            "detected_skills" => DetectedLanguagesMetaData::class,
            "detected_languages" => DetectedSkillsMetaData::class,
            "cerifications" => CertificationMetaData::class
        ];

        $meta_data = [];
        // add candidate
        $meta_data["candidate"] = $candidate;
        foreach($domains as $domain => $model_class){
            $meta_data_service = new MetaDataService(new $model_class);
            $meta_data[$domain] = $meta_data_service->get($candidate_id);
        }
     

        return $meta_data;
    }

    private static function scheduleScreening($allMetaData){
        $listOfEmails = [];
        $items = $allMetaData["meta_data"];
        for($i = 0; $i < count($items); $i++){
            $data = $items[$i]['json'];
            $candidate = Candidate::where('id', $data["candidate_id"])
                    ->select('email')
                    ->first();

            if ($candidate) {
                $listOfEmails[$data["candidate_id"]] = $candidate->email;
            }
        }

        InterviewService::scheduleInterviews($listOfEmails);
    }

    public static function getCandidateData(){
        $candidates = self::getUnprocessedCandidates();

        $githubService = new GitHubService();
        $cvService     = new CVExtractionService();

        $users = $candidates->map(function ($candidate) use ($githubService, $cvService) {
            return self::buildCandidatePayload(
                $candidate,
                $githubService,
                $cvService
            );
        })->values()->toArray();

        return ['users' => $users];
    }


    public static function getCandidateByRole(int $recruiter_id){
        // get all job roles of this recruiter
        $jobRoles = JobRole::where('recruiter_id' , $recruiter_id)->get();

        // get all candidate per role
        $candidatesByRole = [];
        foreach($jobRoles as $role){
            $candidatesByRole[$role->id] = self::getCandidateForRole($recruiter_id , $role);
        }

        return $candidatesByRole;
    }
    
    public static function getInterviews($candidate_id){
        $interviews = Interview::with('scoreCard')
                                ->where('candidate_id' , $candidate_id)
                                ->get();
        return $interviews;        
    }

    public static function getCandidateProgress(int $candidateId): array{
        $candidate = self::getCandidate($candidateId);

        $stages = self::getAllStagesForJobRole($candidate->job_role_id);

        $liveStage = self::getLiveStage(
            $candidateId,
            $candidate->job_role_id
        );

        $stages[] = $liveStage;

        return $stages;
    }

    private static function getCandidate(int $candidateId){
        return Candidate::findOrFail($candidateId);
    }

    private static function getAllStagesForJobRole(int $jobRoleId){
        $stages = [
            'applied',
            'screening',
        ];

        $customStages = CustomStage::where('job_role_id', $jobRoleId)
            ->pluck('name')
            ->toArray();

        return array_merge(
            $stages,
            $customStages,
            ['offer']
        );
    }

    private static function getLiveStage(int $candidateId,int $jobRoleId): array {
        $pipeline = Pipeline::where('candidate_id', $candidateId)
            ->where('job_role_id', $jobRoleId)
            ->with('customStage')
            ->first();

        if (!$pipeline) {
            return [
                'id'   => null,
                'name' => null,
            ];
        }

        // Custom stage takes priority unless Offer
        if ($pipeline->custom_stage_id && $pipeline->global_stages !== 'Offer') {
            return [
                'id'   => $pipeline->custom_stage_id,
                'name' => $pipeline->customStage->name,
            ];
        }

        return [
            'id'   => null,
            'name' => $pipeline->global_stages,
        ];
    }

    private static function getCandidateForRole($recruiter_id , $role){
        $candidates = Candidate::where('recruiter_id' , $recruiter_id)
                                ->where('job_role_id' , $role->id)
                                ->get();
        return [
            "role_name" => $role->title,
            "candidates" => $candidates
        ];
    }

    public static function cleanUtf8($str){
        return $str ? mb_convert_encoding($str, 'UTF-8', 'UTF-8') : null;
    }

    private static function getUnprocessedCandidates(){
        return Candidate::where('processed', 0)
            ->select([
                'id',
                'github_username',
                'attachments',
            ])
            ->get();
    }
    private static function buildCandidatePayload(Candidate $candidate,GitHubService $githubService,CVExtractionService $cvService) {
        return [
            'candidate_id' => $candidate->id,
            'github'       => self::getGithubData($candidate, $githubService),
            'cv_text'      => self::getCvText($candidate, $cvService),
        ];
    }

    private static function getGithubData(Candidate $candidate,GitHubService $githubService) {
        if (empty($candidate->github_username)) {
            return [
                'profile' => null,
                'repos'   => [],
            ];
        }

        $profile = $githubService->getUser($candidate->github_username);
        $repos   = $githubService->getRepos($candidate->github_username);

        return [
            'profile' => self::cleanArray($profile),
            'repos'   => self::cleanNestedArray($repos),
        ];
    }

    private static function getCvText(Candidate $candidate,CVExtractionService $cvService) {
        if (empty($candidate->attachments)) {
            return null;
        }

        return self::cleanUtf8(
            $cvService->extract($candidate->attachments)
        );
    }

    private static function cleanArray(?array $data): ?array{
        if (!$data) {
            return null;
        }

        return array_map(
            [self::class, 'cleanUtf8'],
            $data
        );
    }

    private static function cleanNestedArray(array $data): array{
        return array_map(function ($item) {
            return array_map(
                [self::class, 'cleanUtf8'],
                $item
            );
        }, $data);
    }

}
