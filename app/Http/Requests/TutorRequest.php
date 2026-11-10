<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class TutorRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    public function rules(): array
    {
        return [
            'action' => ['required', 'string', 'in:explain,hint,similar,chat'],
            'question_code' => ['nullable', 'string', 'max:60', 'exists:questions,code'],
            'user_answer' => ['nullable', 'string', 'max:300'],
            'message' => ['nullable', 'string', 'max:1000', 'required_if:action,chat'],
            'history' => ['nullable', 'array', 'max:6'],
            'history.*.role' => ['required_with:history', 'string', 'in:user,model'],
            'history.*.text' => ['required_with:history', 'string', 'max:2000'],
        ];
    }
}
