<?php

namespace App\Http\Controllers;

use App\Events\PackingCompleted;
use App\Events\ShippingStarted; // 引入新增的事件
use App\Models\Order;
use App\Models\PackingTask;
use Illuminate\Http\Request;

class PackingController extends Controller
{
    // ... (假設其他方法存在：index, startTask)

    /**
     * PUT /api/wms/packing/tasks/{packingTask}/complete
     * 完成包裝任務。
     */
    public function completeTask(PackingTask $packingTask)
    {
        // 假設 PackingTask::STATUS_COMPLETED 存在
        $packingTask->update(['status' => 'Completed']);

        $order = $packingTask->order;

        // 檢查訂單所有包裝任務是否都完成 (此處簡化為只檢查當前任務)
        // 實際 WMS 可能有多個包裝任務，需複雜檢查。此處直接檢查並更新訂單狀態。
        $order->update(['status' => 'Packed']);

        // 觸發包裝完成事件 (用於庫存扣減)
        PackingCompleted::dispatch($order);

        // 觸發出貨流程開始事件，初始化 shipments 記錄 (新增邏輯)
        ShippingStarted::dispatch($order);

        return response()->json(['message' => '包裝任務已完成，訂單已進入 Packed (待出貨) 狀態']);
    }

    // 為了讓腳本完整，提供一個簡化的 index 方法
    public function index()
    {
        $tasks = PackingTask::where('status', '!=', 'Completed')->with('order')->get();
        return response()->json(['tasks' => $tasks]);
    }

    public function startTask(PackingTask $packingTask)
    {
        $packingTask->update(['status' => 'Packing']);
        $packingTask->order->update(['status' => 'Packing']);
        return response()->json(['message' => '包裝任務已開始', 'task' => $packingTask]);
    }
}
