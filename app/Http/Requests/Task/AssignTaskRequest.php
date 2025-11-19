<?php

namespace App\Http\Requests\Task;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Assign Task Request
 * 
 * Validates data for assigning engineer/vendor to task
 */
class AssignTaskRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        // Can be customized with policy check
        return auth()->check();
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array|string>
     */
    public function rules(): array
    {
        return [
            'engineer_id' => ['nullable', 'exists:users,id'],
            'vendor_id' => ['nullable', 'exists:users,id'],
            'reason' => ['nullable', 'string', 'max:500'],
        ];
    }

    /**
     * Get custom messages for validator errors.
     *
     * @return array
     */
    public function messages(): array
    {
        return [
            'engineer_id.exists' => 'Selected engineer does not exist',
            'vendor_id.exists' => 'Selected vendor does not exist',
            'reason.max' => 'Reason cannot exceed 500 characters',
        ];
    }

    /**
     * Configure the validator instance.
     *
     * @param  \Illuminate\Validation\Validator  $validator
     * @return void
     */
    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            // At least one assignment must be provided
            if (empty($this->engineer_id) && empty($this->vendor_id)) {
                $validator->errors()->add(
                    'engineer_id',
                    'Either engineer or vendor must be assigned'
                );
            }
        });
    }
}
