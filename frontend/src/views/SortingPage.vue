<script setup lang="ts">
import { ref, onMounted } from 'vue';
import { flowApi, taskApi } from '@/api';

interface SortingTask {
  id: number;
  order_id: number;
  sku_list: { sku: string; sorted: boolean }[]; // 假設後端提供分揀列表
  status: string; // Pending | Completed
  order: { order_number: string, customer_name: string };
}

const tasks = ref<SortingTask[]>([]);
const isLoading = ref(false);
const message = ref('');

async function fetchTasks() {
  isLoading.value = true;
  message.value = '';
  try {
    const data = await flowApi.getSortingTasks();
    tasks.value = data.map(task => ({
      ...task,
      // 假設後端在 sorting_tasks 裡存了 sku_list 的 JSON 字符串
      sku_list: JSON.parse(task.sku_list_json || '[]').map((sku: string) => ({ sku, sorted: false })),
    }));
  } catch (error: any) {
    message.value = `載入分揀任務失敗: ${error.message}`;
  } finally {
    isLoading.value = false;
  }
}

async function sortItem(taskId: number, orderNumber: string) {
  message.value = '';
  try {
    // 模擬掃描商品，觸發後端 API
    await taskApi.sortItem(taskId);
    message.value = `分揀任務 (訂單 ${orderNumber}) 進度更新！`;
    await fetchTasks(); // 刷新列表
  } catch (error: any) {
    message.value = `分揀失敗: ${error.message}`;
  }
}

// 模擬標記為已分揀 (僅用於前端視覺，實際進度由 API 更新)
function markAsSorted(task: SortingTask, sku: string) {
    const item = task.sku_list.find(i => i.sku === sku);
    if (item) {
        item.sorted = true;
    }
}

onMounted(fetchTasks);
</script>

<template>
  <div class="p-6 max-w-7xl mx-auto">
    <h1 class="text-3xl font-extrabold mb-6 text-gray-800 border-b pb-2">📦 分揀作業 (Sorting)</h1>
    <p class="text-lg text-gray-600 mb-6">將揀出的商品分揀到對應的訂單箱位。完成後，訂單狀態轉為 **Packing**。</p>

    <p v-if="message" :class="message.includes('失敗') ? 'text-red-500 bg-red-100' : 'text-green-600 bg-green-100'" class="p-3 rounded-lg mb-6 font-medium border">{{ message }}</p>

    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
      <div v-if="isLoading" class="col-span-full text-center p-8 text-gray-500">載入中...</div>
      <div v-else-if="tasks.length === 0" class="col-span-full text-center p-8 text-gray-500">目前沒有待處理的分揀任務。</div>

      <div v-for="task in tasks" :key="task.id" class="bg-white shadow-xl rounded-xl p-5 border-t-4 border-indigo-500 flex flex-col justify-between">
        <div>
            <h2 class="text-xl font-bold text-indigo-700 mb-2">訂單 {{ task.order.order_number }}</h2>
            <p class="text-sm text-gray-500 mb-4">客戶: {{ task.order.customer_name }}</p>
            
            <div class="space-y-2">
                <div v-for="item in task.sku_list" :key="item.sku" class="flex items-center justify-between p-2 rounded-lg" :class="item.sorted ? 'bg-green-50' : 'bg-gray-100'">
                    <span class="font-medium" :class="item.sorted ? 'text-green-600 line-through' : 'text-gray-800'">{{ item.sku }}</span>
                    <span v-if="item.sorted" class="text-green-500 text-xs font-semibold">已分揀</span>
                    <button
                        v-else
                        @click="markAsSorted(task, item.sku)"
                        class="bg-indigo-500 hover:bg-indigo-600 text-white text-xs py-1 px-3 rounded-full transition shadow-sm"
                    >
                        掃描
                    </button>
                </div>
            </div>
        </div>
        
        <button
            @click="sortItem(task.id, task.order.order_number)"
            :disabled="isLoading || task.status === 'Completed'"
            class="mt-4 bg-indigo-600 hover:bg-indigo-700 text-white font-bold py-2 px-4 rounded-lg shadow-md transition disabled:opacity-50 disabled:cursor-not-allowed"
        >
          確認分揀完成
        </button>
      </div>
    </div>
  </div>
</template>
