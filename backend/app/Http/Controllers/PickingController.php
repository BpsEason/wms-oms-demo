<?php

namespace App\Http\Controllers;

use App\Events\PickingCompleted;
use App\Events\SortingStarted;
use App\Models\Order;
use App\Models\PickingTask;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PickingController extends Controller
{
    // ... (其他方法，此處僅提供修正後的 checkPickingCompletion)

    /**
     * 檢查揀貨任務是否全部完成。
     */
    protected function checkPickingCompletion(Order $order): void
    {
        // 假設 PickingTask::STATUS_COMPLETED 存在
        $pendingTasks = PickingTask::where('order_id', $order->id)
                                    ->where('status', '!=', 'Completed')
                                    ->count();

        if ($pendingTasks === 0) {
            // 假設 Order::STATUS_PICKING_COMPLETED 存在
            $order->update(['status' => 'Picking Completed']);
            PickingCompleted::dispatch($order);

            // 觸發分揀流程開始事件
            // use App\Events\SortingStarted; // 註解已修正為正確的 SortingStarted 
            SortingStarted::dispatch($order);
        }
    }
}
