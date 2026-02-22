<?php

namespace App\Http\Requests\Task;

use App\Enums\TaskStatus;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * Validates input for creating a new task/target. Checks that selected sites exist, assigned
 * staff are valid, and date ranges are logical (end date >= start date). Prevents orphaned task
 * records.
 *
 * Data Flow:
 *   POST /tasks → StoreTaskRequest validates → Check relationships → Pass: controller
 *   creates task → Fail: redirect with errors
 *
 * @depends-on Task, StreetlightTask, User, Streetlight
 * @business-domain Field Operations
 * @package App\Http\Requests\Task
 */
class StoreTaskRequest extends FormRequest
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
            'project_id' => ['required', 'exists:projects,id'],
            'site_id' => ['nullable', 'integer'],
            'engineer_id' => ['nullable', 'exists:users,id'],
            'vendor_id' => ['nullable', 'exists:users,id'],
            'manager_id' => ['nullable', 'exists:users,id'],
            'task_name' => ['nullable', 'string', 'max:255'],
            'activity' => ['nullable', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'status' => ['nullable', Rule::in(array_map(fn($case) => $case->value, TaskStatus::cases()))],
            'start_date' => ['nullable', 'date'],
            'end_date' => ['nullable', 'date', 'after_or_equal:start_date'],
            'image' => ['nullable', 'string'],
            'materials_consumed' => ['nullable', 'string'],
            'selected_wards' => ['nullable', 'string'],
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
            'project_id.required' => 'Project is required',
            'project_id.exists' => 'Selected project does not exist',
            'engineer_id.exists' => 'Selected engineer does not exist',
            'vendor_id.exists' => 'Selected vendor does not exist',
            'end_date.after_or_equal' => 'End date must be equal to or after start date',
        ];
    }

    /**
     * Prepare the data for validation.
     */
    protected function prepareForValidation(): void
    {
        // Set default status if not provided
        if (!$this->has('status')) {
            $this->merge([
                'status' => TaskStatus::PENDING->value,
            ]);
        }

        // Set default start date if not provided
        if (!$this->has('start_date')) {
            $this->merge([
                'start_date' => now()->toDateString(),
            ]);
        }
    }
}
