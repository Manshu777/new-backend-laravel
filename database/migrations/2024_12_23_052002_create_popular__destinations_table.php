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
        Schema::create('popular__destinations_flight', function (Blueprint $table) {
            $table->id();
            $table->string("from");
            $table->string("from_code");
            $table->string("dis");

            $table->string("to");
            $table->string("to_code");

            $table->timestamps();
        });
        Schema::create('popular__destinations_hotels', function (Blueprint $table) {
            $table->id();
            $table->string("from");
            $table->string("from_code");
            $table->string("dis");
            $table->timestamps();
        });
       
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('popular__destinations_flight');
        Schema::dropIfExists('popular__destinations_hotels');

    }
};
