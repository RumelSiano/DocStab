<?php

namespace App\Http\Requests;

use App\Models\DocumentRequest;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreDocumentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('create', DocumentRequest::class);
    }

    public function rules(): array
    {
        return [
            'requestor_id' => ['nullable', 'integer', Rule::exists('users', 'id')->where('role', 'requestor')->where('is_active', true)],
            'purpose' => ['nullable', 'string', 'max:2000'], 'notes' => ['nullable', 'string', 'max:4000'], 'needed_date' => ['nullable', 'date', 'after_or_equal:today'],
            'documents' => ['required', 'array', 'min:1', 'max:30'],
            'documents.*' => ['array:document_type_id,faculty_ids'],
            'documents.*.document_type_id' => ['required', 'integer', Rule::exists('document_types', 'id')->where('is_active', true)],
            'documents.*.faculty_ids' => ['present', 'array', 'max:20'],
            'documents.*.faculty_ids.*' => ['integer', Rule::exists('faculty', 'id')->where('is_active', true)],
        ];
    }
}
