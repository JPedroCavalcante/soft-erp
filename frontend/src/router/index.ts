import { createRouter, createWebHistory } from 'vue-router';
import type { RouteRecordRaw } from 'vue-router';
import productRoutes from '@/modules/products/routes';

const routes: RouteRecordRaw[] = [

  ...productRoutes,
];

const router = createRouter({
  history: createWebHistory(),
  routes,
});

export default router;
