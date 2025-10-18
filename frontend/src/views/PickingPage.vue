<script setup lang="ts">
import { ref, onMounted } from 'vue';
import { flowApi, taskApi } from '@/api';

interface PickingTask {
  id: number;
  order_id: number;
  sku: string;
  quantity: number;
  location: string;
  status: string;
  order: { order_number: string, customer_name: string };
}

const tasks = ref<PickingTask[]>([]);
const isLoading = ref(false);
const message = ref('');

async function fetchTasks() {
  isLoading.value = true;
  message.value = '';
  try {
    const data = await flowApi.getPickingTasks();
    tasks.value = data;
  } catch (error: any) {
    message.value = `載入揀貨任務失敗: ${error.message}`;
  } finally {
    isLoading.value = false;
  }
}

async function completeTask(taskId: number, orderNumber: string) {
  message.value = '';
  try {
    await taskApi.completePicking(taskId);
    message.value = `揀貨任務 (訂單 ${orderNumber}) 完成！訂單已進入 Sorting 流程。`;
    await fetchTasks(); // 刷新列表
  } catch (error: any) {
    message.value = `完成揀貨任務失敗: ${error.message}`;
  }
}

onMounted(fetchTasks);
</script>

<template>
  <div class="p-6 max-w-7xl mx-auto">
    <h1 class="text-3xl font-extrabold mb-6 text-gray-800 border-b pb-2">🚚 揀貨作業 (Picking)</h1>
    <p class="text-lg text-gray-600 mb-6">顯示狀態為 **New** 或 **Picking** 的任務。完成後，訂單狀態轉為 **Sorting**。</p>

    <p v-if="message" :class="message.includes('失敗') ? 'text-red-500 bg-red-100' : 'text-green-600 bg-green-100'" class="p-3 rounded-lg mb-6 font-medium border">{{ message }}</p>

    <div class="bg-white shadow-xl rounded-xl overflow-hidden">
      <table class="min-w-full divide-y divide-gray-200">
        <thead class="bg-yellow-100">
          <tr>
            <th class="px-6 py-3 text-left text-xs font-bold text-yellow-800 uppercase tracking-wider">
              訂單編號
            </th>
            <th class="px-6 py-3 text-left text-xs font-bold text-yellow-800 uppercase tracking-wider">
              SKU
            </th>
            <th class="px-6 py-3 text-left text-xs font-bold text-yellow-800 uppercase tracking-wider">
              數量
            </th>
            <th class="px-6 py-3 text-left text-xs font-bold text-yellow-800 uppercase tracking-wider">
              儲位
            </th>
            <th class="px-6 py-3 text-left text-xs font-bold text-yellow-800 uppercase tracking-wider">
              動作
            </th>
          </tr>
        </thead>
        <tbody class="bg-white divide-y divide-gray-200">
          <tr v-if="isLoading">
            <td colspan="5" class="p-6 text-center text-gray-500">載入中...</td>
          </tr>
          <tr v-else-if="tasks.length === 0">
            <td colspan="5" class="p-6 text-center text-gray-500">目前沒有待處理的揀貨任務。</td>
          </tr>
          <tr v-for="task in tasks" :key="task.id" class="hover:bg-gray-50 transition duration-150">
            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
              {{ task.order.order_number }}
            </td>
            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
              {{ task.sku }}
            </td>
            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
              {{ task.quantity }}
            </td>
            <td class="px-6 py-4 whitespace-nowrap text-sm font-semibold text-orange-600">
              {{ task.location }}
            </td>
            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
              <button
                @click="completeTask(task.id, task.order.order_number)"
                :disabled="isLoading || task.status === 'Completed'"
                class="bg-yellow-600 hover:bg-yellow-700 text-white font-bold py-2 px-4 rounded-lg shadow transition duration-150 disabled:opacity-50 disabled:cursor-not-allowed"
              >
                完成揀貨
              </button>
            </td>
          </tr>
        </tbody>
      </table>
    </div>
  </div>
</template>
