<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreInterviewRequest extends FormRequest
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
            'interviewer_id' => 'required|exists:users,id',
            'job_role_id' => 'required|exists:job_roles,id',
            'candidate_id' => 'required|exists:candidates,id',
            'type' => 'required|string|max:255',
            'schedule' => 'required|date',
            'duration' => 'required|integer|min:15|max:480', // 15 minutes to 8 hours
            'meeting_link' => 'nullable|string|max:500',
            'rubric' => 'nullable|string',
            'notes' => 'nullable|string',
            'status' => 'nullable|in:no show,completed,canceled,posptponed,pending',
        ];
    }
}
