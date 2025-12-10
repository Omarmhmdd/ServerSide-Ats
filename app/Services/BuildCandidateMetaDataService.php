<?php

namespace App\Services;

class BuildCandidateMetaDataService
{
    public static function buildInsertStrings($allMetaData){
        $candidateRepositories = [];
        $candidateSkills = [];
        $candidateLanguages = [];
        $candidateWork = [];
        $candidateEducation = [];
        $candidateCertifications = [];
        $candidateProjects = [];
        $candidateVolunteering = [];
        $candidateAchievements = [];

        foreach ($allMetaData as $candidateId => $metaData) {
            $githubData = $metaData[0] ?? [];
            $linkedInData = $metaData[1] ?? [];
            $cvData = $metaData[2] ?? [];

            // Build insert arrays
            self::buildRepositories($candidateRepositories, $candidateId, $githubData);
            self::buildSkills($candidateSkills, $candidateId, $githubData);
            self::buildLanguages($candidateLanguages, $candidateId, $githubData);

            self::buildWork($candidateWork, $candidateId, $linkedInData, $cvData);
            self::buildEducation($candidateEducation, $candidateId, $linkedInData, $cvData);
            self::buildCertifications($candidateCertifications, $candidateId, $linkedInData, $cvData);
            self::buildProjects($candidateProjects, $candidateId, $linkedInData, $cvData);

            self::buildVolunteering($candidateVolunteering, $candidateId, $linkedInData);
            self::buildAchievements($candidateAchievements, $candidateId, $linkedInData, $cvData);
        }

        return [
            "candidate_repositories" => $candidateRepositories,
            "candidate_skills" => $candidateSkills,
            "candidate_languages" => $candidateLanguages,
            "candidate_work_experience" => $candidateWork,
            "candidate_education" => $candidateEducation,
            "candidate_certifications" => $candidateCertifications,
            "candidate_projects" => $candidateProjects,
            "candidate_volunteering" => $candidateVolunteering,
            "candidate_achievements" => $candidateAchievements
        ];
    }

    public static function bulkInsert($table, $data){
        if (!empty($data)) {
            \DB::table($table)->insert($data);
        }
    }

    // --- Build functions ---
    public static function buildRepositories(array &$insertArray, $candidateId, $githubData){
        foreach ($githubData['repositories'] ?? [] as $repo) {
            $insertArray[] = [
                'candidate_id' => $candidateId,
                'name' => $repo['name'] ?? null,
                'title' => $repo['title'] ?? null,
                'description' => $repo['description'] ?? null,
                'purpose' => $repo['purpose'] ?? null,
                'technologies_used' => isset($repo['technologies_used']) ? json_encode($repo['technologies_used']) : null,
                'last_updated' => $repo['last_updated'] ?? null,
                'stars' => $repo['stars'] ?? null,
                'forks' => $repo['forks'] ?? null,
                'created_at' => now(),
                'updated_at' => now()
            ];
        }
    }

    public static function buildSkills(array &$insertArray, $candidateId, $githubData)
    {
        foreach ($githubData['detected_skills'] ?? [] as $skill) {
            $insertArray[] = [
                'candidate_id' => $candidateId,
                'skill' => $skill,
                'created_at' => now(),
                'updated_at' => now()
            ];
        }
    }

    public static function buildLanguages(array &$insertArray, $candidateId, $githubData){
        foreach ($githubData['detected_languages'] ?? [] as $lang) {
            $insertArray[] = [
                'candidate_id' => $candidateId,
                'language' => $lang,
                'created_at' => now(),
                'updated_at' => now()
            ];
        }
    }

    public static function buildWork(array &$insertArray, $candidateId, $linkedInData, $cvData){
        foreach ($linkedInData['experience'] ?? $cvData['work_experience'] ?? [] as $work) {
            $insertArray[] = [
                'candidate_id' => $candidateId,
                'title' => $work['title'] ?? null,
                'company' => $work['company'] ?? null,
                'employment_type' => $work['employment_type'] ?? null,
                'location' => $work['location'] ?? null,
                'start_date' => $work['start_date'] ?? null,
                'end_date' => $work['end_date'] ?? null,
                'duration' => $work['duration'] ?? null,
                'description' => $work['description'] ?? null,
                'skills_used' => isset($work['skills_used']) ? json_encode($work['skills_used']) : null,
                'created_at' => now(),
                'updated_at' => now()
            ];
        }
    }

    public static function buildEducation(array &$insertArray, $candidateId, $linkedInData, $cvData){
        foreach ($linkedInData['education'] ?? $cvData['education'] ?? [] as $edu) {
            $insertArray[] = [
                'candidate_id' => $candidateId,
                'school' => $edu['school'] ?? null,
                'degree' => $edu['degree'] ?? null,
                'field_of_study' => $edu['field_of_study'] ?? null,
                'start_date' => $edu['start_date'] ?? null,
                'end_date' => $edu['end_date'] ?? null,
                'grade' => $edu['grade'] ?? null,
                'activities' => $edu['activities'] ?? null,
                'created_at' => now(),
                'updated_at' => now()
            ];
        }
    }

    public static function buildCertifications(array &$insertArray, $candidateId, $linkedInData, $cvData){
        foreach (($linkedInData['certifications'] ?? []) + ($cvData['certifications'] ?? []) as $cert) {
            $insertArray[] = [
                'candidate_id' => $candidateId,
                'name' => $cert['name'] ?? null,
                'issuing_organization' => $cert['issuing_organization'] ?? null,
                'issue_date' => $cert['issue_date'] ?? null,
                'expiration_date' => $cert['expiration_date'] ?? null,
                'credential_id' => $cert['credential_id'] ?? null,
                'credential_url' => $cert['credential_url'] ?? null,
                'created_at' => now(),
                'updated_at' => now()
            ];
        }
    }

    public static function buildProjects(array &$insertArray, $candidateId, $linkedInData, $cvData){
        foreach (($linkedInData['projects'] ?? []) + ($cvData['projects'] ?? []) as $proj) {
            $insertArray[] = [
                'candidate_id' => $candidateId,
                'title' => $proj['title'] ?? null,
                'description' => $proj['description'] ?? null,
                'start_date' => $proj['start_date'] ?? null,
                'end_date' => $proj['end_date'] ?? null,
                'skills_used' => isset($proj['skills_used']) ? json_encode($proj['skills_used']) : null,
                'project_url' => $proj['project_url'] ?? null,
                'created_at' => now(),
                'updated_at' => now()
            ];
        }
    }

    public static function buildVolunteering(array &$insertArray, $candidateId, $linkedInData){
        foreach ($linkedInData['volunteering'] ?? [] as $vol) {
            $insertArray[] = [
                'candidate_id' => $candidateId,
                'role' => $vol['role'] ?? null,
                'organization' => $vol['organization'] ?? null,
                'start_date' => $vol['start_date'] ?? null,
                'end_date' => $vol['end_date'] ?? null,
                'cause' => $vol['cause'] ?? null,
                'description' => $vol['description'] ?? null,
                'created_at' => now(),
                'updated_at' => now()
            ];
        }
    }

    public static function buildAchievements(array &$insertArray, $candidateId, $linkedInData, $cvData){
        $achievements = $linkedInData['achievements'] ?? $cvData['achievements'] ?? [];
        $insertArray[] = [
            'candidate_id' => $candidateId,
            'honors_awards' => isset($achievements['honors_awards']) ? json_encode($achievements['honors_awards']) : null,
            'publications' => isset($achievements['publications']) ? json_encode($achievements['publications']) : null,
            'patents' => isset($achievements['patents']) ? json_encode($achievements['patents']) : null,
            'created_at' => now(),
            'updated_at' => now()
        ];
    }
}
