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
        Schema::create('property_amenities', function (Blueprint $table) {
            $table->id();
            $table->foreignId('property_id')->constrained('properties')->onDelete('cascade');
            $table->enum('amenity_type', ['basic', 'luxury', 'safety', 'convenience', 'outdoor']);
            $table->string('amenity_name');
            $table->string('amenity_value')->nullable();
            $table->timestamps();

            $table->index(['property_id', 'amenity_type']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('property_amenities');
    }
};
