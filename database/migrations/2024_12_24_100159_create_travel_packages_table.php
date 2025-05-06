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
        Schema::create('travel_packages', function (Blueprint $table) {
            $table->id();
            $table->string("package_name");
            $table->string("package_Type");
            $table->string("banner_image");
            $table->string("rating");
            $table->string("country");
            $table->string("state");
            $table->string("city");
            $table->integer("duration");
            $table->text("des");
            $table->string("price");
            $table->json("images"); 
            $table->json( "activite");
            $table->json("terms");
            $table->string("slug")->unique();


            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('travel_packages');
    }
};
