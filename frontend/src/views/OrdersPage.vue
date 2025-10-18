<script setup lang="ts">
import { ref, onMounted } from 'vue';
import { orderApi } from '@/api';

interface Order {
  id: number;
  order_number: string;
  customer_name: string;
  status: string;
  created_at: string;
}

const orders = ref<Order[]>([]);
const isLoading = ref(false);
const newOrderData = ref({ customer_name: '李小明', items: 'A1:1,B2:2' });
const message = ref('');

const statusColorMap: Record<string, string> = {
  New: 'text-blue-500 bg-blue-100',
  Picking: 'text-yellow-600 bg-yellow-100',
  'Picking Completed': 'text-orange-500 bg-orange-100',
  Sorting: 'text-indigo-500 bg-indigo-100',
  Packing: 'text-purple-400 bg-purple-100',
  Packed: 'text-purple-700 bg-purple-200',
  Shipped: 'text-green-600 bg-green-100',
};

const getStatusColor = (status: string) => statusColorMap[status] || 'text-gray-500 bg-gray-100';

async function fetchOrders() {
  isLoading.value = true;
  message.value = '';
  try {
    // 假設 orderApi.getOrders() 已經處理了後端回傳的結構
    const data = await orderApi.getOrders();
    orders.value = data;
  } catch (error: any) {
    message.value = `載入訂單失敗: ${error.message}`;
  } finally {
    isLoading.value = false;
  }
}

async function createNewOrder() {
  message.value = '';
  if (!newOrderData.value.customer_name || !newOrderData.value.items) {
    message.value = '請填寫客戶名稱和商品清單。';
    return;
  }
  try {
    const response = await orderApi.createOrder(newOrderData.value);
    const orderNumber = (response as any).order_number || '新訂單';
    message.value = `訂單 ${orderNumber} 創建成功！WMS流程已啟動 (Picking)。`;
    newOrderData.value = { customer_name: '王小華', items: 'C3:3,D4:1' };
    await fetchOrders();
  } catch (error: any) {
    message.value = `創建訂單失敗: ${error.message}`;
  }
}

onMounted(fetchOrders);
</script>

<template>
  <div class="p-6 max-w-7xl mx-auto">
    <h1 class="text-3xl font-extrabold mb-6 text-gray-800 border-b pb-2">📦 訂單管理 (OMS) - 流程起點</h1>

    <!-- 創建新訂單表單 -->
    <div class="bg-white shadow-xl rounded-xl p-6 mb-8 border-t-8 border-blue-500">
      <h2 class="text-xl font-bold mb-4 text-blue-600">快速創建新訂單 (模擬電商下單)</h2>
      <div class="flex flex-col md:flex-row gap-4 mb-4">
        <input
          v-model="newOrderData.customer_name"
          placeholder="客戶名稱 (e.g., 李小明)"
          class="flex-1 p-3 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500 transition shadow-sm"
        />
        <input
          v-model="newOrderData.items"
          placeholder="商品清單 (e.g., SKU1:1,SKU2:2)"
          class="flex-1 p-3 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500 transition shadow-sm"
        />
        <button
          @click="createNewOrder"
          :disabled="isLoading"
          class="w-full md:w-auto bg-blue-600 hover:bg-blue-700 text-white font-bold py-3 px-6 rounded-lg shadow-md transition duration-150 ease-in-out disabled:opacity-50 disabled:cursor-not-allowed"
        >
          {{ isLoading ? '處理中...' : '創建訂單 (觸發 WMS 流程)' }}
        </button>
      </div>
      <p v-if="message" :class="message.includes('失敗') ? 'text-red-500 font-semibold' : 'text-green-600 font-semibold'" class="mt-4 text-sm bg-gray-50 p-2 rounded">{{ message }}</p>
    </div>

    <!-- 訂單列表 -->
    <h2 class="text-2xl font-bold mb-4 text-gray-700">所有訂單列表</h2>
    <div class="bg-white shadow-xl rounded-xl overflow-hidden">
      <table class="min-w-full divide-y divide-gray-200">
        <thead class="bg-gray-100">
          <tr>
            <th class="px-6 py-3 text-left text-xs font-bold text-gray-600 uppercase tracking-wider">
              訂單編號
            </th>
            <th class="px-6 py-3 text-left text-xs font-bold text-gray-600 uppercase tracking-wider">
              客戶
            </th>
            <th class="px-6 py-3 text-left text-xs font-bold text-gray-600 uppercase tracking-wider">
              狀態
            </th>
            <th class="px-6 py-3 text-left text-xs font-bold text-gray-600 uppercase tracking-wider">
              創建時間
            </th>
          </tr>
        </thead>
        <tbody class="bg-white divide-y divide-gray-200">
          <tr v-if="isLoading">
            <td colspan="4" class="p-6 text-center text-gray-500">載入中...</td>
          </tr>
          <tr v-else-if="orders.length === 0">
            <td colspan="4" class="p-6 text-center text-gray-500">目前沒有訂單。</td>
          </tr>
          <tr v-for="order in orders" :key="order.id" class="hover:bg-gray-50 transition duration-150">
            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
              {{ order.order_number }}
            </td>
            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
              {{ order.customer_name }}
            </td>
            <td class="px-6 py-4 whitespace-nowrap">
              <span
                :class="getStatusColor(order.status)"
                class="px-3 py-1 inline-flex text-xs leading-5 font-semibold rounded-full"
              >
                {{ order.status }}
              </span>
            </td>
            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
              {{ new Date(order.created_at).toLocaleString() }}
            </td>
          </tr>
        </tbody>
      </table>
    </div>
  </div>
</template>
