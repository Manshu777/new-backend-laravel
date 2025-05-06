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
      
if (!Schema::hasTable('bookflights')) {
            Schema::create('bookflights', function (Blueprint $table) {
                $table->id();
            $table->unsignedBigInteger('user_id')->nullable(); // Allow null for SET NULL to work
            $table->string('user_name'); // Storing the user's name
            $table->string('user_number'); // Storing the user's number
            $table->string('flight_name'); // Name of the flight
            $table->string('flight_number'); // Flight number
            $table->string('departure_from'); // Departure location
            $table->string('arrival_to'); // Arrival location
            $table->dateTime('flight_date'); // Flight date and time
            $table->dateTime('date_of_booking'); // Date of booking
            $table->dateTime('return_date')->nullable(); // Return date (nullable)
            $table->text('initial_response')->nullable(); // Initial response details
            $table->boolean('refund')->default(false); // Refund status (default is false)
            $table->text('response')->nullable(); // Final response details
            $table->timestamps();

            // Define the foreign key relationship
            $table->foreign('user_id')
                  ->references('id')
                  ->on('apkatripusers')
                  ->nullOnDelete();
            });








    }
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('bookflights');
    }
};
