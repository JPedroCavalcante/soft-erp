<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Relatório de Lucro por Produto</title>
    <style>
        body { font-family: Arial, sans-serif; font-size: 12px; }
        h1 { text-align: center; color: #333; }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        th { background-color: #FF9800; color: white; padding: 10px; text-align: left; }
        td { padding: 8px; border-bottom: 1px solid #ddd; }
        tr:hover { background-color: #f5f5f5; }
        .positive { color: green; font-weight: bold; }
    </style>
</head>
<body>
    <h1>Relatório de Lucro por Produto</h1>
    <p><strong>Data de Geração:</strong> {{ now()->format('d/m/Y H:i') }}</p>

    <table>
        <thead>
            <tr>
                <th>ID</th>
                <th>Produto</th>
                <th>Qtd Vendida</th>
                <th>Lucro Total</th>
                <th>Preço Médio</th>
                <th>Custo Médio</th>
            </tr>
        </thead>
        <tbody>
            @foreach($data as $item)
            <tr>
                <td>{{ $item->id }}</td>
                <td>{{ $item->name }}</td>
                <td>{{ $item->total_quantity_sold }}</td>
                <td class="positive">R$ {{ number_format($item->total_profit, 2, ',', '.') }}</td>
                <td>R$ {{ number_format($item->avg_sale_price, 2, ',', '.') }}</td>
                <td>R$ {{ number_format($item->avg_cost, 2, ',', '.') }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>
</body>
</html>
