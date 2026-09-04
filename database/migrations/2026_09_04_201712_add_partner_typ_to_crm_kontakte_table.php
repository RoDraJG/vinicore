<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('crm_kontakte', function (Blueprint $table) {
            // 🎯 Weiche: privat oder firma
            $table->string('partner_typ', 20)->default('privat')->index()->after('kontakt_uuid');
        });
    }

    public function down(): void
    {
        Schema::table('crm_kontakte', function (Blueprint $table) {
            $table->dropColumn('partner_typ');
        });
    }
};
