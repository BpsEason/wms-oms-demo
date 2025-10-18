<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Order extends Model
{
    use HasFactory;

    protected $fillable = [
        'order_number',
        'customer_name',
        'status', // New, Picking, Sorting, Packing, Packed, Shipped
        'total_items',
    ];

    /**
     * 獲取訂單對應的貨件資訊。
     */
    public function shipment(): HasOne
    {
        return $this->hasOne(Shipment::class);
    }

    // 假設其他關係和方法 (pickingTasks, sortingTask, packingTask) 已經存在
}
