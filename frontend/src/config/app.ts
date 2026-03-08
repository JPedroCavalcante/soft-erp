export const CACHE_TIME = {
  SHORT: 30000,
  MEDIUM: 60000,
  LONG: 300000,
} as const;

export const STOCK_LEVELS = {
  OUT_OF_STOCK: 0,
  LOW_STOCK_THRESHOLD: 10,
} as const;

export const TOAST_DURATION = {
  SUCCESS: 3000,
  ERROR: 4000,
  WARNING: 3500,
  INFO: 3000,
} as const;
