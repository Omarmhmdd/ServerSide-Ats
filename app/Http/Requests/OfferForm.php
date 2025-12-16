<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class OfferForm extends FormRequest
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
            'candidate_id' => 'required|integer|exists:candidates,id',
            'role_id' => 'required|integer|exists:job_roles,id',
            'base_salary' => 'required|integer',
            'equity' => 'required|integer',
            'bonus' => 'required|integer',
            'benifits' => 'required|string',
            'start_date' => 'required|string',
            'contract_type' => 'required|string',
            'status' => 'required|string',
            'expiry_date' => 'required|string',
        ];
    }
}
