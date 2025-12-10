<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateInterviewRequest extends FormRequest
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
            'intreveiwer_id' => 'sometimes|exists:users,id',
            'job_role_id' => 'sometimes|exists:job_roles,id',
            'candidate_id' => 'sometimes|exists:candidates,id',
            'type' => 'sometimes|string|max:255',
            'schedule' => 'sometimes|date',
            'duration' => 'sometimes|integer|min:15|max:480',
            'meeting_link' => 'nullable|string|max:500',
            'rubric' => 'nullable|string',
            'notes' => 'nullable|string',
            'status' => 'sometimes|in:no show,completed,canceled,posptponed,pending',
        ];
    }
}
