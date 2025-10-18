<script setup lang="ts">
import { ref, onMounted } from 'vue';
import { flowApi, taskApi } from '@/api';

interface ShipmentOrder {
  id: number;
  order_number: string;
  customer_name: string;
  status: 'Packed' | 'Shipped'; // 訂單狀態
  shipment: {
    tracking_number: string;
    carrier: string;
    status: 'Pending' | 'InTransit' | 'Delivered'; // 貨件狀態
    shipped_at: string | null;
  } | null;
}

const shipments = ref<ShipmentOrder[]>([]);
const isLoading = ref(false);
const message = ref('');

// 狀態顏色映射
const statusColorMap: Record<string, string> = {
  Packed: 'text-purple-700 bg-purple-200',
  Shipped: 'text-green-600 bg-green-100',
  Pending: 'text-yellow-600 bg-yellow-100', // 貨件待出貨
  InTransit: 'text-blue-500 bg-blue-100', // 貨件運輸中
  Delivered: 'text-gray-600 bg-gray-300',
};

const getStatusColor = (status: string) => statusColorMap[status] || 'text-gray-500 bg-gray-100';

async function fetchShipments() {
  isLoading.value = true;
  message.value = '';
  try {
    // 假設後端回傳結構為 { orders: ShipmentOrder[] }
    const data = await flowApi.getShipments();
    shipments.value = (data as any).orders as ShipmentOrder[];
  } catch (error: any) {
    message.value = `載入貨件失敗: ${error.message}`;
  } finally {
    isLoading.value = false;
  }
}

async function handleShip(orderId: number, orderNumber: string) {
  message.value = '';
  try {
    const response = await taskApi.shipOrder(orderId);
    const trackingNumber = (response as any).order.shipment.tracking_number;
    message.value = `訂單 ${orderNumber} 已成功出貨！訂單狀態轉為 Shipped。追蹤碼: ${trackingNumber}`;
    await fetchShipments(); // 刷新列表
  } catch (error: any) {
    message.value = `出貨失敗: ${error.message}`;
  }
}

onMounted(fetchShipments);
</script>

<template>
  <div class="p-6 max-w-7xl mx-auto">
    <h1 class="text-3xl font-extrabold mb-6 text-gray-800 border-b pb-2">🚢 出貨作業 (Shipping) - 流程終點</h1>
    <p class="text-lg text-gray-600 mb-6">顯示狀態為 **Packed** (待出貨) 或 **Shipped** (已出貨) 的訂單。</p>

    <p v-if="message" :class="message.includes('失敗') ? 'text-red-500 bg-red-100' : 'text-green-600 bg-green-100'" class="p-3 rounded-lg mb-6 font-medium border">{{ message }}</p>

    <!-- 貨件列表 -->
    <div class="bg-white shadow-xl rounded-xl overflow-hidden">
      <table class="min-w-full divide-y divide-gray-200">
        <thead class="bg-green-100">
          <tr>
            <th class="px-6 py-3 text-left text-xs font-bold text-green-800 uppercase tracking-wider">
              訂單編號
            </th>
            <th class="px-6 py-3 text-left text-xs font-bold text-green-800 uppercase tracking-wider">
              訂單狀態
            </th>
            <th class="px-6 py-3 text-left text-xs font-bold text-green-800 uppercase tracking-wider">
              物流公司/追蹤碼
            </th>
            <th class="px-6 py-3 text-left text-xs font-bold text-green-800 uppercase tracking-wider">
              動作
            </th>
          </tr>
        </thead>
        <tbody class="bg-white divide-y divide-gray-200">
          <tr v-if="isLoading">
            <td colspan="4" class="p-6 text-center text-gray-500">載入中...</td>
          </tr>
          <tr v-else-if="shipments.length === 0">
            <td colspan="4" class="p-6 text-center text-gray-500">目前沒有待出貨或已出貨的訂單。</td>
          </tr>
          <tr v-for="order in shipments" :key="order.id" class="hover:bg-gray-50 transition duration-150">
            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
              {{ order.order_number }}
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
              <template v-if="order.shipment">
                <p class="font-semibold text-gray-800">{{ order.shipment.carrier || 'N/A' }}</p>
                <p class="text-xs text-gray-600">{{ order.shipment.tracking_number || '待生成' }}</p>
                <span
                    :class="getStatusColor(order.shipment.status)"
                    class="mt-1 px-2 inline-flex text-xs leading-5 font-semibold rounded-full"
                >
                    貨件狀態: {{ order.shipment.status }}
                </span>
              </template>
              <span v-else class="text-xs text-yellow-500">等待 ShippingStarted 事件初始化...</span>
            </td>
            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
              <button
                v-if="order.status === 'Packed'"
                @click="handleShip(order.id, order.order_number)"
                :disabled="isLoading"
                class="bg-green-500 hover:bg-green-600 text-white font-bold py-2 px-4 rounded-lg text-sm transition duration-150 shadow disabled:opacity-50 disabled:cursor-not-allowed"
              >
                確認出貨
              </button>
              <span v-else class="text-gray-500 text-sm italic">已完成出貨 (Shipped)</span>
            </td>
          </tr>
        </tbody>
      </table>
    </div>
  </div>
</template>
