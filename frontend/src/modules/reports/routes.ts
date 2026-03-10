import type { RouteRecordRaw } from 'vue-router';
import ReportsView from './views/ReportsView.vue';

const reportRoutes: RouteRecordRaw[] = [
  {
    path: '/reports',
    name: 'Reports',
    component: ReportsView,
    meta: { title: 'Relatórios' },
  },
];

export default reportRoutes;
