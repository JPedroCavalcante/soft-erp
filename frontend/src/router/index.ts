import { createRouter, createWebHistory } from 'vue-router';
import type { RouteRecordRaw, NavigationGuardNext, RouteLocationNormalized } from 'vue-router';
import { useAuthStore } from '@/stores/auth';
import dashboardRoutes from '@/modules/dashboard/routes';
import reportRoutes from '@/modules/reports/routes';
import productRoutes from '@/modules/products/routes';
import purchasesRoutes from '@/modules/purchases/routes';
import salesRoutes from '@/modules/sales/routes';
import LoginView from '@/modules/auth/views/LoginView.vue';

const routes: RouteRecordRaw[] = [
  {
    path: '/login',
    name: 'login',
    component: LoginView,
    meta: { requiresAuth: false },
  },
  {
    path: '/',
    redirect: '/dashboard',
  },
  ...dashboardRoutes,
  ...reportRoutes,
  ...productRoutes,
  ...purchasesRoutes,
  ...salesRoutes,
];

const router = createRouter({
  history: createWebHistory(),
  routes,
});

router.beforeEach(async (to: RouteLocationNormalized, _from: RouteLocationNormalized, next: NavigationGuardNext) => {
  const authStore = useAuthStore();

  if (!authStore.user && to.name !== 'login') {
    await authStore.checkAuth();
  }

  const requiresAuth = to.meta.requiresAuth !== false;

  if (requiresAuth && !authStore.isAuthenticated) {
    next({ name: 'login' });
  } else if (to.name === 'login' && authStore.isAuthenticated) {
    next({ path: '/' });
  } else {
    next();
  }
});

export default router;
