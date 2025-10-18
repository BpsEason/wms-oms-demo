<script setup lang="ts">
import { ref, onMounted } from 'vue';
import { flowApi, taskApi } from '@/api';

interface PackingTask {
  id: number;
  order_id: number;
  status: string; // ReadyForPacking | Packing | Completed
  order: { order_number: string, customer_name: string };
}

const tasks = ref<PackingTask[]>([]);
const isLoading = ref(false);
const message = ref('');

async function fetchTasks() {
  isLoading.value = true;
  message.value = '';
  try {
    const data = await flowApi.getPackingTasks();
    tasks.value = data;
  } catch (error: any) {
    message.value = `載入包裝任務失敗: ${error.message}`;
  } finally {
    isLoading.value = false;
  }
}

async function startTask(taskId: number, orderNumber: string) {
  message.value = '';
  try {
    await taskApi.startPacking(taskId);
    message.value = `包裝任務 (訂單 ${orderNumber}) 已開始！`;
    await fetchTasks();
  } catch (error: any) {
    message.value = `開始包裝失敗: ${error.message}`;
  }
}

async function completeTask(taskId: number, orderNumber: string) {
  message.value = '';
  try {
    await taskApi.completePacking(taskId);
    message.value = `包裝任務 (訂單 ${orderNumber}) 完成！訂單狀態變為 Packed，並觸發 ShippingStarted 事件。`;
    await fetchTasks(); // 刷新列表
  } catch (error: any) {
    message.value = `完成包裝失敗: ${error.message}`;
  }
}

onMounted(fetchTasks);
</script>

<template>
  <div class="p-6 max-w-7xl mx-auto">
    <h1 class="text-3xl font-extrabold mb-6 text-gray-800 border-b pb-2">🛍️ 包裝作業 (Packing)</h1>
    <p class="text-lg text-gray-600 mb-6">處理狀態為 **ReadyForPacking** 和 **Packing** 的任務。完成後，訂單狀態轉為 **Packed**。</p>

    <p v-if="message" :class="message.includes('失敗') ? 'text-red-500 bg-red-100' : 'text-green-600 bg-green-100'" class="p-3 rounded-lg mb-6 font-medium border">{{ message }}</p>

    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
      <div v-if="isLoading" class="col-span-full text-center p-8 text-gray-500">載入中...</div>
      <div v-else-if="tasks.length === 0" class="col-span-full text-center p-8 text-gray-500">目前沒有待處理的包裝任務。</div>

      <div v-for="task in tasks" :key="task.id" class="bg-white shadow-xl rounded-xl p-5 border-t-4" :class="task.status === 'Packing' ? 'border-pink-500' : 'border-purple-500'">
        <h2 class="text-xl font-bold text-gray-800 mb-2">訂單 {{ task.order.order_number }}</h2>
        <p class="text-sm text-gray-500 mb-4">客戶: {{ task.order.customer_name }}</p>

        <span
            :class="task.status === 'Packing' ? 'bg-pink-100 text-pink-700' : 'bg-purple-100 text-purple-700'"
            class="px-3 py-1 inline-flex text-xs leading-5 font-semibold rounded-full mb-4"
        >
            狀態: {{ task.status }}
        </span>
        
        <div class="flex gap-2">
            <button
                v-if="task.status === 'ReadyForPacking'"
                @click="startTask(task.id, task.order.order_number)"
                :disabled="isLoading"
                class="flex-1 bg-purple-600 hover:bg-purple-700 text-white font-bold py-2 px-4 rounded-lg shadow transition duration-150 disabled:opacity-50 disabled:cursor-not-allowed"
            >
                開始包裝
            </button>
            <button
                v-else-if="task.status === 'Packing'"
                @click="completeTask(task.id, task.order.order_number)"
                :disabled="isLoading"
                class="flex-1 bg-pink-600 hover:bg-pink-700 text-white font-bold py-2 px-4 rounded-lg shadow transition duration-150 disabled:opacity-50 disabled:cursor-not-allowed"
            >
                完成包裝
            </button>
            <span v-else class="text-gray-500 text-sm italic">已完成</span>
        </div>
      </div>
    </div>
  </div>
</template>
