<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class TransitionDocumentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('process', $this->route('documentRequest'));
    }

    public function rules(): array
    {
        return [
            'action' => ['required', Rule::in(['receive', 'route', 'return', 'ready', 'move'])],
            'faculty_id' => ['nullable', 'integer'],
            'drawer_id' => ['nullable', 'integer', Rule::exists('drawers', 'id')->where('is_active', true)],
            'notes' => ['nullable', 'string', 'max:2000'],
        ];
    }
}
