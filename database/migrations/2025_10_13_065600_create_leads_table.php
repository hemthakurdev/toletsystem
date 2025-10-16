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
        Schema::create('leads', function (Blueprint $table) {
            $table->id();
            $table->foreignId('property_id')->constrained('properties')->onDelete('cascade');
            $table->foreignId('org_id')->constrained('organizations')->onDelete('cascade');
            $table->string('name');
            $table->string('phone');
            $table->string('email')->nullable();
            $table->text('message');
            $table->enum('status', ['new', 'contacted', 'interested', 'not_interested', 'converted'])->default('new');
            $table->enum('source', ['public_listing', 'manual', 'referral'])->default('public_listing');
            $table->integer('lead_score')->default(0);
            $table->timestamp('contacted_at')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index(['org_id', 'status']);
            $table->index(['property_id', 'status']);
            $table->index(['created_at', 'status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('leads');
    }
};
