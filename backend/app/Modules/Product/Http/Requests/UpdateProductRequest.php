<?php

namespace App\Modules\Product\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateProductRequest extends FormRequest
{

    public function rules(): array
    {
        return [
            'name' => [
                'sometimes',
                'required',
                'string',
                'min:3',
                'max:255',
            ],
            'sale_price' => [
                'sometimes',
                'required',
                'numeric',
                'min:0.01',
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'O nome do produto é obrigatório quando fornecido.',
            'name.string' => 'O nome do produto deve ser um texto.',
            'name.min' => 'O nome do produto deve ter no mínimo :min caracteres.',
            'name.max' => 'O nome do produto deve ter no máximo :max caracteres.',
            'sale_price.required' => 'O preço de venda é obrigatório quando fornecido.',
            'sale_price.numeric' => 'O preço de venda deve ser um número.',
            'sale_price.min' => 'O preço de venda deve ser maior que zero.',
        ];
    }
}
