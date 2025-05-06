<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('hotels', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('email')->unique();
            $table->string('password');
            $table->string('address')->nullable();
            $table->string('phone')->nullable();
            $table->boolean('useractive')->default(true);
            $table->timestamp('email_verified_at')->nullable();
            $table->string('remember_token', 100)->nullable();
            $table->timestamps();
        });

        Schema::create('hotel_details', function (Blueprint $table) {
            $table->id();
            $table->string('property_name');
            $table->text('hotel_des');
            $table->json('hotel_img');
            $table->smallInteger('rating');
            $table->date('built_year');
            $table->date('accepting_since');
            $table->string('email')->unique();
            $table->string('number', 15)->nullable();
            $table->string('land_line', 15)->nullable();
            $table->string('address');
            $table->decimal('lat', 10, 8)->nullable();
            $table->decimal('longitude', 11, 8)->nullable();
            $table->string('house_no');
            $table->json('social_media')->nullable();
            $table->string('locality');
            $table->string('pincode');
            $table->string('country');
            $table->string('state');
            $table->string('city');
            $table->text('terms');
            $table->unsignedBigInteger('hotel_id')->nullable();
            $table->foreign('hotel_id')->references('id')->on('hotels')->onDelete('cascade');
            $table->timestamps();
        });

        Schema::create('amenities', function (Blueprint $table) {
            $table->id();
            $table->json('mandatory');
            $table->json('basic_facilities');
            $table->json('general_services');
            $table->json('outdoor_activities');
            $table->json('common_areas');
            $table->json('food_drinks');
            $table->json('health_wellness');
            $table->json('business_center');
            $table->json('beauty_spa');
            $table->json('security');
            $table->json('transfers');
            $table->json('shopping');
            $table->json('payment_services');
            $table->json('indoor_activities');
            $table->json('family_kids');
            $table->json('pets_essentials');
            $table->unsignedBigInteger('hotel_details_id')->nullable();
            $table->foreign('hotel_details_id')->references('id')->on('hotel_details')->onDelete('cascade');
            $table->timestamps();
        });

        Schema::create('room_regs', function (Blueprint $table) {
            $table->id();
            $table->string('room_type');
            $table->string('size');
            $table->string('bed_type');
            $table->decimal('price', 8, 2);
            $table->string('max_occupancy');
            $table->integer('room_ava');
            $table->json('features');
            $table->json('image');
            $table->text('room_des');
            $table->json('additional_serv');
            $table->unsignedBigInteger('hotel_details_id')->nullable();
            $table->foreign('hotel_details_id')->references('id')->on('hotel_details')->onDelete('cascade');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('room_regs');
        Schema::dropIfExists('amenities');
        Schema::dropIfExists('hotel_details');
        Schema::dropIfExists('hotels');
    }
};
