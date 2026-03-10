<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Relatório de Compras</title>
    <style>
        body { font-family: Arial, sans-serif; font-size: 12px; }
        h1 { text-align: center; color: #333; }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        th { background-color: #2196F3; color: white; padding: 10px; text-align: left; }
        td { padding: 8px; border-bottom: 1px solid #ddd; }
        tr:hover { background-color: #f5f5f5; }
    </style>
</head>
<body>
    <h1>Relatório de Compras</h1>
    <p><strong>Data de Geração:</strong> {{ now()->format('d/m/Y H:i') }}</p>

    <table>
        <thead>
            <tr>
                <th>ID</th>
                <th>Fornecedor</th>
                <th>Valor Total</th>
                <th>Itens</th>
                <th>Data</th>
            </tr>
        </thead>
        <tbody>
            @foreach($data as $purchase)
            <tr>
                <td>{{ $purchase->id }}</td>
                <td>{{ $purchase->supplier }}</td>
                <td>R$ {{ number_format($purchase->total_amount, 2, ',', '.') }}</td>
                <td>{{ $purchase->items_count }}</td>
                <td>{{ \Carbon\Carbon::parse($purchase->created_at)->format('d/m/Y') }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>
</body>
</html>
