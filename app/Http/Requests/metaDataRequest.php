<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class metaDataRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
         return [
            '*' => 'required|array', 

            // index 0
            '*.basics' => 'sometimes|array',
            '*.work' => 'sometimes|array',
            '*.education' => 'sometimes|array',
            '*.skills' => 'sometimes|array',
            '*.projects' => 'sometimes|array',
            '*.certifications' => 'sometimes|array',
            '*.languages' => 'sometimes|array',

            // index 1
            '*.candidate_profile' => 'sometimes|array',
            '*.skills_analysis' => 'sometimes|array',
            '*.experience_intelligence' => 'sometimes|array',
            '*.cultural_contextual_fit' => 'sometimes|array',
            '*.career_trajectory_insights' => 'sometimes|array',
            '*.competitive_advantages' => 'sometimes|array',

            // index 2
            '*.output' => 'sometimes|array'
        ];
    }
}
