<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class metaDataRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

public function rules(): array {
    return [

        // TOP LEVEL
        'meta_data' => 'required|array',
        'meta_data.*.json' => 'required|array',

        // CANDIDATE ID
        'meta_data.*.json.candidate_id' => 'required|integer',

        // REPOSITORIES
        'meta_data.*.json.repositories' => 'nullable|array',
        'meta_data.*.json.repositories.repositories' => 'nullable|array',
        'meta_data.*.json.repositories.repositories.*.name' => 'nullable|string',
        'meta_data.*.json.repositories.repositories.*.title' => 'nullable|string',
        'meta_data.*.json.repositories.repositories.*.description' => 'nullable|string',
        'meta_data.*.json.repositories.repositories.*.purpose' => 'nullable|string',
        'meta_data.*.json.repositories.repositories.*.technologies_used' => 'nullable|array',
        'meta_data.*.json.repositories.repositories.*.technologies_used.*' => 'string',
        'meta_data.*.json.repositories.repositories.*.last_updated' => 'nullable|string',
        'meta_data.*.json.repositories.repositories.*.stars' => 'nullable|integer',
        'meta_data.*.json.repositories.repositories.*.forks' => 'nullable|integer',

        // PERSONAL INFO
        'meta_data.*.json.personal_info' => 'nullable|array',
        'meta_data.*.json.personal_info.personal_info.full_name' => 'nullable|string',
        'meta_data.*.json.personal_info.personal_info.email' => 'nullable|string',
        'meta_data.*.json.personal_info.personal_info.phone' => 'nullable|string',
        'meta_data.*.json.personal_info.personal_info.address' => 'nullable|string',
        'meta_data.*.json.personal_info.personal_info.linkedin_url' => 'nullable|string',
        'meta_data.*.json.personal_info.personal_info.github_url' => 'nullable|string',
        'meta_data.*.json.personal_info.personal_info.portfolio_url' => 'nullable|string',

        'meta_data.*.json.personal_info.summary' => 'nullable|string',

        // WORK EXPERIENCE
        'meta_data.*.json.personal_info.work_experience' => 'nullable|array',
        'meta_data.*.json.personal_info.work_experience.*.title' => 'nullable|string',
        'meta_data.*.json.personal_info.work_experience.*.company' => 'nullable|string',
        'meta_data.*.json.personal_info.work_experience.*.location' => 'nullable|string',
        'meta_data.*.json.personal_info.work_experience.*.start_date' => 'nullable|string',
        'meta_data.*.json.personal_info.work_experience.*.end_date' => 'nullable|string',
        'meta_data.*.json.personal_info.work_experience.*.duration' => 'nullable|string',
        'meta_data.*.json.personal_info.work_experience.*.responsibilities' => 'nullable|string',
        'meta_data.*.json.personal_info.work_experience.*.achievements' => 'nullable|string',
        'meta_data.*.json.personal_info.work_experience.*.skills_used' => 'nullable|array',
        'meta_data.*.json.personal_info.work_experience.*.skills_used.*' => 'string',

        // EDUCATION
        'meta_data.*.json.personal_info.education' => 'nullable|array',
        'meta_data.*.json.personal_info.education.*.school' => 'nullable|string',
        'meta_data.*.json.personal_info.education.*.degree' => 'nullable|string',
        'meta_data.*.json.personal_info.education.*.field_of_study' => 'nullable|string',
        'meta_data.*.json.personal_info.education.*.start_date' => 'nullable|string',
        'meta_data.*.json.personal_info.education.*.end_date' => 'nullable|string',
        'meta_data.*.json.personal_info.education.*.grade' => 'nullable|string',
        'meta_data.*.json.personal_info.education.*.activities' => 'nullable|string',

        // SKILLS
        'meta_data.*.json.personal_info.skills' => 'nullable|array',
        'meta_data.*.json.personal_info.skills.*' => 'string',

        // CERTIFICATIONS
        'meta_data.*.json.personal_info.certifications' => 'nullable|array',
        'meta_data.*.json.personal_info.certifications.*.name' => 'nullable|string',
        'meta_data.*.json.personal_info.certifications.*.issuing_organization' => 'nullable|string',
        'meta_data.*.json.personal_info.certifications.*.issue_date' => 'nullable|string',

        // PROJECTS
        'meta_data.*.json.personal_info.projects' => 'nullable|array',
        'meta_data.*.json.personal_info.projects.*.title' => 'nullable|string',
        'meta_data.*.json.personal_info.projects.*.description' => 'nullable|string',
        'meta_data.*.json.personal_info.projects.*.start_date' => 'nullable|string',
        'meta_data.*.json.personal_info.projects.*.end_date' => 'nullable|string',
        'meta_data.*.json.personal_info.projects.*.skills_used' => 'nullable|array',
        'meta_data.*.json.personal_info.projects.*.skills_used.*' => 'string',

        // LANGUAGES
        'meta_data.*.json.personal_info.languages' => 'nullable|array',
        'meta_data.*.json.personal_info.languages.*.language' => 'nullable|string',
        'meta_data.*.json.personal_info.languages.*.proficiency' => 'nullable|string',

        // ACHIEVEMENTS
        'meta_data.*.json.personal_info.achievements' => 'nullable|array',
        'meta_data.*.json.personal_info.achievements.honors_awards' => 'nullable|array',
        'meta_data.*.json.personal_info.achievements.honors_awards.*' => 'string',
    ];
}



}
