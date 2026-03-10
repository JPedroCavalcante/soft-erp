export const API_ENDPOINTS = {
  PRODUCTS: '/product/products',
  PURCHASES: '/purchase/purchases',
  SALES: '/sale/sales',
  DASHBOARD: '/dashboard/metrics',
  REPORTS: '/report/reports',
} as const;

export const API_CONFIG = {
  BASE_URL: import.meta.env.VITE_API_BASE_URL || 'http://localhost:8000/api',
  TIMEOUT: 30000,
} as const;
