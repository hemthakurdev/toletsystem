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
        Schema::create('properties', function (Blueprint $table) {
            $table->id();
            $table->foreignId('org_id')->constrained('organizations')->onDelete('cascade');
            $table->string('title');
            $table->text('short_description')->nullable();
            $table->longText('long_description')->nullable();
            $table->enum('property_type', ['rent', 'sale', 'pg', 'commercial']);
            $table->string('category')->nullable();
            $table->decimal('price', 12, 2);
            $table->decimal('security_deposit', 12, 2)->nullable();
            $table->text('deposit_terms')->nullable();
            $table->string('city');
            $table->string('locality');
            $table->string('pincode');
            $table->text('address_line');
            $table->decimal('latitude', 10, 8)->nullable();
            $table->decimal('longitude', 11, 8)->nullable();
            $table->enum('furnished_status', ['furnished', 'semi_furnished', 'unfurnished'])->default('unfurnished');
            $table->integer('bedrooms')->nullable();
            $table->integer('bathrooms')->nullable();
            $table->integer('area_sqft')->nullable();
            $table->enum('availability_status', ['vacant', 'occupied', 'maintenance', 'blocked'])->default('vacant');
            $table->boolean('published')->default(false);
            $table->boolean('featured')->default(false);
            $table->timestamp('published_at')->nullable();
            $table->json('amenities')->nullable();
            $table->json('images')->nullable();
            $table->timestamps();

            $table->index(['org_id', 'published']);
            $table->index(['city', 'locality']);
            $table->index(['property_type', 'published']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('properties');
    }
};
