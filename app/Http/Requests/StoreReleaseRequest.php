<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreReleaseRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('process', $this->route('documentRequest'));
    }

    public function rules(): array
    {
        return [
            'document_ids' => ['required', 'array', 'min:1', 'max:30'], 'document_ids.*' => ['required', 'integer', 'distinct'],
            'claimant_name' => ['required', 'string', 'max:255'], 'claimant_identifier' => ['nullable', 'string', 'max:255'],
            'is_representative' => ['required', 'boolean'], 'authorization_reference' => ['required_if:is_representative,true', 'nullable', 'string', 'max:255'],
            'acknowledged' => ['required', 'accepted'],
        ];
    }
}
