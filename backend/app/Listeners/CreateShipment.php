<?php

namespace App\Listeners;

use App\Events\ShippingStarted;
use App\Models\Order;
use App\Models\Shipment;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Log;

class CreateShipment implements ShouldQueue
{
    use InteractsWithQueue;

    /**
     * 處理事件。
     */
    public function handle(ShippingStarted $event): void
    {
        $order = $event->order;

        // 確保在 Packed 狀態才創建 Shipment 記錄
        if ($order->status !== 'Packed') {
            Log::warning("Order {$order->order_number} is not in Packed status. Skipping shipment creation.");
            return;
        }

        // 查找或創建 Shipment 記錄
        $shipment = Shipment::firstOrCreate(
            ['order_id' => $order->id],
            [
                'carrier' => 'DefaultCarrier',
                'status' => 'Pending',     // 待出貨狀態
                'packed_at' => now(),
                'tracking_log' => json_encode([
                    ['time' => now()->toDateTimeString(), 'event' => '包裹包裝完成，等待出貨']
                ]),
            ]
        );

        Log::info("Order {$order->order_number} is ready for shipping. Shipment record created: {$shipment->id}");
    }
}
