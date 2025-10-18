<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SortingTask extends Model
{
    protected $fillable = [
        'order_id', 'sku', 'quantity', 'sorted_quantity', 'status', 'sorting_bin'
    ];

    protected $attributes = [
        'status' => 'Pending',
        'sorted_quantity' => 0,
    ];

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class, 'order_id');
    }
}
