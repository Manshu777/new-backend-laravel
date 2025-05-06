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
        Schema::create('hotel_data', function (Blueprint $table) {
            $table->id();
            $table->string('city_code');
            $table->string('hotel_code')->index();
            $table->json('hotel_details'); // Store Hoteldetails response
            $table->json('search_results'); // Store Search response
            $table->timestamp('created_at')->useCurrent();
            $table->timestamp('updated_at')->nullable();
            $table->index(['city_code', 'created_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('hotel_data');
    }
};
