<?php

namespace App\Http\Requests;

use App\Models\Task;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class TaskRequest extends FormRequest
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
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $rules = [
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:1000'],
            'status' => ['sometimes', Rule::in([Task::STATUS_PENDING, Task::STATUS_COMPLETED])],
            'priority' => ['sometimes', Rule::in([Task::PRIORITY_LOW, Task::PRIORITY_MEDIUM, Task::PRIORITY_HIGH])],
            'order' => ['sometimes', 'integer', 'min:0'],
        ];

        // For update requests, make title optional
        if ($this->isMethod('PUT') || $this->isMethod('PATCH')) {
            $rules['title'] = ['sometimes', 'required', 'string', 'max:255'];
        }

        return $rules;
    }

    /**
     * Get custom messages for validator errors.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'title.required' => 'The task title is required.',
            'title.max' => 'The task title may not be greater than 255 characters.',
            'description.max' => 'The task description may not be greater than 1000 characters.',
            'status.in' => 'The status must be either pending or completed.',
            'priority.in' => 'The priority must be low, medium, or high.',
            'order.integer' => 'The order must be a valid integer.',
            'order.min' => 'The order must be at least 0.',
        ];
    }

    /**
     * Prepare the data for validation.
     */
    protected function prepareForValidation(): void
    {
        // Set default values if not provided
        if ($this->isMethod('POST')) {
            $this->merge([
                'status' => $this->status ?? Task::STATUS_PENDING,
                'priority' => $this->priority ?? Task::PRIORITY_MEDIUM,
            ]);
        }
    }

    /**
     * Get the validated data from the request with sanitization.
     *
     * @param array|null $key
     * @param mixed $default
     * @return array
     */
    public function validated($key = null, $default = null): array
    {
        $validated = parent::validated($key, $default);

        // Sanitize input data
        if (isset($validated['title'])) {
            $validated['title'] = strip_tags(trim($validated['title']));
        }

        if (isset($validated['description'])) {
            $validated['description'] = strip_tags(trim($validated['description']));
        }

        return $validated;
    }
}
