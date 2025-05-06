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
        if (!Schema::hasTable('airports')) {
            Schema::create('airports', function (Blueprint $table) {
                $table->string('id', 6)->nullable();
                $table->string('ident', 7)->nullable();
                $table->string('type', 14)->nullable();
                $table->string('name', 108)->nullable();
                $table->string('latitude_deg', 21)->nullable();
                $table->string('longitude_deg', 21)->nullable();
                $table->string('elevation_ft', 12)->nullable();
                $table->string('continent', 9)->nullable();
                $table->string('iso_country', 11)->nullable();
                $table->string('iso_region', 10)->nullable();
                $table->string('municipality', 61)->nullable();
                $table->string('scheduled_service', 17)->nullable();
                $table->string('gps_code', 8)->nullable();
                $table->string('iata_code', 9)->nullable();
                $table->string('local_code', 10)->nullable();
                $table->string('home_link', 128)->nullable();
                $table->string('wikipedia_link', 128)->nullable();
                $table->string('keywords', 256)->nullable();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('airports');
    }
};
