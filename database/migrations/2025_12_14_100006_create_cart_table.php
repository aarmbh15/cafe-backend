<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('cart', function (Blueprint $table) {
    $table->id();
    $table->foreignId('user_id')->constrained('users')->onDelete('cascade');

    $table->string('item_name');                    // e.g., "Custom Wrap - Chicken Tikka + Cheese"
    $table->json('selections');                     // Full list of chosen options: [{"name": "Wrap", "price": 3.49}, ...]
    $table->decimal('total_price', 8, 2);           // Calculated total for this item
    $table->integer('quantity')->default(1);

    $table->string('section')->nullable();          // e.g., "LUNCH", "BREAKFAST" – optional, for grouping

    $table->timestamps();

    // One unique instance per user (but allow multiples via quantity or duplicates)
    $table->index('user_id');
});
    }

    public function down(): void
    {
        Schema::dropIfExists('cart');
    }
};