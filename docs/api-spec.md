# API 規格文件 (API Specification)

## 基礎路徑

`http://localhost:8080/api/wms`

## 訂單管理 (OMS)

| 方法 | 路徑 | 說明 |
| :--- | :--- | :--- |
| `GET` | `/orders` | 獲取所有訂單及相關任務。 |
| `POST` | `/orders` | 創建新訂單並觸發 WMS 流程 (揀貨)。 |
| `GET` | `/orders/{id}` | 獲取單一訂單詳情。 |

## 揀貨流程 (Picking)

| 方法 | 路徑 | 說明 |
| :--- | :--- | :--- |
| `GET` | `/picking/tasks` | 獲取所有揀貨任務。 |
| `PUT` | `/picking/tasks/{taskId}/complete` | 手動完成一個揀貨任務，觸發 SortingStarted。 |

## 分揀流程 (Sorting)

| 方法 | 路徑 | 說明 |
| :--- | :--- | :--- |
| `GET` | `/sorting/tasks` | 獲取所有分揀任務。 |
| `PUT` | `/sorting/tasks/{taskId}/sort` | 模擬分揀一個商品，更新進度，完成後觸發 PackingStarted。 |

## 包裝流程 (Packing)

| 方法 | 路徑 | 說明 |
| :--- | :--- | :--- |
| `GET` | `/packing/tasks` | 獲取所有包裝任務。 |
| `PUT` | `/packing/tasks/{taskId}/start` | 開始包裝任務 (狀態: Packing)。 |
| `PUT` | `/packing/tasks/{taskId}/complete` | 完成包裝任務，觸發 PackingCompleted 和 **ShippingStarted**。 |

## 出貨流程 (Shipping) - **新增**

| 方法 | 路徑 | 說明 |
| :--- | :--- | :--- |
| `GET` | `/shipping/shipments` | 獲取所有狀態為 **Packed** 或 **Shipped** 的訂單及其貨件資訊。 |
| `PUT` | `/shipping/orders/{orderId}/ship` | 執行出貨操作，生成追蹤碼，將訂單狀態從 `Packed` 轉為 `Shipped`。 |
