import type { RouteRecordRaw } from 'vue-router';

const salesRoutes: RouteRecordRaw[] = [
  {
    path: '/sales',
    name: 'sales',
    component: () => import('./views/SalesView.vue'),
  },
];

export default salesRoutes;
