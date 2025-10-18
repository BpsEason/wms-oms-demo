<?php

namespace App\Http\Controllers;

use App\Events\OrderCreated;
use App\Models\Order;
use App\Models\Inventory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class OrderController extends Controller
{
    public function index()
    {
        $orders = Order::orderBy('id', 'desc')->get();
        return response()->json(['orders' => $orders]);
    }

    public function show(int $orderId)
    {
        $order = Order::with(['pickingTasks', 'sortingTasks', 'packingTasks'])->find($orderId);

        if (!$order) {
            return response()->json(['message' => '訂單不存在'], 404);
        }

        return response()->json(['order' => $order]);
    }

    // 創建新訂單並觸發 WMS 流程
    public function store(Request $request)
    {
        $request->validate([
            'customer_name' => 'required|string|max:255',
            'items' => 'required|array',
            'items.*.sku' => 'required|string', // 不嚴格檢查 exists:inventory,sku
            'items.*.quantity' => 'required|integer|min:1',
        ]);

        try {
            DB::beginTransaction();

            $order = Order::create([
                'order_number' => 'ORD-' . strtoupper(Str::random(8)),
                'customer_name' => $request->input('customer_name'),
                'total_items' => array_sum(array_column($request->input('items'), 'quantity')),
            ]);

            // 觸發事件，啟動 WMS 流程 (Picking)
            event(new OrderCreated($order));

            DB::commit();

            return response()->json([
                'message' => '訂單創建成功，WMS 流程 (Picking) 已啟動。',
                'order_id' => $order->id,
                'order_number' => $order->order_number
            ], 201);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['message' => '訂單創建失敗: ' . $e->getMessage()], 500);
        }
    }
}
