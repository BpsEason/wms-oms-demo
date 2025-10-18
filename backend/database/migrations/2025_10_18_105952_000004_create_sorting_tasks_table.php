<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sorting_tasks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained('orders')->onDelete('cascade');
            $table->string('sku');
            $table->unsignedInteger('quantity')->comment('所需分揀數量');
            $table->unsignedInteger('sorted_quantity')->default(0)->comment('已分揀數量');
            // 狀態：Pending (待分), PartiallySorted, Completed (已完成)
            $table->enum('status', ['Pending', 'PartiallySorted', 'Completed'])->default('Pending');
            $table->string('sorting_bin')->comment('分揀目標箱/格');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sorting_tasks');
    }
};
