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
        Schema::create('bookedhotels', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->unsignedBigInteger('hotel_id')->nullable();
            $table->string('user_name');
            $table->string('user_number');
            $table->string('hotel_name')->nullable();
            $table->string('location')->nullable();
            $table->string('address')->nullable();
            $table->date('check_in_date');
            $table->date('check_out_date');
            $table->string('room_type');
            $table->decimal('price', 8, 2);
            
            $table->text('initial_response')->nullable();
            $table->boolean('refund')->default(false);
            $table->text('response')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('bookedhotels');
    }
};
