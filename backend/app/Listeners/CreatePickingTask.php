<?php
namespace App\Listeners;
use App\Events\OrderCreated;
use App\Jobs\ProcessPicking;
use App\Models\Inventory;
use App\Models\PickingTask;
use Illuminate\Contracts\Queue\ShouldQueue;

class CreatePickingTask implements ShouldQueue
{
    public function handle(OrderCreated $event): void
    {
        $order = $event->order;
        $demoItems = [
            ['sku' => 'SKU-001', 'quantity' => rand(1, 2)],
            ['sku' => 'SKU-002', 'quantity' => rand(1, 3)],
        ];

        // 設置訂單狀態為 Picking (表示已分配揀貨任務)
        $order->status = 'Picking';
        $order->save();

        foreach ($demoItems as $item) {
            $inventory = Inventory::where('sku', $item['sku'])->first();
            if ($inventory) {
                $task = PickingTask::create([
                    'order_id' => $order->id,
                    'sku' => $item['sku'],
                    'quantity' => $item['quantity'],
                    'location' => $inventory->location,
                    'status' => 'Pending',
                ]);
                // 模擬揀貨 Job 運行
                ProcessPicking::dispatch($task)->onQueue('wms_picking');
            }
        }
    }
}
