<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class RenameFolderRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255', 'regex:/^[^\\\\\\/:\*\?"<>\|]+$/'],
        ];
    }

    public function messages(): array
    {
        return [
            'name.regex' => __('ui.messages.invalid_folder_name'),
        ];
    }
}
