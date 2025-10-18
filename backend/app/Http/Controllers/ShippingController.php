<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\Shipment;
use Illuminate\Http\Request;

class ShippingController extends Controller
{
    /**
     * GET /api/wms/shipping/shipments
     * 獲取所有處於 Packed 或 Shipped 狀態的訂單及其貨件資訊。
     */
    public function index()
    {
        // 假設 Order Model 中定義了 'Packed' 和 'Shipped'
        $orders = Order::whereIn('status', ['Packed', 'Shipped'])
                       ->with('shipment') // 預載入貨件資訊
                       ->orderBy('id', 'desc')
                       ->get();

        return response()->json(['orders' => $orders]);
    }

    /**
     * PUT /api/wms/shipping/orders/{order}/ship
     * 模擬出貨操作：從 Packed 轉為 Shipped，生成追蹤碼。
     */
    public function ship(Order $order)
    {
        // 確保訂單狀態是 Packed，表示已完成包裝
        if ($order->status !== 'Packed') {
            return response()->json(['message' => '訂單狀態必須為 Packed 才能執行出貨操作。', 'status' => $order->status], 400);
        }

        // 查找 Shipment 記錄
        $shipment = Shipment::firstOrCreate(
            ['order_id' => $order->id],
            ['packed_at' => now()]
        );

        // 模擬生成追蹤碼
        $trackingNumber = 'TN' . date('YmdHis') . $order->id;

        // 更新 Shipment 狀態
        $shipment->update([
            'tracking_number' => $trackingNumber,
            'carrier' => 'TaiwanPost',
            'status' => 'InTransit', // 實際出貨後狀態變為 InTransit (運輸中)
            'shipped_at' => now(),
            'tracking_log' => array_merge(
                $shipment->tracking_log ?? [],
                [['time' => now()->toDateTimeString(), 'event' => '包裹已交由物流商，開始運輸']]
            )
        ]);

        // 更新 Order 狀態到最終的 Shipped
        $order->update(['status' => 'Shipped']);

        return response()->json([
            'message' => "訂單 {$order->order_number} 已成功出貨。追蹤碼：{$trackingNumber}",
            'order' => $order->load('shipment')
        ]);
    }
}
