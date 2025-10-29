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
        Schema::table('tenants', function (Blueprint $table) {
            $table->string('occupation')->nullable()->after('email');
            $table->string('company')->nullable()->after('occupation');
            $table->string('emergency_contact_name')->nullable()->after('security_deposit');
            $table->string('emergency_contact_phone')->nullable()->after('emergency_contact_name');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('tenants', function (Blueprint $table) {
            $table->dropColumn([
                'occupation',
                'company',
                'emergency_contact_name',
                'emergency_contact_phone',
            ]);
        });
    }
};


