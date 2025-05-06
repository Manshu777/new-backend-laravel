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
        Schema::create('holidayspackages', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('location');
            $table->string('duration');
            $table->json('traveloptions');
            $table->json('highlights');
            $table->json('inclusions');
            $table->json('exclusions');
            $table->float('rating',3);
            $table->json('reviews');
            $table->json('images');
            $table->json('cancellationPolicy');
            $table->json('itinerary');
            $table->json('additionalInfo');
            $table->boolean('status');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('holidayspackages');
    }
};
