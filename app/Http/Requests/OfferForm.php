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
            'form.base_salary' => ['required', 'numeric', 'min:0'],
            'form.equity' => ['required', 'numeric', 'min:0'],
            'form.bonus' => ['required', 'numeric', 'min:0'],
            'form.benefits' => ['required', 'string', 'max:5000'],
            'form.start_date' => ['required', 'date', 'after_or_equal:today'],
            'form.contract_type' => ['required', 'string', 'max:255'],
            'form.expiry_date' => ['required', 'date', 'after:form.start_date'],
            
            'candidate_id' => ['required', 'integer', 'exists:candidates,id'],
            'role_id' => ['required', 'integer', 'exists:job_roles,id'],
        ];
    }
}
