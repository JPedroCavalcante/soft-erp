<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Relatório de Estoque</title>
    <style>
        body { font-family: Arial, sans-serif; font-size: 12px; }
        h1 { text-align: center; color: #333; }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        th { background-color: #9C27B0; color: white; padding: 10px; text-align: left; }
        td { padding: 8px; border-bottom: 1px solid #ddd; }
        tr:hover { background-color: #f5f5f5; }
        .status { font-weight: bold; padding: 4px 8px; border-radius: 4px; }
        .sem-estoque { background-color: #f44336; color: white; }
        .baixo { background-color: #ff9800; color: white; }
        .normal { background-color: #2196F3; color: white; }
        .alto { background-color: #4CAF50; color: white; }
    </style>
</head>
<body>
    <h1>Relatório de Estoque</h1>
    <p><strong>Data de Geração:</strong> {{ now()->format('d/m/Y H:i') }}</p>

    <table>
        <thead>
            <tr>
                <th>ID</th>
                <th>Produto</th>
                <th>Estoque</th>
                <th>Custo Médio</th>
                <th>Preço Venda</th>
                <th>Valor Estoque</th>
                <th>Status</th>
            </tr>
        </thead>
        <tbody>
            @foreach($data as $product)
            <tr>
                <td>{{ $product->id }}</td>
                <td>{{ $product->name }}</td>
                <td>{{ $product->stock }}</td>
                <td>R$ {{ number_format($product->average_cost, 2, ',', '.') }}</td>
                <td>R$ {{ number_format($product->sale_price, 2, ',', '.') }}</td>
                <td>R$ {{ number_format($product->stock_value, 2, ',', '.') }}</td>
                <td>
                    <span class="status {{ strtolower(str_replace(' ', '-', $product->stock_status)) }}">
                        {{ $product->stock_status }}
                    </span>
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
</body>
</html>
