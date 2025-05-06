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
        Schema::create('featuredproperties', function (Blueprint $table) {
            $table->id();
            $table->string("city");
            $table->string("image"); 
            $table->string("title");
            $table->decimal("rating", 2, 1);
            $table->string("offer_type"); 
            $table->decimal("price", 10, 2); 
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('featuredproperties');
    }
};
