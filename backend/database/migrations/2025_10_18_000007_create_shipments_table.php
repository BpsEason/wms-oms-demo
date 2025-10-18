<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('shipments', function (Blueprint $table) {
            $table->id();
            // order_id 設為唯一，因為一張訂單只對應一個貨件
            $table->foreignId('order_id')->constrained()->onDelete('cascade')->unique();
            $table->string('tracking_number')->unique()->nullable()->comment('物流追蹤碼');
            $table->string('carrier')->default('DefaultCarrier')->comment('物流公司');
            $table->string('status')->default('Pending')->comment('狀態: Pending, InTransit, Shipped, Delivered');
            $table->timestamp('packed_at')->nullable();
            $table->timestamp('shipped_at')->nullable();
            $table->json('tracking_log')->nullable()->comment('物流追蹤記錄');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('shipments');
    }
};
