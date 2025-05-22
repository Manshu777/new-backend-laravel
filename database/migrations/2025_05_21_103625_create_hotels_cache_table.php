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
        Schema::create('tbohotelcodelist', function (Blueprint $table) {
            $table->id();
            $table->string('hotel_code')->unique();
            $table->string('hotel_name');
            $table->decimal('latitude', 10, 8);
            $table->decimal('longitude', 11, 8);
            $table->string('hotel_rating');
            $table->text('address');
            $table->string('country_name');
            $table->string('country_code');
            $table->string('city_name');
            $table->timestamp('expires_at')->nullable();

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('hotels_cache');
    }
};
