<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreFeedbackRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('feedback', $this->route('documentRequest'));
    }

    public function rules(): array
    {
        return [
            'service_rating' => ['required', 'integer', 'between:1,5'], 'processing_rating' => ['required', 'integer', 'between:1,5'], 'comments' => ['nullable', 'string', 'max:300'],
        ];
    }
}
