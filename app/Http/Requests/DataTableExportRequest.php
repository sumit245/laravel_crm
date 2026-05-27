<?php

namespace App\Http\Requests;

use App\Enums\DataTableExportScope;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class DataTableExportRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'export_scope' => ['nullable', 'string', Rule::enum(DataTableExportScope::class)],
            'page_from' => ['nullable', 'integer', 'min:1'],
            'page_to' => ['nullable', 'integer', 'min:1'],
            'date_from' => ['nullable', 'date'],
            'date_to' => ['nullable', 'date', 'after_or_equal:date_from'],
            'date_column' => ['nullable', 'string', 'max:64'],
            'max_rows' => ['nullable', 'integer', 'min:1', 'max:100000'],
            'format' => ['nullable', 'string', Rule::in(['xlsx'])],
        ];
    }

    public function exportScope(): DataTableExportScope
    {
        $raw = $this->input('export_scope', DataTableExportScope::FilteredAll->value);

        return DataTableExportScope::tryFrom((string) $raw) ?? DataTableExportScope::FilteredAll;
    }
}
