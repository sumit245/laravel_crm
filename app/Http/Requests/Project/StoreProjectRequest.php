<?php

namespace App\Http\Requests\Project;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Store Project Request
 * 
 * Handles validation for project creation
 */
class StoreProjectRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request
     */
    public function authorize(): bool
    {
        // Authorization is handled by policies
        return true;
    }

    /**
     * Get the validation rules that apply to the request
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $rules = [
            'project_type' => 'required|in:0,1',
            'project_name' => 'required|string|max:255',
            'project_in_state' => 'nullable|string',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
            'work_order_number' => 'required|string|unique:projects,work_order_number',
            'rate' => 'nullable|numeric|min:0',
            'project_capacity' => 'nullable|string',
            'total' => 'nullable|numeric|min:0',
            'description' => 'nullable|string|max:1000',
        ];

        // For streetlight projects, agreement fields are mandatory
        if ($this->input('project_type') == 1) {
            $rules['agreement_number'] = 'required|string|max:255';
            $rules['agreement_date'] = 'required|date|before_or_equal:start_date';
        }

        return $rules;
    }

    /**
     * Get custom error messages for validator errors
     */
    public function messages(): array
    {
        return [
            'project_type.required' => 'Please select a project type.',
            'project_type.in' => 'Invalid project type selected.',
            'project_name.required' => 'Project name is required.',
            'work_order_number.required' => 'Work order number is required.',
            'work_order_number.unique' => 'This work order number already exists.',
            'start_date.required' => 'Start date is required.',
            'end_date.required' => 'End date is required.',
            'end_date.after_or_equal' => 'End date must be after or equal to start date.',
            'agreement_number.required' => 'Agreement number is required for streetlight projects.',
            'agreement_date.required' => 'Agreement date is required for streetlight projects.',
            'agreement_date.before_or_equal' => 'Agreement date must be before or equal to start date.',
        ];
    }

    /**
     * Get custom attributes for validator errors
     */
    public function attributes(): array
    {
        return [
            'project_type' => 'project type',
            'project_name' => 'project name',
            'project_in_state' => 'state',
            'work_order_number' => 'work order number',
            'agreement_number' => 'agreement number',
            'agreement_date' => 'agreement date',
        ];
    }
}
