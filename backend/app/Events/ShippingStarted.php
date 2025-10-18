<?php

namespace App\Events;

use App\Models\Order;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class ShippingStarted
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    /**
     * 訂單實例
     */
    public Order $order;

    /**
     * 創建一個新的事件實例。
     */
    public function __construct(Order $order)
    {
        $this->order = $order;
    }
}
