<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CreateFolderRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'parent_id' => ['nullable', 'integer'],
            'name' => ['required', 'string', 'max:255', 'regex:/^[^\\\\\\/:\*\?"<>\|]+$/'],
        ];
    }

    public function messages(): array
    {
        return [
            'name.regex' => '資料夾名稱包含不合法字元。',
        ];
    }
}
