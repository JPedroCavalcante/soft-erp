export const formatCurrency = (value: string | number): string => {
  const numValue = typeof value === 'string' ? parseFloat(value) : value;
  return new Intl.NumberFormat('pt-BR', {
    style: 'currency',
    currency: 'BRL',
  }).format(numValue);
};

export const getStockClass = (stock: number): string => {
  if (stock === 0) return 'stock-empty';
  if (stock < 10) return 'stock-low';
  return 'stock-ok';
};
