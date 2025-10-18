<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PackingTask extends Model
{
    protected $fillable = [
        'order_id', 'sku', 'quantity', 'status', 'package_type'
    ];

    protected $attributes = [
        'status' => 'Pending',
    ];

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class, 'order_id');
    }
}
