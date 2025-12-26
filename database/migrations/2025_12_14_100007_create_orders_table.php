<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('address_id')->nullable()->constrained('user_addresses')->onDelete('restrict');

            $table->string('order_number')->unique();

            $table->decimal('items_total', 10, 2);     // Subtotal of products
            $table->decimal('delivery_fee', 8, 2)->default(0.00);
            $table->decimal('total', 10, 2);

            $table->string('payment_method')->default('cash');  // Only COD now
            // $table->string('payment_status')->default('pending');

            $table->text('customer_notes')->nullable();  // Any special requests
            $table->timestamp('placed_at')->useCurrent();

            $table->timestamps();
            $table->softDeletes();

            $table->index('placed_at');
            $table->index('order_number');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('orders');
    }
};