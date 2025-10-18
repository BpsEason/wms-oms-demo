<?php
namespace App\Listeners;
use App\Events\SortingCompleted; // 接收 SortingCompleted (已分揀)
use App\Models\Order;
use Illuminate\Contracts\Queue\ShouldQueue;
use App\Events\PackingCompleted; // 觸發 Packing Completed

class CheckPackingCompletion implements ShouldQueue
{
    public function handle(SortingCompleted $event): void
    {
        $order = $event->order;
        
        // 檢查訂單是否還有未完成的包裝任務
        $pendingTasksCount = $order->packingTasks()->where('status', 'Pending')->count();

        if ($pendingTasksCount === 0 && $order->status === 'Packed') {
            
            // 訂單所有包裝任務完成，進入下一階段：待出貨 (Packed - 待物流)
            // 狀態不變，但準備呼叫物流 API
            
            // (實務上) 此處應呼叫物流預約 API，然後等待物流商回調 (ShippingController)
            
            // 觸發事件：包裝完成
            event(new PackingCompleted($order));
        }
    }
}
