import { createRouter, createWebHistory } from 'vue-router';
import type { RouteRecordRaw } from 'vue-router';
import productRoutes from '@/modules/products/routes';
import purchasesRoutes from '@/modules/purchases/routes';
import salesRoutes from '@/modules/sales/routes';

const routes: RouteRecordRaw[] = [
  ...productRoutes,
  ...purchasesRoutes,
  ...salesRoutes,
];

const router = createRouter({
  history: createWebHistory(),
  routes,
});

export default router;
