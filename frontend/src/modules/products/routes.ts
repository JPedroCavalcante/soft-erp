import type { RouteRecordRaw } from 'vue-router';
import ProductsView from './views/ProductsView.vue';

const productRoutes: RouteRecordRaw[] = [
  {
    path: '/products',
    name: 'Products',
    component: ProductsView,
    meta: { title: 'Produtos' },
  },
];

export default productRoutes;
