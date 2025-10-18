# 實體關係圖 (Entity Relationship Diagram - ERD)

## 核心資料表 (Core Tables)

### 1. orders (訂單主表)
* `id` (PK)
* `order_number` (string, Unique)
* `customer_name` (string)
* `status` (string, New | Picking | Sorting | Packing | Packed | Shipped)
* `total_items` (integer)
* `created_at`, `updated_at`

### 2. inventory (庫存表)
* `id` (PK)
* `sku` (string, Unique)
* `product_name` (string)
* `quantity` (integer)
* `location` (string)

### 3. picking_tasks (揀貨任務)
* `id` (PK)
* `order_id` (FK to orders, CASCADE)
* `sku` (string)
* `quantity` (integer)
* `status` (string, New | Completed)
* `location` (string, 建議揀貨位置)
* `created_at`, `updated_at`

### 4. sorting_tasks (分揀任務)
* `id` (PK)
* `order_id` (FK to orders, CASCADE)
* `status` (string, Pending | Completed)
* `created_at`, `updated_at`

### 5. packing_tasks (包裝任務)
* `id` (PK)
* `order_id` (FK to orders, CASCADE)
* `status` (string, ReadyForPacking | Packing | Completed)
* `created_at`, `updated_at`

### 6. shipments (出貨/物流追蹤) - **新增**
* `id` (PK)
* `order_id` (FK to orders, **Unique**, CASCADE)
* `tracking_number` (string, Unique, 物流追蹤碼)
* `carrier` (string, 物流公司)
* `status` (string, Pending | InTransit | Shipped | Delivered)
* `packed_at` (timestamp)
* `shipped_at` (timestamp)
* `tracking_log` (JSON, 物流追蹤記錄)
* `created_at`, `updated_at`

## 關係摘要

* `orders` 1 : N `picking_tasks` (一對多)
* `orders` 1 : 1 `sorting_tasks` (一對一)
* `orders` 1 : 1 `packing_tasks` (一對一)
* `orders` 1 : 1 `shipments` (一對一)
