/**
 * WMS API Service
 * 負責所有與後端 /api/wms 路由的互動
 */

const API_BASE_URL = '/api/wms';

interface ApiResponse<T> {
  data?: T;
  message?: string;
  order?: T; // Laravel 單個資源回傳 (如 ship API)
  orders?: T[]; // Laravel 列表回傳 (如 getShipments)
  tasks?: T[]; // 任務列表回傳
}

// 基礎 Fetch 函數
async function apiFetch<T>(endpoint: string, options: RequestInit = {}): Promise<T> {
  const url = `${API_BASE_URL}${endpoint}`;
  const defaultHeaders = {
    'Content-Type': 'application/json',
    'Accept': 'application/json',
  };

  if (options.body && typeof options.body !== 'string') {
    options.body = JSON.stringify(options.body);
  }

  const response = await fetch(url, {
    ...options,
    headers: {
      ...defaultHeaders,
      ...options.headers,
    },
  });

  const result: ApiResponse<T> = await response.json();

  if (!response.ok) {
    throw new Error(result.message || `API 請求失敗: ${response.status} ${response.statusText}`);
  }

  // 根據後端回傳結構，嘗試提取主要的數據部分
  return (result.data || result.order || result.orders || result.tasks || result) as T;
}

// === 訂單管理 (Orders) ===

export const orderApi = {
  // 獲取所有訂單
  getOrders: () => apiFetch<any[]>('/orders'),

  // 創建新訂單 (模擬)
  createOrder: (orderData: { customer_name: string, items: string }) => apiFetch<any>('/orders', {
    method: 'POST',
    body: orderData
  }),
};

// === 任務操作 (Tasks) ===

export const taskApi = {
  // 揀貨：完成任務
  completePicking: (taskId: number) => apiFetch<any>(`/picking/tasks/${taskId}/complete`, { method: 'PUT' }),

  // 分揀：掃描商品
  sortItem: (taskId: number) => apiFetch<any>(`/sorting/tasks/${taskId}/sort`, { method: 'PUT' }),

  // 包裝：開始任務
  startPacking: (taskId: number) => apiFetch<any>(`/packing/tasks/${taskId}/start`, { method: 'PUT' }),

  // 包裝：完成任務
  completePacking: (taskId: number) => apiFetch<any>(`/packing/tasks/${taskId}/complete`, { method: 'PUT' }),

  // 出貨：執行出貨 (將 Packed 轉為 Shipped)
  shipOrder: (orderId: number) => apiFetch<any>(`/shipping/orders/${orderId}/ship`, {
    method: 'PUT'
  }),
};

// === 流程頁面數據獲取 ===

export const flowApi = {
  // 獲取揀貨任務
  getPickingTasks: () => apiFetch<any[]>('/picking/tasks'),

  // 獲取分揀任務
  getSortingTasks: () => apiFetch<any[]>('/sorting/tasks'),

  // 獲取包裝任務
  getPackingTasks: () => apiFetch<any[]>('/packing/tasks'),

  // 獲取所有貨件 (待出貨/已出貨)，後端回傳 orders 列表，裡面有 shipment 資訊
  getShipments: () => apiFetch<any[]>('/shipping/shipments'),
};
