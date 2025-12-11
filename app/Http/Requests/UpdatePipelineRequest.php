<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdatePipelineRequest extends FormRequest
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
            'job_role_id' => 'sometimes|exists:job_roles,id',
            'candidate_id' => 'sometimes|exists:candidates,id',
            'stage_id' => 'sometimes|exists:stages,id',
            'intreview_id' => 'nullable|exists:intreviews,id',
        ];
    }
}
