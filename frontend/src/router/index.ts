import { createRouter, createWebHistory } from 'vue-router';
// 導入所有新增的頁面 (假設它們將被創建在 views/ 目錄下)
import OrdersPage from '../views/OrdersPage.vue';
import PickingPage from '../views/PickingPage.vue';
import SortingPage from '../views/SortingPage.vue';
import PackingPage from '../views/PackingPage.vue';
import ShippingPage from '../views/ShippingPage.vue';

const router = createRouter({
  history: createWebHistory(import.meta.env.BASE_URL),
  routes: [
    {
      path: '/',
      name: 'Orders',
      component: OrdersPage,
      meta: { title: '訂單管理 (OMS)' }
    },
    {
      path: '/picking',
      name: 'Picking',
      component: PickingPage,
      meta: { title: '揀貨作業 (Picking)' }
    },
    {
      path: '/sorting',
      name: 'Sorting',
      component: SortingPage,
      meta: { title: '分揀作業 (Sorting)' }
    },
    {
      path: '/packing',
      name: 'Packing',
      component: PackingPage,
      meta: { title: '包裝作業 (Packing)' }
    },
    {
      path: '/shipping',
      name: 'Shipping',
      component: ShippingPage,
      meta: { title: '出貨作業 (Shipping)' }
    },
  ],
});

router.beforeEach((to, from, next) => {
  document.title = (to.meta.title ? `${to.meta.title} | WMS Demo` : 'WMS Demo');
  next();
});

export default router;
