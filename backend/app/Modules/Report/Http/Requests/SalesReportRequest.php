<?php

namespace App\Modules\Report\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class SalesReportRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'start_date' => ['nullable', 'date', 'before_or_equal:end_date'],
            'end_date' => ['nullable', 'date', 'after_or_equal:start_date'],
            'customer' => ['nullable', 'string', 'min:2'],
        ];
    }

    public function messages(): array
    {
        return [
            'start_date.date' => 'A data inicial deve ser uma data válida.',
            'start_date.before_or_equal' => 'A data inicial deve ser anterior ou igual à data final.',
            'end_date.date' => 'A data final deve ser uma data válida.',
            'end_date.after_or_equal' => 'A data final deve ser posterior ou igual à data inicial.',
            'customer.min' => 'O nome do cliente deve ter no mínimo 2 caracteres.',
        ];
    }
}
