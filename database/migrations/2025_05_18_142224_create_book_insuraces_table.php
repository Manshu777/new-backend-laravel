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
        Schema::create('book_insuraces', function (Blueprint $table) {
              $table->id();
            $table->string('booking_id')->unique()->index();
            $table->string('trace_id');
            $table->integer('result_index');
            $table->string('title', 10);
            $table->string('first_name', 50);
            $table->string('last_name', 50);
            $table->string('beneficiary_title', 10);
            $table->string('beneficiary_name');
            $table->string('relationship_to_insured');
            $table->string('relationship_to_beneficiary');
            $table->string('gender', 1);
            $table->date('dob');
            $table->string('passport_no');
            $table->string('phone_number', 15);
            $table->string('email');
            $table->string('address_line1');
            $table->string('city_code', 3);
            $table->string('country_code', 3);
            $table->string('major_destination');
            $table->string('passport_country', 2);
            $table->string('pin_code', 10);
            $table->string('policy_start_date');
            $table->string('policy_end_date');
            $table->text('coverage_details')->nullable();
            $table->string('pdf_url')->nullable();
            $table->string('status')->default('pending');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('book_insuraces');
    }
};
