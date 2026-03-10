import type { RouteRecordRaw } from 'vue-router';
import DashboardView from './views/DashboardView.vue';

const dashboardRoutes: RouteRecordRaw[] = [
  {
    path: '/dashboard',
    name: 'Dashboard',
    component: DashboardView,
    meta: { title: 'Dashboard' },
  },
];

export default dashboardRoutes;
