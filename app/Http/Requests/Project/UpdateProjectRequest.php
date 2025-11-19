<?php

namespace App\Http\Requests\Project;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * Update Project Request
 * 
 * Handles validation for project updates
 */
class UpdateProjectRequest extends FormRequest
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
        $projectId = $this->route('project');

        $rules = [
            'project_type' => 'sometimes|required|in:0,1',
            'project_name' => 'sometimes|required|string|max:255',
            'project_in_state' => 'nullable|string',
            'start_date' => 'sometimes|required|date',
            'end_date' => 'sometimes|required|date|after_or_equal:start_date',
            'work_order_number' => [
                'sometimes',
                'required',
                'string',
                Rule::unique('projects', 'work_order_number')->ignore($projectId)
            ],
            'rate' => 'nullable|numeric|min:0',
            'project_capacity' => 'nullable|string',
            'total' => 'nullable|numeric|min:0',
            'description' => 'nullable|string|max:1000',
        ];

        // For streetlight projects, agreement fields are mandatory
        if ($this->input('project_type') == 1) {
            $rules['agreement_number'] = 'sometimes|required|string|max:255';
            $rules['agreement_date'] = 'sometimes|required|date|before_or_equal:start_date';
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
        ];
    }
}
