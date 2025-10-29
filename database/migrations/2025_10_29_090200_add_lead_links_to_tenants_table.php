<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('tenants', function (Blueprint $table) {
            $table->unsignedBigInteger('lead_id')->nullable()->after('org_id');
            $table->unsignedBigInteger('lead_user_id')->nullable()->after('lead_id');
            $table->index('lead_id');
            $table->index('lead_user_id');
        });
    }

    public function down(): void
    {
        Schema::table('tenants', function (Blueprint $table) {
            $table->dropIndex(['lead_id']);
            $table->dropIndex(['lead_user_id']);
            $table->dropColumn(['lead_id', 'lead_user_id']);
        });
    }
};


