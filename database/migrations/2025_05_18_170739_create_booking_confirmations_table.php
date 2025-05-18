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
        Schema::create('booking_confirmations', function (Blueprint $table) {
            $table->id();
             $table->string('trace_id');
            $table->string('booking_status');
            $table->decimal('invoice_amount', 10, 2);
            $table->string('invoice_number');
            $table->integer('bus_id');
            $table->string('ticket_no');
            $table->string('travel_operator_pnr');
            $table->json('passenger_details');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('booking_confirmations');
    }
};
