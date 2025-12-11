<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class JobRoleRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return false;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'recruiter_id'     => 'required|int|',
            'level_id'     => 'required|int|',
            'hiring_manager_id'     => 'required|int|',
            'location'    => 'required|string|',
            'title' => 'required|string|',
            'description' => 'required|string|',
            'is_remote' => 'nullable|boolean|',
            'is_on_site' => 'nullable|boolean|',
        ];
    }
}
