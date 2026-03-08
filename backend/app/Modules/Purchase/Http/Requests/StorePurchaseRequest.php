<?php

namespace App\Modules\Purchase\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StorePurchaseRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'supplier' => [
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
            'items.*.unit_price' => [
                'required',
                'numeric',
                'min:0.01',
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'supplier.required' => 'O nome do fornecedor é obrigatório.',
            'supplier.string' => 'O nome do fornecedor deve ser um texto.',
            'supplier.min' => 'O nome do fornecedor deve ter no mínimo :min caracteres.',
            'supplier.max' => 'O nome do fornecedor deve ter no máximo :max caracteres.',
            'items.required' => 'É necessário incluir ao menos um item na compra.',
            'items.array' => 'Os items devem estar em formato de array.',
            'items.min' => 'É necessário incluir ao menos um item na compra.',
            'items.*.product_id.required' => 'O ID do produto é obrigatório.',
            'items.*.product_id.integer' => 'O ID do produto deve ser um número inteiro.',
            'items.*.product_id.exists' => 'O produto informado não existe.',
            'items.*.quantity.required' => 'A quantidade é obrigatória.',
            'items.*.quantity.integer' => 'A quantidade deve ser um número inteiro.',
            'items.*.quantity.min' => 'A quantidade deve ser no mínimo :min.',
            'items.*.unit_price.required' => 'O preço unitário é obrigatório.',
            'items.*.unit_price.numeric' => 'O preço unitário deve ser um número.',
            'items.*.unit_price.min' => 'O preço unitário deve ser maior que zero.',
        ];
    }
}
