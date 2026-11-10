<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class SyncAttemptsRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    public function rules(): array
    {
        return [
            'attempts' => ['required', 'array', 'min:1', 'max:500'],
            'attempts.*.code' => ['required', 'string', 'max:60', 'exists:questions,code'],
            'attempts.*.answer' => ['required', 'array'],
            'attempts.*.answer.option' => ['sometimes', 'string', 'in:A,B,C,D'],
            'attempts.*.answer.kind' => ['sometimes', 'string', 'in:segment,missing,correct'],
            'attempts.*.answer.index' => ['sometimes', 'nullable', 'integer', 'min:0', 'max:20'],
            'attempts.*.answer.what' => ['sometimes', 'nullable', 'string', 'in:subject,verb'],
            'attempts.*.hints_used' => ['required', 'integer', 'min:0', 'max:3'],
            'attempts.*.time_ms' => ['nullable', 'integer', 'min:0', 'max:3600000'],
            'attempts.*.at' => ['nullable', 'date'],
        ];
    }
}
