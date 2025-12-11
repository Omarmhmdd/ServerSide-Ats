<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StorePipelineRequest extends FormRequest
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
            'job_role_id' => 'required|exists:job_roles,id',
            'candidate_id' => 'required|exists:candidates,id',
            'stage_id' => 'required|exists:stages,id',
            'intreview_id' => 'nullable|exists:intreviews,id',
        ];
    }
}
