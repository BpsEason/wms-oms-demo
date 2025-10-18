# 系統架構與核心流程 (Architecture & Core Process Flow)

## 1. 系統架構總覽 (System Architecture Overview)

本系統採用微服務概念的**事件驅動架構** (Event-Driven Architecture, EDA)，劃分為前端 (Vue3) 和後端 (Laravel)。

| 組件 | 技術/框架 | 職責 |
| :--- | :--- | :--- |
| **前端 (OMS/WMS UI)** | Vue 3 + Router + Pinia + Tailwind CSS | 提供訂單管理與 WMS 各流程的操作介面。 |
| **後端 (WMS/OMS Core)** | Laravel + Events + Queues | 提供 RESTful API、處理業務邏輯、流程解耦與異步處理。 |
| **異步處理** | Laravel Events + Listeners + Queues (Redis/Horizon) | 確保流程解耦、非同步處理耗時任務。 |

## 2. 核心流程：訂單到出貨 (完整流程)

整個流程由訂單創建開始，通過事件和異步 Job 驅動，確保各步驟的解耦和高併發能力。

| 流程階段 | 觸發事件 | 監聽器/任務 | 作用 | 訂單狀態變更為 |
| :--- | :--- | :--- | :--- | :--- |
| 訂單創建 | `OrderCreated` | `CreatePickingTask` | 創建揀貨任務。 | New -> Picking |
| 揀貨完成 | `PickingCompleted` | N/A | 訂單揀貨完成。 | Picking -> Picking Completed |
| 分揀開始 | `SortingStarted` | `CreateSortingTask` | 觸發分揀流程（由 PickingController 觸發）。 | Picking Completed -> Sorting |
| 分揀完成 | `SortingCompleted` | `CreatePackingTask` | 訂單分揀完成。 | Sorting -> ReadyForPacking |
| 包裝開始 | `PackingStarted` | N/A (API Call) | 操作員開始包裝。 | ReadyForPacking -> Packing |
| 包裝完成 | `PackingCompleted` | `UpdateInventory` | 實際扣減庫存。 | Packing -> Packed |
| **出貨開始** | `ShippingStarted` | `CreateShipment` | **(新增)** 初始化 `shipments` 記錄。 | Packed (不變) |
| **出貨操作** | (API Call) | `ShippingController@ship` | **(新增)** 生成追蹤碼，訂單交給物流。 | Packed -> Shipped |

## 3. 流程圖 (Process Flow)

```mermaid
graph TD
    subgraph Frontend (WMS UI)
        A[OrdersPage: 創建新訂單]
        B[PickingPage: 完成揀貨任務]
        C[SortingPage: 完成分揀]
        D[PackingPage: 完成包裝]
        E[ShippingPage: 確認出貨]
    end

    subgraph Backend (Laravel)
        F(OrderController: Create Order)
        G(Event: OrderCreated)
        H(Listener: CreatePickingTask)
        I(Job: ProcessPicking)
        J{Picking Done? PickingController}
        K(Event: SortingStarted)
        L(Listener: CreateSortingTask)
        M{Sorting Done? SortingController}
        N(Event: PackingStarted)
        O(Listener: CreatePackingTask)
        P{Packing Done? PackingController}
        Q(Event: PackingCompleted)
        R(Listener: UpdateInventory)
        S(Event: ShippingStarted)
        T(Listener: CreateShipment)
        U(ShippingController: Ship Order)
    end

    A -- POST /orders --> F
    F --> |Dispatch| G
    G --> H
    H --> |Dispatch| I
    I --> B -- PUT /picking/tasks/id/complete --> J
    J -- Yes & Status Update --> K
    K --> L
    L --> C -- PUT /sorting/tasks/id/sort --> M
    M -- Yes --> N
    N --> O
    O --> D -- PUT /packing/tasks/id/complete --> P
    P -- Yes --> Q
    P -- Yes --> S
    Q --> R
    S --> T
    T --> E -- PUT /shipping/orders/id/ship --> U
    U --> E[Order Status: Shipped]

    style I fill:#f9f,stroke:#333
    style R fill:#ccf,stroke:#333
    style U fill:#d8f,stroke:#333
    style S fill:#aaffdd,stroke:#333
    style T fill:#aaffdd,stroke:#333
