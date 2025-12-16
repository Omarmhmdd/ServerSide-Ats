<?php

namespace App\Services\Candidate;

use App\Models\Candidate;
use DB;

class BuildCandidateMetaDataService{

    public static function buildInsertStrings(array $allMetaData){
        $containers = self::initializeInsertContainers();

        foreach ($allMetaData["meta_data"] as  $item) {
            self::processCandidateMetaData($item, $containers);
        }

        return self::formatInsertResult($containers);
    }

    public static function bulkInsert($table, $data)
    {
        if (!empty($data)) {
            DB::table($table)->insert($data);
        }
    }

    public static function buildRepositories(array &$reposArray,array &$techArray,$candidateId,array $repos) {
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

    public static function buildProjects(array &$projectArray,array &$skillArray,$candidateId,array $projects) {
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

    private static function initializeInsertContainers(){
        return [
            'repositories'            => [],
            'repository_technologies' => [],
            'detected_skills'         => [],
            'detected_languages'      => [],
            'education'               => [],
            'certifications'          => [],
            'projects'                => [],
            'project_skills'          => [],
        ];
    }

    private static function processCandidateMetaData(array $item, array &$containers): void{
        $data        = $item['json'] ?? [];
        $candidateId = $data['candidate_id'] ?? null;

        if (!$candidateId) {
            return;
        }

        self::buildCandidateInserts($candidateId, $data, $containers);
        self::markCandidateAsProcessed($candidateId);
    }

    private static function buildCandidateInserts(int $candidateId,array $data,array &$containers): void {
        self::buildRepositories(
            $containers['repositories'],
            $containers['repository_technologies'],
            $candidateId,
            $data['repositories']['repositories'] ?? []
        );

        self::buildSkills(
            $containers['detected_skills'],
            $candidateId,
            $data['personal_info']['skills'] ?? []
        );

        self::buildLanguages(
            $containers['detected_languages'],
            $candidateId,
            $data['personal_info']['personal_info'] ?? []
        );

        self::buildEducation(
            $containers['education'],
            $candidateId,
            $data['personal_info']['education'] ?? []
        );

        self::buildCertifications(
            $containers['certifications'],
            $candidateId,
            $data['personal_info']['certifications'] ?? []
        );

        self::buildProjects(
            $containers['projects'],
            $containers['project_skills'],
            $candidateId,
            $data['personal_info']['projects'] ?? []
        );
    }

    private static function markCandidateAsProcessed(int $candidateId): void{
        Candidate::where('id', $candidateId)
            ->update(['processed' => 1]);
    }

    private static function formatInsertResult(array $containers){
        return $containers;
    }
}
