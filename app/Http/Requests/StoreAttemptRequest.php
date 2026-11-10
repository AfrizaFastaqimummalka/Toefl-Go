<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreAttemptRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    public function rules(): array
    {
        return [
            'code' => ['required', 'string', 'max:60', 'exists:questions,code'],
            'answer' => ['required', 'array'],
            'answer.option' => ['sometimes', 'string', 'in:A,B,C,D'],
            'answer.kind' => ['sometimes', 'string', 'in:segment,missing,correct'],
            'answer.index' => ['sometimes', 'nullable', 'integer', 'min:0', 'max:20'],
            'answer.what' => ['sometimes', 'nullable', 'string', 'in:subject,verb'],
            'hints_used' => ['required', 'integer', 'min:0', 'max:3'],
            'time_ms' => ['nullable', 'integer', 'min:0', 'max:3600000'],
            'mode' => ['sometimes', 'string', 'in:practice,review,test'],
        ];
    }
}
