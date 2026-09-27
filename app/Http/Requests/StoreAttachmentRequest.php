<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreAttachmentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('upload', $this->route('documentRequest'));
    }

    public function rules(): array
    {
        return ['file' => ['required', 'file', 'mimes:pdf,png,jpg,jpeg,doc,docx', 'extensions:pdf,png,jpg,jpeg,doc,docx', 'max:10240']];
    }
}
