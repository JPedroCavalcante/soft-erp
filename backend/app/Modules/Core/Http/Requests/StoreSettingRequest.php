<?php

namespace App\Modules\Core\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * Store Setting Request
 *
 * Validation rules for creating a new setting.
 */
class StoreSettingRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        // TODO: Add authorization logic (e.g., check if user has 'manage-settings' permission)
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
            'key' => [
                'required',
                'string',
                'max:255',
                'unique:settings,key',
                'regex:/^[a-z0-9._-]+$/',
            ],
            'value' => [
                'required',
            ],
            'type' => [
                'nullable',
                'string',
                Rule::in(['string', 'integer', 'boolean', 'json', 'array']),
            ],
            'description' => [
                'nullable',
                'string',
                'max:1000',
            ],
        ];
    }

    /**
     * Get custom messages for validator errors.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'key.required' => 'The setting key is required.',
            'key.unique' => 'This setting key already exists.',
            'key.regex' => 'The setting key must contain only lowercase letters, numbers, dots, underscores, and hyphens.',
            'value.required' => 'The setting value is required.',
        ];
    }
}
