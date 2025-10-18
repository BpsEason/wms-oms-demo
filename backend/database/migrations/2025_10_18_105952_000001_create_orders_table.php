<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->string('order_number')->unique();
            $table->string('customer_name');
            // 狀態：New, Picking, Sorted, Packed, Shipped
            $table->enum('status', ['New', 'Picking', 'Sorted', 'Packed', 'Shipped'])->default('New');
            $table->unsignedInteger('total_items');
            $table->string('tracking_number')->nullable()->comment('物流追蹤單號');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('orders');
    }
};
