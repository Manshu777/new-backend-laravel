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
        Schema::table('bookedhotels', function (Blueprint $table) {
              $table->string('tokenid')->nullable();
            $table->string('traceid')->nullable();
            $table->string('bookingId')->nullable();
            $table->string('pnr')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('bookedhotels', function (Blueprint $table) {
            //
        });
    }
};
