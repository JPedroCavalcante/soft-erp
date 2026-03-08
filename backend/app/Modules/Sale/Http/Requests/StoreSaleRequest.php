<?php

namespace App\Modules\Sale\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreSaleRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'customer' => [
                'required',
                'string',
                'min:3',
                'max:255',
            ],
            'items' => [
                'required',
                'array',
                'min:1',
            ],
            'items.*.product_id' => [
                'required',
                'integer',
                'exists:products,id',
            ],
            'items.*.quantity' => [
                'required',
                'integer',
                'min:1',
            ],
            'items.*.unit_sale_price' => [
                'required',
                'numeric',
                'min:0.01',
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'customer.required' => 'O nome do cliente é obrigatório.',
            'customer.string' => 'O nome do cliente deve ser um texto.',
            'customer.min' => 'O nome do cliente deve ter no mínimo :min caracteres.',
            'customer.max' => 'O nome do cliente deve ter no máximo :max caracteres.',
            'items.required' => 'É necessário incluir ao menos um item na venda.',
            'items.array' => 'Os items devem estar em formato de array.',
            'items.min' => 'É necessário incluir ao menos um item na venda.',
            'items.*.product_id.required' => 'O ID do produto é obrigatório.',
            'items.*.product_id.integer' => 'O ID do produto deve ser um número inteiro.',
            'items.*.product_id.exists' => 'O produto informado não existe.',
            'items.*.quantity.required' => 'A quantidade é obrigatória.',
            'items.*.quantity.integer' => 'A quantidade deve ser um número inteiro.',
            'items.*.quantity.min' => 'A quantidade deve ser no mínimo :min.',
            'items.*.unit_sale_price.required' => 'O preço de venda unitário é obrigatório.',
            'items.*.unit_sale_price.numeric' => 'O preço de venda unitário deve ser um número.',
            'items.*.unit_sale_price.min' => 'O preço de venda unitário deve ser maior que zero.',
        ];
    }
}
