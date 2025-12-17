<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class AIRequest extends FormRequest
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
            'interview_id' => 'required|integer',
            'candidate_id' => 'required|integer',
            'summary' => 'required|string',
            'scorecard' => 'required|',
            'overall_recommendation' => 'required|string',
        ];
    }
}
