<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('order_items', function (Blueprint $table) {
    $table->id();
    $table->foreignId('order_id')->constrained('orders')->onDelete('cascade');

    $table->string('item_name');
    $table->json('selections');                     // Snapshot of what was ordered
    $table->decimal('total_price', 8, 2);            // Same as cart: price for quantity=1
    $table->integer('quantity');
    $table->decimal('line_total', 10, 2);            // unit_price * quantity

    $table->string('section')->nullable();           // e.g., "KIDS MEAL", "COFFEE & TEA"

    $table->timestamps();

    $table->index('order_id');
});
    }

    public function down(): void
    {
        Schema::dropIfExists('order_items');
    }
};