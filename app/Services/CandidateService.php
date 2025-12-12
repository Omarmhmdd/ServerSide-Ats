<?php

namespace App\Services;

use App\Services\BuildCandidateMetaDataService;
use App\Models\Candidate;
use App\Services\CVExtractionService;
use Exception;
use Log;

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


    private static function cleanUtf8($str){
        return $str ? mb_convert_encoding($str, 'UTF-8', 'UTF-8') : null;
    }

    public static function getCandidateData(){
        $candidates = Candidate::where('processed', 0)
        ->select([
            'id',
            'github_username',
            'attachments'
        ])
        ->get();
        
        $github = new GitHubService();
        $cvExtractor = new CVExtractionService();

        $results = [];

        foreach ($candidates as $candidate) {

            $githubProfile = null;
            $githubRepos = [];

            if (!empty($candidate->github_username)) {
                $githubProfile = $github->getUser($candidate->github_username);
                $githubRepos   = $github->getRepos($candidate->github_username);
            }

            // cv extraction
            $cvText = null;
            if (!empty($candidate->attachments)) {
                $cvText = CandidateService::cleanUtf8($cvExtractor->extract($candidate->attachments));
            }

            // clean github info from UTF-8
            $githubProfile = array_map([CandidateService::class, 'cleanUtf8'], $githubProfile ?? []);
            $githubRepos = array_map(function($repo) {
                return array_map([CandidateService::class, 'cleanUtf8'], $repo);
            }, $githubRepos ?? []);


            $results[] = [
                "candidate_id" => $candidate->id,
                "github" => [
                    "profile" => $githubProfile,
                    "repos"   => $githubRepos
                ],
                "cv_text" => $cvText,
            ];
        }

        return [
            "users" => $results
        ];

    }


}
