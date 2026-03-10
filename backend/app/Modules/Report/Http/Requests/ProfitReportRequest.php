<?php

namespace App\Modules\Report\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ProfitReportRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'start_date' => ['nullable', 'date', 'before_or_equal:end_date'],
            'end_date' => ['nullable', 'date', 'after_or_equal:start_date'],
            'product_id' => ['nullable', 'integer', 'exists:products,id'],
        ];
    }

    public function messages(): array
    {
        return [
            'start_date.date' => 'A data inicial deve ser uma data válida.',
            'start_date.before_or_equal' => 'A data inicial deve ser anterior ou igual à data final.',
            'end_date.date' => 'A data final deve ser uma data válida.',
            'end_date.after_or_equal' => 'A data final deve ser posterior ou igual à data inicial.',
            'product_id.integer' => 'O ID do produto deve ser um número inteiro.',
            'product_id.exists' => 'O produto selecionado não existe.',
        ];
    }
}
