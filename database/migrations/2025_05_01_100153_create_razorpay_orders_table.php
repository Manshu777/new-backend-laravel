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
        Schema::create('razorpay_orders', function (Blueprint $table) {
            $table->id();
            $table->string('order_id')->unique()->comment('Razorpay order ID');
            $table->string('booking_pnr')->index()->comment('Booking PNR from frontend');
            $table->unsignedBigInteger('amount')->comment('Amount in paise');
            $table->string('currency', 3)->comment('Currency code, e.g., INR');
            $table->string('receipt')->comment('Receipt ID, e.g., receipt_PNR123');
            $table->string('status')->default('created')->comment('Order status: created, paid, failed');
            $table->unsignedBigInteger('created_by')->nullable()->comment('User ID who created the order');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('razorpay_orders');
    }
};
