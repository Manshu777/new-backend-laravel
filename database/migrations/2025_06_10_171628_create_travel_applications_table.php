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
        Schema::create('travel_applications', function (Blueprint $table) {
            $table->id();
            $table->date('tentative_departure_date')->nullable();;
            $table->date('tentative_return_date')->nullable();;
            $table->string('full_name');
            $table->date('date_of_birth')->nullable();
            $table->enum('gender', ['Male', 'Female', 'Other'])->nullable();
            $table->string('travel_purpose')->nullable();
            $table->string('email');
            $table->string('phone');
            $table->string('passport_number')->nullable();
            $table->string('given_name')->nullable();
            $table->string('surname')->nullable();
            $table->string('place_of_birth')->nullable();
            $table->string('passport_front_path')->nullable();
            $table->string('passport_back_path')->nullable();
            $table->string('photograph_path')->nullable();
            $table->string('supporting_document_path')->nullable();
            $table->boolean('study_abroad')->default(false);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('travel_applications');
    }
};
