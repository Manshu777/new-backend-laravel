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
        Schema::create('sitelayouts', function (Blueprint $table) {
            $table->id();
            $table->json("banner_image");
            $table->string("image1");
            $table->string("image2");
            $table->string("image3");

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sitelayouts');
    }
};
