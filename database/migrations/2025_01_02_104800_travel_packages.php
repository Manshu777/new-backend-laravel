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
        //
        if(!Schema::hasTable("travel_packages")){   
  Schema::table("travel_packages",function(Blueprint $table){
    $table->boolean('is_active')->default(false);
    $table->string('place_type');


  });
}


    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        //
    }
};
