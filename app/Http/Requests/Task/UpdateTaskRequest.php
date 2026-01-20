<?php

namespace App\Http\Requests\Task;

use App\Enums\TaskStatus;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * Update Task Request
 * 
 * Validates data for updating an existing task
 */
class UpdateTaskRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
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
            'project_id' => ['sometimes', 'exists:projects,id'],
            'site_id' => ['sometimes', 'nullable', 'integer'],
            'engineer_id' => ['sometimes', 'nullable', 'exists:users,id'],
            'vendor_id' => ['sometimes', 'nullable', 'exists:users,id'],
            'manager_id' => ['sometimes', 'nullable', 'exists:users,id'],
            'task_name' => ['sometimes', 'nullable', 'string', 'max:255'],
            'activity' => ['sometimes', 'nullable', 'string', 'max:255'],
            'description' => ['sometimes', 'nullable', 'string'],
            'status' => ['sometimes', Rule::in(array_map(fn($case) => $case->value, TaskStatus::cases()))],
            'start_date' => ['sometimes', 'nullable', 'date'],
            'end_date' => ['sometimes', 'nullable', 'date'],
            'image' => ['sometimes', 'nullable', 'string'],
            'materials_consumed' => ['sometimes', 'nullable', 'string'],
            'approved_by' => ['sometimes', 'nullable', 'exists:users,id'],
            'progress_notes' => ['sometimes', 'nullable', 'string'],
            'blocker_description' => ['sometimes', 'nullable', 'string'],
            'extension_reason' => ['sometimes', 'nullable', 'string', 'max:1000'],
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
            'project_id.exists' => 'Selected project does not exist',
            'engineer_id.exists' => 'Selected engineer does not exist',
            'vendor_id.exists' => 'Selected vendor does not exist',
            'approved_by.exists' => 'Selected approver does not exist',
            'end_date.after_or_equal' => 'End date must be equal to or after start date',
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
            // If end_date is being updated, ensure it's after start_date
            if ($this->has('end_date') && $this->has('start_date')) {
                if ($this->end_date && $this->start_date) {
                    if (strtotime($this->end_date) < strtotime($this->start_date)) {
                        $validator->errors()->add(
                            'end_date',
                            'End date must be equal to or after start date'
                        );
                    }
                }
            }

            // If status is being changed to COMPLETED, require progress notes
            if ($this->has('status') && $this->status === TaskStatus::COMPLETED->value) {
                if (empty($this->progress_notes) && empty($this->description)) {
                    $validator->errors()->add(
                        'progress_notes',
                        'Progress notes are required when completing a task'
                    );
                }
            }

            // If status is being changed to BLOCKED, require blocker description
            if ($this->has('status') && $this->status === TaskStatus::BLOCKED->value) {
                if (empty($this->blocker_description) && empty($this->description)) {
                    $validator->errors()->add(
                        'blocker_description',
                        'Blocker description is required when marking task as blocked'
                    );
                }
            }
        });
    }
}
