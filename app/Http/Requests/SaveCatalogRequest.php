<?php

namespace App\Http\Requests;

use App\Services\CatalogManager;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class SaveCatalogRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('manage-catalogs');
    }

    public function rules(): array
    {
        $table = CatalogManager::modelClass($this->route('resource'));
        $id = $this->route('id');
        $record = $id ? $table::query()->findOrFail($id) : null;
        $rules = ['is_active' => ['sometimes', 'boolean']];
        if ($this->route('resource') === 'drawers') {
            $rules += ['number' => ['required', 'integer', 'between:1,65535', Rule::unique('drawers')->ignore($record)], 'label' => ['required', 'string', 'max:255']];
        } else {
            $rules['name'] = ['required', 'string', 'max:255'];
            if ($this->route('resource') === 'document_types') {
                $rules['name'][] = Rule::unique('document_types')->ignore($record);
            }
            if ($this->route('resource') === 'faculty') {
                $rules['department'] = ['nullable', 'string', 'max:255'];
            }
        }

        return $rules;
    }
}
