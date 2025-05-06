<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('sliders', function (Blueprint $table) {
            $table->json('slider_img')->change(); // Convert to JSON for multiple images
            $table->json('link')->change(); // Store multiple links
        });
    }

    public function down(): void
    {
        Schema::table('sliders', function (Blueprint $table) {
            $table->string('slider_img')->change();
            $table->json('link')->change();
        });
    }
};
