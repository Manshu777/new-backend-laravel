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
        if(!Schema::hasTable("chackout_flights")){
        Schema::create('chackout_flights', function (Blueprint $table) {
            $table->string("id");
            $table->string("user_id");
            $table->json("flight_info");
            $table->string("price");
            $table->timestamps();
        });
    }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('chackout_flights');
    }
};
