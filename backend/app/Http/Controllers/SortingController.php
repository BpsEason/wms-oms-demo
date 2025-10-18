<?php

namespace App\Http\Controllers;

use App\Models\SortingTask;
use App\Models\Order;
use App\Listeners\CheckSortingCompletion;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class SortingController extends Controller
{
    public function index()
    {
        $tasks = SortingTask::orderBy('id', 'desc')->get();
        $tasks->load('order'); 
        return response()->json(['tasks' => $tasks]);
    }

    // 模擬分揀員掃碼分揀商品
    public function sort(SortingTask $sortingTask, Request $request)
    {
        $request->validate(['quantity' => 'required|integer|min:1']);
        
        if ($sortingTask->status === 'Completed') {
            return response()->json(['message' => '該分揀任務已完成。'], 400);
        }
        
        $quantity = $request->input('quantity');

        try {
            DB::beginTransaction();

            $newSortedQuantity = $sortingTask->sorted_quantity + $quantity;
            
            if ($newSortedQuantity > $sortingTask->quantity) {
                DB::rollBack();
                return response()->json(['message' => '分揀數量超出所需數量。'], 400);
            }

            $sortingTask->sorted_quantity = $newSortedQuantity;

            if ($newSortedQuantity === $sortingTask->quantity) {
                $sortingTask->status = 'Completed';
            } else {
                $sortingTask->status = 'PartiallySorted';
            }
            $sortingTask->save();
            
            // 檢查訂單是否所有分揀任務完成
            $order = Order::find($sortingTask->order_id);
            (new CheckSortingCompletion())->handle((object)['order' => $order]);

            DB::commit();
            return response()->json(['message' => "分揀任務 {$sortingTask->id} 狀態已更新。"], 200);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['message' => '分揀失敗: ' . $e->getMessage()], 500);
        }
    }
}
