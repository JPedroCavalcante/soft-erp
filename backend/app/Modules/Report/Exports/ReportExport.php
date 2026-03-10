<?php

namespace App\Modules\Report\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class ReportExport implements FromArray, WithHeadings, WithStyles
{
    public function __construct(
        private readonly array $data,
        private readonly string $reportName
    ) {}

    public function array(): array
    {
        return array_map(function ($item) {
            return (array) $item;
        }, $this->data);
    }

    public function headings(): array
    {
        return match($this->reportName) {
            'sales' => ['ID', 'Cliente', 'Valor Total', 'Lucro Total', 'Qtd Itens', 'Data'],
            'purchases' => ['ID', 'Fornecedor', 'Valor Total', 'Qtd Itens', 'Data'],
            'profit' => ['ID', 'Produto', 'Qtd Vendida', 'Lucro Total', 'Preço Médio', 'Custo Médio'],
            'stock' => ['ID', 'Produto', 'Estoque', 'Custo Médio', 'Preço Venda', 'Valor Estoque', 'Status'],
            default => [],
        };
    }

    public function styles(Worksheet $sheet): array
    {
        return [
            1 => ['font' => ['bold' => true]],
        ];
    }
}
