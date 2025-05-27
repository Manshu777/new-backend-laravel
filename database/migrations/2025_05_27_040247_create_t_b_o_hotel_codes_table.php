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
        Schema::create('t_b_o_hotel_codes', function (Blueprint $table) {
            $table->id();
     
        $table->string('hotel_code')->unique();
        $table->string('hotel_name');
        $table->string('latitude')->nullable();
        $table->string('longitude')->nullable();
        $table->string('city_code');


        $table->string('hotel_rating')->nullable();
        $table->text('address')->nullable();
        $table->string('country_name')->nullable();
        $table->string('country_code')->nullable();
        $table->string('city_name')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('t_b_o_hotel_codes');
    }
};
