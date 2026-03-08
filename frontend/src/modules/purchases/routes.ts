import type { RouteRecordRaw } from 'vue-router';

const purchasesRoutes: RouteRecordRaw[] = [
  {
    path: '/purchases',
    name: 'purchases',
    component: () => import('./views/PurchasesView.vue'),
  },
];

export default purchasesRoutes;
