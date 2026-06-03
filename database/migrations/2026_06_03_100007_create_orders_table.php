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
            // Keep orders even if the user is removed, for accounting integrity.
            $table->foreignId('user_id')->constrained()->restrictOnDelete();
            $table->string('order_number', 20)->unique();
            $table->enum('status', ['pending', 'paid', 'cancelled', 'refunded'])
                ->default('pending')->index();
            // All money in integer cents.
            $table->unsignedInteger('subtotal_cents');
            $table->unsignedInteger('tax_cents')->default(0);
            $table->unsignedInteger('total_cents');
            // Shipping details snapshotted at purchase time.
            $table->string('shipping_name');
            $table->text('shipping_address');
            $table->string('shipping_phone', 30);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('orders');
    }
};
