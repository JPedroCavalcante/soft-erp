<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Relatório de Vendas</title>
    <style>
        body { font-family: Arial, sans-serif; font-size: 12px; }
        h1 { text-align: center; color: #333; }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        th { background-color: #4CAF50; color: white; padding: 10px; text-align: left; }
        td { padding: 8px; border-bottom: 1px solid #ddd; }
        tr:hover { background-color: #f5f5f5; }
        .total { font-weight: bold; background-color: #e8f5e9; }
    </style>
</head>
<body>
    <h1>Relatório de Vendas</h1>
    <p><strong>Data de Geração:</strong> {{ now()->format('d/m/Y H:i') }}</p>

    <table>
        <thead>
            <tr>
                <th>ID</th>
                <th>Cliente</th>
                <th>Valor Total</th>
                <th>Lucro</th>
                <th>Itens</th>
                <th>Data</th>
            </tr>
        </thead>
        <tbody>
            @foreach($data as $sale)
            <tr>
                <td>{{ $sale->id }}</td>
                <td>{{ $sale->customer }}</td>
                <td>R$ {{ number_format($sale->total_amount, 2, ',', '.') }}</td>
                <td>R$ {{ number_format($sale->total_profit, 2, ',', '.') }}</td>
                <td>{{ $sale->items_count }}</td>
                <td>{{ \Carbon\Carbon::parse($sale->created_at)->format('d/m/Y') }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>
</body>
</html>
