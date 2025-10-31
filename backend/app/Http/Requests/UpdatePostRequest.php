<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdatePostRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'title' => 'sometimes|string|max:255',
            'content' => 'sometimes|string',
            'author' => 'sometimes|string|max:100',
            'status' => 'sometimes|in:draft,published',
        ];
    }

    public function messages(): array
    {
        return [
            'title.max' => 'タイトルは255文字以内で入力してください',
            'author.max' => '著者名は100文字以内で入力してください',
            'status.in' => 'ステータスはdraftまたはpublishedを指定してください',
        ];
    }
}