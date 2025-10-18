<script setup lang="ts">
import { RouterView, RouterLink } from 'vue-router';
</script>

<template>
  <div class="min-h-screen bg-gray-50">
    <header class="bg-white shadow-md sticky top-0 z-10">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between items-center h-16">
          <div class="flex-shrink-0">
            <RouterLink to="/" class="text-2xl font-extrabold text-indigo-600">
              WMS/OMS Demo
            </RouterLink>
          </div>
          <nav class="flex space-x-4">
            <RouterLink to="/" class="text-gray-600 hover:text-indigo-600 px-3 py-2 rounded-md text-sm font-medium transition duration-150" active-class="bg-indigo-50 text-indigo-700">
              OMS 訂單
            </RouterLink>
            <RouterLink to="/picking" class="text-gray-600 hover:text-indigo-600 px-3 py-2 rounded-md text-sm font-medium transition duration-150" active-class="bg-indigo-50 text-indigo-700">
              揀貨 (Picking)
            </RouterLink>
            <RouterLink to="/sorting" class="text-gray-600 hover:text-indigo-600 px-3 py-2 rounded-md text-sm font-medium transition duration-150" active-class="bg-indigo-50 text-indigo-700">
              分揀 (Sorting)
            </RouterLink>
            <RouterLink to="/packing" class="text-gray-600 hover:text-indigo-600 px-3 py-2 rounded-md text-sm font-medium transition duration-150" active-class="bg-indigo-50 text-indigo-700">
              包裝 (Packing)
            </RouterLink>
            <RouterLink to="/shipping" class="text-gray-600 hover:text-indigo-600 px-3 py-2 rounded-md text-sm font-medium transition duration-150" active-class="bg-indigo-50 text-indigo-700">
              出貨 (Shipping)
            </RouterLink>
          </nav>
        </div>
      </div>
    </header>

    <main>
      <div class="max-w-7xl mx-auto py-6 sm:px-6 lg:px-8">
        <RouterView />
      </div>
    </main>
  </div>
</template>

<style>
/* 基礎樣式和 Tailwind 導入 (假設已配置) */
@import './assets/base.css';

/* 確保 RouterLink 在活躍時有明顯標記 */
.router-link-active {
  font-weight: bold;
}
</style>
