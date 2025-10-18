<?php
namespace App\Listeners;
use App\Events\PickingCompleted; // 接收 PickingCompleted (已揀貨)
use App\Models\Order;
use App\Models\PackingTask;
use Illuminate\Contracts\Queue\ShouldQueue;
use App\Events\SortingCompleted; // 觸發 Sorting Completed

class CheckSortingCompletion implements ShouldQueue
{
    public function handle(PickingCompleted $event): void
    {
        $order = $event->order;
        
        // 檢查訂單是否還有未完成的分揀任務
        $pendingTasksCount = $order->sortingTasks()->where('status', '!=', 'Completed')->count();

        if ($pendingTasksCount === 0 && $order->status === 'Sorted') {
            
            // 訂單所有分揀任務完成，進入下一階段：包裝 (Packed)
            $order->status = 'Packed';
            $order->save();

            // 創建包裝任務 (Packing Tasks)
            $demoItems = $order->sortingTasks()->get();
            foreach ($demoItems as $item) {
                 PackingTask::create([
                    'order_id' => $order->id,
                    'sku' => $item->sku,
                    'quantity' => $item->quantity,
                    'package_type' => 'Box A', 
                    'status' => 'Pending',
                ]);
            }
            // 觸發事件：分揀完成，包裝開始
            event(new SortingCompleted($order));
        }
    }
}
