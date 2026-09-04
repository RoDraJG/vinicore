<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Anrede für den Hauptpartner
        Schema::table('crm_kontakte', function (Blueprint $table) {
            $table->string('anrede', 30)->nullable()->after('partner_typ')->comment('Herr, Frau, Familie, Firma');
        });

        // 2. Anrede für die spezifischen Firmen-Ansprechpartner
        Schema::table('crm_kontakte_details', function (Blueprint $table) {
            $table->string('anrede', 30)->nullable()->after('crm_kontakt_id')->comment('Herr, Frau, Divers');
        });
    }

    public function down(): void
    {
        Schema::table('crm_kontakte', function (Blueprint $table) {
            $table->dropColumn('anrede');
        });
        Schema::table('crm_kontakte_details', function (Blueprint $table) {
            $table->dropColumn('anrede');
        });
    }
};
