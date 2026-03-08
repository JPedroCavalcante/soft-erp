import { createRouter, createWebHistory } from 'vue-router';
import type { RouteRecordRaw } from 'vue-router';
import productRoutes from '@/modules/products/routes';
import purchasesRoutes from '@/modules/purchases/routes';

const routes: RouteRecordRaw[] = [
  ...productRoutes,
  ...purchasesRoutes,
];

const router = createRouter({
  history: createWebHistory(),
  routes,
});

export default router;
