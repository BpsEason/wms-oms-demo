# 📦 WMS/OMS 系統示範專案 (V5: 全流程倉儲與訂單管理)

此專案是一個基於 Laravel 10 和 Vue 3 的中小型電商倉庫管理系統 (Warehouse Management System, WMS) 與訂單管理系統 (Order Management System, OMS) 原型，涵蓋從訂單創建、庫存檢查、揀貨任務、分揀分配、包裝驗證到出貨物流的全流程。  
**專案目標**：展示一個事件驅動、可擴展、貼近實務的倉儲系統架構，適合作為面試展示、原型驗證或中小型電商的生產環境基礎。

專案結構包含完整的後端 API、前端 UI、Docker 配置與測試數據，透過 GitHub 提供關鍵代碼，支援快速部署與演示，融入倉儲演算法與工程最佳實務，超越傳統 CRUD 應用。

## 🛠️ 技術棧

- **後端**：Laravel 10 (PHP 8.2)、Eloquent ORM、Events/Listeners、Queues (Redis + Horizon)、Sanctum (API 認證，預設未啟用)。
- **前端**：Vue 3 (Composition API + TypeScript)、Vue Router、Pinia (狀態管理)、Axios (API 呼叫)、Tailwind CSS (樣式)。
- **資料庫**：PostgreSQL 15 (支援訂單、庫存、任務等資料表)。
- **佇列與緩存**：Redis 7 (非同步任務處理，如揀貨延遲)。
- **容器化**：Docker Compose (整合 PHP-FPM、Nginx、PostgreSQL、Redis)。
- **其他依賴**：GuzzleHttp (物流 API 模擬)、barryvdh/laravel-dompdf (包裝單生成)。

## ✨ 技術亮點

專案不僅實現功能需求，還融入工程思維，強調實務場景的可靠性和效率：

1. **事件驅動架構 (Event-Driven Architecture)**：
   - 訂單創建 (`OrderCreated`) 觸發庫存檢查與揀貨任務生成，揀貨完成 (`PickingCompleted`) 連鎖啟動分揀、包裝、出貨流程。
   - **實務價值**：降低模組耦合，方便未來拆分為微服務，提升系統靈活性。

2. **非同步佇列處理 (Redis + Horizon)**：
   - 使用 Laravel Queue 處理耗時任務（如揀貨模擬 5 秒延遲、物流 API 呼叫），避免 UI 阻塞。
   - Horizon 提供佇列監控面板 (http://localhost:8080/horizon)，追蹤任務 KPI（如執行時間、失敗率）。
   - **實務價值**：支援高併發訂單處理，確保高峰期（如雙 11）系統穩定。

3. **業務邏輯完整性**：
   - **庫存管理**：訂單創建時實時驗證 SKU 庫存，支援扣減與回滾，防止超賣。
   - **狀態流轉**：New → Picking → Sorted → Packed → Shipped，支援取消/失敗處理。
   - **物流整合**：模擬第三方 API（如 Shippo/FedEx），生成追蹤號碼並更新狀態。
   - **實務價值**：確保庫存與訂單數據一致，減少倉庫錯誤，提升客戶滿意度。

4. **前後端分離與 API 設計**：
   - RESTful API (基路徑: `/api/wms`)，支援 GET/POST/PUT，包含資料驗證與錯誤處理。
   - 前端透過 Axios 實現即時狀態更新（如任務列表動態刷新）。
   - Nginx 配置 CORS，確保前端 (5173 埠) 與後端 (8080 埠) 無縫交互。
   - **實務價值**：分離架構便於獨立開發與維護，支援多平台（如 PDA、行動端）。

5. **容器化部署**：
   - Docker Compose 整合 PHP-FPM、Nginx、PostgreSQL、Redis，實現環境隔離與一鍵啟動。
   - Seeder 提供測試數據（庫存 SKU、訂單範例），加速演示。
   - **實務價值**：簡化環境配置，確保開發與生產一致，降低部署風險。

## 🔍 演算法與流程優化亮點

專案融入實務倉儲邏輯，透過演算法與策略優化倉庫效率，展現工程深度，解決真實電商痛點：

1. **分批揀貨演算法 (Batch Picking Algorithm)**：
   - **邏輯**：根據儲位 (`location`) 將揀貨任務 (`picking_tasks`) 分組，優先合併同一區域的 SKU，減少揀貨員移動距離。與傳統逐單揀貨相比，可減少 50%–90% 的倉庫行走時間。
   - **人力限制**：每批次限制 50 件商品（可配置），超量自動拆分，分配至固定揀貨員（預設 2 人），實現負載均衡。
   - **優化方法**：採用貪婪演算法排序儲位（由近至遠），未來可升級為旅行推銷員問題 (TSP) 或 A* 路徑搜尋。
   - **實務價值**：透過 Redis 佇列異步生成批次任務，支援高併發訂單，減少倉庫瓶頸，適用於中大型電商倉庫。

2. **分揀策略優化 (Sorting Strategy)**：
   - **邏輯**：模擬 Pick-to-Light 系統，將揀貨完成的商品按訂單箱號 (`sorting_bin`) 分配，逐項更新 `sorted_quantity`，完成後觸發包裝任務。模擬真實電商倉庫分揀站台，支援急單優先處理。
   - **防錯設計**：使用 JSON 清單驗證分揀數量，確保準確性；支援波次分揀 (Wave Sorting)，適用於批量訂單。
   - **實務價值**：減少分揀錯誤，提升出貨效率，貼近電商倉庫作業流程。

3. **整體流程最佳化**：
   - **狀態機設計**：使用有限狀態機 (Finite State Machine) 管理訂單狀態流轉，確保事件連鎖邏輯一致（如 Packed → Shipped 自動呼叫物流 API）。支援退貨、異常處理等擴展場景。
   - **效能優化**：資料表索引（`sku`, `order_number`）加速查詢；Redis 佇列分擔高併發壓力；可擴展緩存（Redis/Memcached）提升庫存查詢速度。
   - **擴展潛力**：儲位推薦可整合機器學習（如 K-Means 聚類分析倉庫佈局）；KPI 追蹤（如揀貨時間、錯誤率）支援 Prometheus/Grafana 監控。
   - **實務價值**：最小化倉庫觸點，提升揀貨與分揀效率，未來可無縫接軌 ERP 或第三方物流系統。

## 🚀 部署與啟動指南

### 前置條件
- 安裝 [Docker](https://docs.docker.com/get-docker/) 和 [Docker Compose](https://docs.docker.com/compose/install/)。
- 安裝 [Composer](https://getcomposer.org/) 和 [Node.js/NPM](https://nodejs.org/)（用於依賴安裝）。
- 系統支援 Bash 環境（Linux/Mac/Windows WSL）。

### 步驟
1. **克隆 GitHub 倉庫**：
   ```bash
   git clone https://github.com/BpsEason/wms-oms-demo.git
   cd wms-oms-demo
   ```

2. **安裝依賴**：
   - **後端**（進入 `backend/`）：
     ```bash
     cd backend
     composer install
     composer require laravel/horizon barryvdh/laravel-dompdf guzzlehttp/guzzle
     cd ..
     ```
   - **前端**（進入 `frontend/`）：
     ```bash
     cd frontend
     npm install
     npm install -D tailwindcss postcss autoprefixer
     npx tailwindcss init -p
     cd ..
     ```

3. **配置環境**：
   - 複製並調整 `.env` 檔案：
     ```bash
     cp backend/.env.example backend/.env
     cp frontend/.env.example frontend/.env
     ```
   - 編輯 `backend/.env`：
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
   - 編輯 `frontend/.env`：
     ```
     VITE_API_BASE_URL=http://localhost:8080/api/wms
     ```

4. **啟動 Docker 環境**：
   ```bash
   docker compose up --build -d
   ```

5. **初始化資料庫與 Horizon**：
   - 進入 `app` 容器：
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

6. **啟動前端開發伺服器**：
   ```bash
   cd frontend
   npm run dev
   ```

7. **訪問系統**：
   - 前端 UI：http://localhost:5173 (Vite 開發伺服器)
   - 後端 API：http://localhost:8080/api/wms
   - Horizon 監控：http://localhost:8080/horizon

## 💡 Demo 演示步驟

以下步驟展示全流程運作，特別強調演算法與流程優化的效果：

1. **檢查庫存**：
   - 訪問 http://localhost:5173/inventory，確認測試數據（如 SKU-001 庫存 500）。
   - **觀察重點**：庫存表顯示 SKU、數量與儲位，支援快速查詢與防超賣驗證。

2. **創建訂單**：
   - 在訂單頁面輸入客戶名稱、商品清單（JSON 格式，如 `[{"sku": "SKU-001", "qty": 10}]`），提交訂單。
   - **觀察重點**：事件驅動流程啟動，自動生成揀貨任務並觸發 `OrderCreated` 事件。

3. **揀貨任務**：
   - 切換到揀貨頁面，查看分批任務列表，等待佇列自動完成（模擬 5 秒延遲）或手動點擊完成。
   - **觀察重點**：分批揀貨演算法如何合併 SKU，按儲位排序，減少重複走動。

4. **分揀分配**：
   - 在分揀頁面，確認商品分配至訂單箱 (`sorting_bin`)，點擊完成。
   - **觀察重點**：模擬 Pick-to-Light 分揀站台，觀察商品分配與進度更新，支援急單優先。

5. **包裝驗證**：
   - 在包裝頁面，驗證商品清單，生成並下載 PDF 包裝單（DomPDF）。
   - **觀察重點**：JSON 清單驗證與 PDF 生成，模擬條碼掃描確保準確性。

6. **出貨整合**：
   - 在出貨頁面，點擊生成物流單號（模擬 Shippo/FedEx API），確認訂單狀態為 Shipped。
   - **觀察重點**：物流單號生成與狀態更新，模擬真實 API 整合流程。

7. **監控佇列**：
   - 訪問 http://localhost:8080/horizon，檢查任務執行日誌與效能指標（如揀貨時間）。
   - **觀察重點**：Horizon 面板展示佇列任務執行情況，驗證非同步處理效率。

此流程模擬真實電商倉庫運作，展示分批揀貨、分揀策略與事件驅動的效率。

## 📄 文檔與擴展

- **系統架構**：`docs/architecture.md`（ERD 圖、流程圖）。
- **API 規格**：`docs/api-spec.md`（端點詳情、請求/回應範例）。
- **擴展建議**：
   - **物流整合**：使用 Shippo 或 FedEx SDK，實現真實追蹤號碼生成。
   - **行動端支援**：開發 Vue Native 或 PWA，模擬 PDA 揀貨/包裝。
   - **效能優化**：整合 Elasticsearch（庫存搜尋）、Celery-like 任務調度。
   - **安全性**：啟用 Sanctum 認證，新增 Spatie Permission 角色權限。
   - **KPI 監控**：引入 Prometheus/Grafana，追蹤訂單處理時間、揀貨準確率。

## 🌟 為什麼這個專案值得看

本專案不僅展示了 Laravel + Vue 的全棧開發能力，更融入了倉儲演算法（分批揀貨、分揀策略）、事件驅動架構與非同步佇列，展現了工程思維與實務價值。從減少倉庫行走距離到支援高併發訂單處理，專案解決了真實電商痛點，並提供可擴展的架構（支援 ERP 整合、機器學習優化）。無論是面試展示、技術分享，或作為中小型電商倉庫的原型系統，都能快速上手並客製化。

## 🛠️ 常見問題與排錯

- **API 跨域問題**：檢查 Nginx CORS 配置，確保 `frontend/.env` 的 `VITE_API_BASE_URL` 正確。
- **佇列未執行**：檢查 Redis 容器狀態（`docker logs wms-oms-redis`）與 Horizon 日誌。
- **PDF 生成失敗**：確認 `barryvdh/laravel-dompdf` 已安裝，檢查 `storage/app/public/packing_slips` 權限（775）。
- **資料庫連線錯誤**：驗證 `backend/.env` 的 `DB_HOST=db`，檢查 PostgreSQL 容器（`docker logs wms-oms-db`）。
- **前端樣式問題**：確認 Tailwind CSS 初始化（`npx tailwindcss init -p`），檢查 `tailwind.config.js`。

如有問題，請在 [GitHub Issues](https://github.com/your-repo/wms-oms-demo/issues) 回饋。專案開源於 [GitHub Repo](https://github.com/your-repo/wms-oms-demo)（請替換為實際連結）。
