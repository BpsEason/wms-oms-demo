<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Shipment extends Model
{
    use HasFactory;

    protected $fillable = [
        'order_id',
        'tracking_number',
        'carrier',
        'status',
        'packed_at',
        'shipped_at',
        'tracking_log',
    ];

    protected $casts = [
        'tracking_log' => 'array',
        'packed_at' => 'datetime',
        'shipped_at' => 'datetime',
    ];

    /**
     * 獲取擁有此貨件的訂單。
     */
    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }
}
