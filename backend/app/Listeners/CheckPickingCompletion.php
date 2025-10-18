<?php
namespace App\Listeners;
use App\Models\Order;
use App\Models\SortingTask;
use App\Events\PickingCompleted;
use Illuminate\Contracts\Queue\ShouldQueue;
use App\Events\SortingCompleted; // 錯誤：應為 SortingStarted

class CheckPickingCompletion implements ShouldQueue
{
    public function handle($event): void
    {
        // 這裡將處理所有 PickingTask 狀態變動
        $order = Order::find($event->order->id); 
        
        // 檢查訂單是否還有 Pending 的 Picking Tasks
        $pendingTasksCount = $order->pickingTasks()->where('status', 'Pending')->count();

        if ($pendingTasksCount === 0 && $order->status === 'Picking') {
            
            // 訂單所有揀貨任務完成，進入下一階段：分揀 (Sorted)
            $order->status = 'Sorted';
            $order->save();

            // 創建分揀任務 (Sorting Tasks)
            $demoItems = $order->pickingTasks()->get();
            foreach ($demoItems as $item) {
                 SortingTask::create([
                    'order_id' => $order->id,
                    'sku' => $item->sku,
                    'quantity' => $item->quantity,
                    'sorting_bin' => 'BIN-' . substr(md5($order->order_number), 0, 4), // 模擬分配分揀箱
                    'status' => 'Pending',
                ]);
            }
            // 觸發事件：揀貨完成，分揀開始
            event(new PickingCompleted($order));
        }
    }
}
