<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\PickingController;
use App\Http\Controllers\SortingController;
use App\Http\Controllers\PackingController;
use App\Http\Controllers\ShippingController; // 引入新增的控制器

/*
|--------------------------------------------------------------------------
| WMS API Routes
|--------------------------------------------------------------------------
*/

Route::prefix('wms')->group(function () {
    // 訂單 (Orders)
    Route::get('orders', [OrderController::class, 'index']);
    Route::post('orders', [OrderController::class, 'store']);
    Route::get('orders/{order}', [OrderController::class, 'show']);

    // 揀貨 (Picking)
    Route::get('picking/tasks', [PickingController::class, 'index']);
    Route::put('picking/tasks/{pickingTask}/complete', [PickingController::class, 'completeTask']);

    // 分揀 (Sorting)
    Route::get('sorting/tasks', [SortingController::class, 'index']);
    Route::put('sorting/tasks/{sortingTask}/sort', [SortingController::class, 'sortItem']);

    // 包裝 (Packing)
    Route::get('packing/tasks', [PackingController::class, 'index']);
    Route::put('packing/tasks/{packingTask}/start', [PackingController::class, 'startTask']);
    Route::put('packing/tasks/{packingTask}/complete', [PackingController::class, 'completeTask']);

    // 出貨 (Shipping) - 新增
    Route::get('shipping/shipments', [ShippingController::class, 'index']); // 獲取所有待出貨/已出貨的訂單
    Route::put('shipping/orders/{order}/ship', [ShippingController::class, 'ship']); // 執行出貨操作 (生成追蹤碼)
});
