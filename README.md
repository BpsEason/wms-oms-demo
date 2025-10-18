# 📦 WMS/OMS 系統示範專案 (V5: 全流程倉儲與訂單管理)

此專案是一個基於 Laravel + Vue3 的中小型電商倉庫管理系統 (Warehouse Management System, WMS) 與訂單管理系統 (Order Management System, OMS) 的骨架示範。專案涵蓋從訂單創建、庫存檢查、揀貨任務、分揀分配、包裝驗證，到出貨物流整合的全流程，旨在展示實務可擴展的系統架構。適合用於面試展示、原型開發或生產環境基礎。

專案透過 Bash 腳本一鍵生成骨架檔案，包括後端模型、控制器、事件、佇列任務、前端頁面，以及 Docker 部署配置。腳本模擬了 Composer 和 NPM 的專案初始化，但實際部署時需手動安裝依賴。

## 🛠️ 技術棧

- **後端**：Laravel 10 (PHP 8.2)，包含 Eloquent ORM、Events/Listeners、Queues (Redis + Horizon)、Sanctum (API 認證，預設未啟用)。
- **前端**：Vue 3 (Composition API + TypeScript)、Vue Router、Pinia (狀態管理)、Axios (API 呼叫)、Tailwind CSS (樣式)。
- **資料庫**：PostgreSQL 15 (資料表支援訂單、庫存、任務等)。
- **佇列與緩存**：Redis (非同步處理耗時任務，如揀貨模擬延遲)。
- **容器化**：Docker Compose (整合 PHP-FPM、Nginx、PostgreSQL、Redis)。
- **其他依賴**：GuzzleHttp (物流 API 呼叫)、barryvdh/laravel-dompdf (包裝單生成)。

## ✨ 技術亮點

此專案不僅實現基本 CRUD 操作，還融入多項工程最佳實務，強調可擴展性和效能：

1. **事件驅動架構 (Event-Driven Architecture)**：
   - 訂單創建 (`OrderCreated` 事件) 自動觸發庫存檢查與揀貨任務生成。
   - 揀貨完成 (`PickingCompleted` 事件) 連鎖觸發分揀、包裝流程。
   - 優點：解耦模組，提高系統彈性，符合微服務思維。

2. **非同步佇列處理 (Queue with Redis/Horizon)**：
   - 使用 Laravel Queue 處理耗時任務，如揀貨模擬延遲 (sleep 5 秒)、物流 API 呼叫。
   - Horizon 提供佇列監控面板 (http://localhost:8080/horizon)，方便追蹤任務狀態與 KPI (e.g., 處理時間、失敗率)。
   - 實務應用：避免 UI 阻塞，支援高併發訂單處理。

3. **業務邏輯完整性**：
   - 庫存檢查與扣減：創建訂單時自動驗證 SKU 庫存，防止超賣 (最佳實務：實時庫存同步)。
   - 全流程狀態流轉：New → Picking → Sorted → Packed → Shipped (支援取消/失敗狀態)。
   - 物流 API 整合：模擬第三方 API (e.g., Shippo/FedEx)，生成追蹤號碼與狀態更新。

4. **前後端分離與 API 設計**：
   - RESTful API (基路徑: /api/wms)，支援 GET/POST/PUT 操作，包含驗證與錯誤處理。
   - 前端使用 Axios 呼叫 API，支援即時刷新 (e.g., 任務列表自動更新狀態)。
   - CORS 配置 (Nginx) 確保前端 (5173 埠) 順利存取後端 (8080 埠)。

5. **容器化與一鍵部署**：
   - Docker Compose 整合多服務 (app, db, redis, nginx)，支援快速建置與環境隔離。
   - Seeder 自動插入測試數據 (庫存 SKU)，便於 Demo 演示。

6. **最佳實務融入**：
   - 倉庫優化：儲位建議 (location)、分揀箱號分配 (order_box)，減少倉庫旅行距離。
   - 錯誤最小化：包裝驗證使用 JSON 清單與 PDF 生成 (DomPDF)，模擬條碼掃描。
   - KPI 潛力：可擴展追蹤指標，如訂單處理時間、準確率 (未來整合監控工具)。

## 🚀 部署與啟動指南

### 前置條件
- 安裝 Docker 和 Docker Compose。
- 確保系統有 Bash 環境 (Linux/Mac/Windows WSL)。
- 安裝 Composer 和 Node.js/NPM (用於依賴安裝)。

### 步驟
1. **執行腳本生成專案骨架**：
   ```bash
   bash <腳本檔案名稱>.sh
   ```
   - 這會創建 `wms-oms-demo` 目錄，包含所有檔案。

2. **進入專案目錄並安裝依賴**：
   ```bash
   cd wms-oms-demo
   ```
   - **後端依賴** (進入 backend/)：
     ```bash
     cd backend
     composer install
     composer require laravel/horizon barryvdh/laravel-dompdf guzzlehttp/guzzle
     cd ..
     ```
   - **前端依賴** (進入 frontend/)：
     ```bash
     cd frontend
     npm install
     npm install -D tailwindcss postcss autoprefixer
     npx tailwindcss init -p
     cd ..
     ```

3. **配置環境**：
   - 複製 `.env.example` 為 `.env`，檢查並調整變數 (e.g., DB_PASSWORD)。
     ```bash
     cp .env.example backend/.env
     cp .env.example frontend/.env
     ```
   - 在 `backend/.env` 中設置：
     ```
     DB_CONNECTION=pgsql
     DB_HOST=db
     DB_PORT=5432
     DB_DATABASE=wms_db
     DB_USERNAME=user
     DB_PASSWORD=password
     QUEUE_CONNECTION=redis
     REDIS_HOST=redis
     REDIS_PORT=6379
     ```
   - 在 `frontend/.env` 中設置：
     ```
     VITE_API_BASE_URL=http://localhost:8080/api/wms
     ```

4. **啟動 Docker 環境**：
   ```bash
   docker compose up --build -d
   ```

5. **初始化資料庫與 Horizon**：
   - 進入 app 容器：
     ```bash
     docker exec -it wms-oms-app sh
     ```
   - 執行：
     ```bash
     php artisan key:generate
     php artisan migrate --force
     php artisan db:seed --force
     php artisan horizon:install
     php artisan horizon:publish
     mkdir -p storage/app/public/packing_slips
     chmod -R 775 storage
     exit
     ```

6. **啟動前端開發**：
   - 在宿主機執行（腳本假設前端未容器化）：
     ```bash
     cd frontend
     npm run dev
     ```

7. **訪問系統**：
   - 前端 UI：http://localhost:5173 (Vite dev 伺服器)
   - 後端 API：http://localhost:8080/api/wms
   - Horizon 監控：http://localhost:8080/horizon

## 💡 Demo 演示步驟

1. **檢查庫存**：訪問 http://localhost:5173/inventory，確認測試數據 (e.g., SKU-001 庫存 500)。
2. **創建訂單**：在訂單頁面輸入客戶名稱、商品清單 (JSON 格式，如 `[{"sku": "SKU-001", "qty": 10}]`)，提交後觀察事件觸發。
3. **揀貨任務**：切換到揀貨頁面，查看任務列表，等待佇列自動完成 (模擬 5 秒延遲) 或手動完成。
4. **分揀分配**：在分揀頁面，確認商品分配到訂單箱 (order_box)，點擊完成。
5. **包裝驗證**：在包裝頁面，驗證商品清單，生成並下載 PDF 包裝單。
6. **出貨整合**：在出貨頁面，點擊生成物流單號 (模擬 Shippo/FedEx API)，確認狀態為 Shipped。
7. **監控佇列**：訪問 http://localhost:8080/horizon，查看任務執行日誌與效能指標。

此流程模擬真實電商場景：從訂單進入到物流出貨，狀態自動流轉，展示事件驅動與佇列的效率。

## 📄 文檔與擴展

- **系統架構**：`docs/architecture.md` (ERD 圖、訂單流程圖)。
- **API 規格**：`docs/api-spec.md` (所有端點、請求/回應範例)。
- **擴展建議**：
  - 真實物流 API：整合 Shippo 或 FedEx SDK，實現真實追蹤。
  - 行動端支援：開發 Vue Native 或 PWA，模擬 PDA 揀貨/包裝。
  - 效能優化：引入 Elasticsearch (庫存搜尋)、Celery-like 任務調度。
  - 安全性：啟用 Sanctum 認證，整合 Spatie Permission 角色權限。
  - KPI 監控：新增 Prometheus/Grafana，追蹤訂單處理時間、錯誤率。

## 🛠️ 常見問題與排錯

- **API 跨域問題**：確認 Nginx CORS 配置正確，前端 VITE_API_BASE_URL 匹配後端 URL。
- **佇列未執行**：檢查 Redis 連線 (docker logs wms-oms-redis) 和 Horizon 日誌。
- **PDF 生成失敗**：確保已安裝 `barryvdh/laravel-dompdf`，並檢查 `storage/app/public/packing_slips` 權限。
- **資料庫連線錯誤**：確認 `.env` 中的 DB_HOST=db，檢查 PostgreSQL 容器狀態 (docker logs wms-oms-db)。
- **前端樣式問題**：確認 Tailwind CSS 已初始化 (`npx tailwindcss init -p`)，並檢查 `tailwind.config.js`。

如有問題，請在 GitHub Issue 回饋。專案開源於 [GitHub Repo](https://github.com/your-repo)（請替換為實際連結）。