<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('packing_tasks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained('orders')->onDelete('cascade');
            $table->string('sku');
            $table->unsignedInteger('quantity');
            // 狀態：Pending (待包裝), Completed (已包裝)
            $table->enum('status', ['Pending', 'Completed'])->default('Pending');
            $table->string('package_type')->nullable()->comment('包裝類型, e.g., Box A');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('packing_tasks');
    }
};
