<?php

namespace App\Modules\Report\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ExportReportRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'type' => ['required', 'in:pdf,excel'],
            'report_name' => ['required', 'in:sales,purchases,profit,stock'],
            'start_date' => ['nullable', 'date', 'before_or_equal:end_date'],
            'end_date' => ['nullable', 'date', 'after_or_equal:start_date'],
            'customer' => ['nullable', 'string', 'min:2'],
            'supplier' => ['nullable', 'string', 'min:2'],
            'product_id' => ['nullable', 'integer', 'exists:products,id'],
        ];
    }

    public function messages(): array
    {
        return [
            'type.required' => 'O tipo de exportação é obrigatório.',
            'type.in' => 'O tipo deve ser pdf ou excel.',
            'report_name.required' => 'O nome do relatório é obrigatório.',
            'report_name.in' => 'Relatório inválido.',
            'start_date.date' => 'A data inicial deve ser uma data válida.',
            'start_date.before_or_equal' => 'A data inicial deve ser anterior ou igual à data final.',
            'end_date.date' => 'A data final deve ser uma data válida.',
            'end_date.after_or_equal' => 'A data final deve ser posterior ou igual à data inicial.',
            'customer.min' => 'O nome do cliente deve ter no mínimo 2 caracteres.',
            'supplier.min' => 'O nome do fornecedor deve ter no mínimo 2 caracteres.',
            'product_id.integer' => 'O ID do produto deve ser um número inteiro.',
            'product_id.exists' => 'O produto selecionado não existe.',
        ];
    }
}
