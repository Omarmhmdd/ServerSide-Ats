<?php

namespace App\Candidate\Services;

use App\Models\Candidate;
use DB;

class BuildCandidateMetaDataService{
    public static function buildInsertStrings(array $allMetaData){
        $candidateRepositories        = [];
        $candidateRepoTechnologies    = [];
        $candidateSkills              = [];
        $candidateLanguages           = [];
        $candidateEducation           = [];
        $candidateCertifications      = [];
        $candidateProjects            = [];
        $candidateProjectSkills       = [];

        $items = $allMetaData["meta_data"];
        for($i = 0 ; $i < count($items); $i++){
            $data = $items[$i]["json"];
            $candidateId = $data["candidate_id"];
            $repos       = $data["repositories"]["repositories"] ?? [];
            $personal    = $data["personal_info"]["personal_info"] ?? [];
            $skills      = $data["personal_info"]["skills"] ?? [];
            $education   = $data["personal_info"]["education"] ?? [];
            $certs       = $data["personal_info"]["certifications"] ?? [];
            $projects    = $data["personal_info"]["projects"] ?? [];

            // Build inserts
            self::buildRepositories($candidateRepositories, $candidateRepoTechnologies, $candidateId, $repos);
            self::buildSkills($candidateSkills, $candidateId, $skills);
            self::buildLanguages($candidateLanguages, $candidateId, $personal);
            self::buildEducation($candidateEducation, $candidateId, $education);
            self::buildCertifications($candidateCertifications, $candidateId, $certs);
            self::buildProjects($candidateProjects, $candidateProjectSkills, $candidateId, $projects);

            // set candidate as processed
            $candidate = Candidate::where('id' , $candidateId)->first();
            if($candidate){
                $candidate->processed = 1;
                $candidate->save();
            }
        }

        return [
            "repositories"             => $candidateRepositories,
            "repository_technologies"  => $candidateRepoTechnologies,
            "detected_skills"          => $candidateSkills,
            "detected_languages"       => $candidateLanguages,
            "education"                => $candidateEducation,
            "certifications"           => $candidateCertifications,
            "projects"                 => $candidateProjects,
            "project_skills"           => $candidateProjectSkills,
        ];
    }

    public static function bulkInsert($table, $data)
    {
        if (!empty($data)) {
            DB::table($table)->insert($data);
        }
    }

    public static function buildRepositories(
        array &$reposArray,
        array &$techArray,
        $candidateId,
        array $repos
    ) {
        foreach ($repos as $repo) {
            $repoId = uniqid(); // temp id for mapping tech rows before DB insert

            $reposArray[] = [
                "temp_id" => $repoId,
                "candidate_id"   => $candidateId,
                "name"           => $repo["name"] ?? null,
                "title"          => $repo["title"] ?? null,
                "description"    => $repo["description"] ?? null,
                "purpose"        => $repo["purpose"] ?? null,
                "last_updated"   => date('Y-m-d H:i:s', strtotime($repo["last_updated"])) ?? null,
                "stars"          => $repo["stars"] ?? null,
                "forks"          => $repo["forks"] ?? null,
                "created_at"     => now(),
                "updated_at"     => now(),
            ];

            foreach ($repo["technologies_used"] ?? [] as $tech) {
                $techArray[] = [
                    "repository_id" => null,// unavailable yet depend on temp
                    "repo_temp_id"  => $repoId,
                    "technology"    => $tech,
                    "created_at"    => now(),
                    "updated_at"    => now(),
                ];
            }
        }
    }

    public static function buildSkills(array &$insertArray, $candidateId, array $skills)
    {
        foreach ($skills as $skill) {
            $insertArray[] = [
                "candidate_id" => $candidateId,
                "skill"        => $skill,
                "created_at"   => now(),
                "updated_at"   => now(),
            ];
        }
    }

    public static function buildLanguages(array &$insertArray, $candidateId, array $personal)
    {
        foreach ($personal["languages"] ?? [] as $lang) {
            $insertArray[] = [
                "candidate_id" => $candidateId,
                "language"     => $lang,
                "created_at"   => now(),
                "updated_at"   => now(),
            ];
        }
    }

    public static function buildEducation(array &$insertArray, $candidateId, array $education)
    {
        foreach ($education as $edu) {
            $insertArray[] = [
                "candidate_id"  => $candidateId,
                "school"        => $edu["school"] ?? null,
                "degree"        => $edu["degree"] ?? null,
                "field_of_study"=> $edu["field_of_study"] ?? null,
                "start_date"    => $edu["start_date"] ?? null,
                "end_date"      => $edu["end_date"] ?? null,
                "grade"         => $edu["grade"] ?? null,
                "created_at"    => now(),
                "updated_at"    => now(),
            ];
        }
    }

    public static function buildCertifications(array &$insertArray, $candidateId, array $certs)
    {
        foreach ($certs as $cert) {
            $insertArray[] = [
                "candidate_id"       => $candidateId,
                "name"               => $cert["name"] ?? null,
                "issue_date"         => $cert["issue_date"] ?? null,
                "expiration_date"    => $cert["expiration_date"] ?? null,
                "credential_id"      => $cert["credential_id"] ?? null,
                "credential_url"     => $cert["credential_url"] ?? null,
                "created_at"         => now(),
                "updated_at"         => now(),
            ];
        }
    }

    public static function buildProjects(
        array &$projectArray,
        array &$skillArray,
        $candidateId,
        array $projects
    ) {
        foreach ($projects as $proj) {
            $projId = uniqid();

            $projectArray[] = [
                "temp_id"      => $projId,
                "candidate_id" => $candidateId,
                "title"        => $proj["title"] ?? null,
                "description"  => $proj["description"] ?? null,
                "start_date"   => $proj["start_date"] ?? null,
                "end_date"     => $proj["end_date"] ?? null,
                "project_url"  => $proj["project_url"] ?? null,
                "created_at"   => now(),
                "updated_at"   => now(),
            ];

            foreach ($proj["skills_used"] ?? [] as $skill) {
                $skillArray[] = [
                    "project_id" => null,// temporarly
                    "project_temp_id" => $projId,
                    "skill"           => $skill,
                    "created_at"      => now(),
                    "updated_at"      => now(),
                ];
            }
        }
    }
}
