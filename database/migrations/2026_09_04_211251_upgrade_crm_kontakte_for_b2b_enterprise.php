<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('crm_kontakte', function (Blueprint $table) {
            // Rechtsform & E-Rechnung (Gesetzlicher Standard für B2B ab 2025/2026)
            $table->string('rechtsform', 50)->nullable()->after('firma')->comment('GmbH, KG, e.K. etc.');
            $table->string('leitweg_id', 100)->nullable()->after('ust_id')->comment('Leitweg-ID für XRechnung');
            
            // 🎯 FILIALSTEUERUNG: Abweichende Rechnungsanschrift (Mutter-GmbH)
            $table->boolean('weicht_rechnungsanschrift_ab')->default(0)->after('ort');
            $table->string('rechnung_firma', 255)->nullable()->after('weicht_rechnungsanschrift_ab');
            $table->string('rechnung_strasse', 150)->nullable()->after('rechnung_firma');
            $table->string('rechnung_hausnummer', 20)->nullable()->after('rechnung_strasse');
            $table->string('rechnung_adresszusatz', 255)->nullable()->after('rechnung_hausnummer');
            $table->string('rechnung_plz', 10)->nullable()->after('rechnung_adresszusatz');
            $table->string('rechnung_ort', 255)->nullable()->after('rechnung_plz');
        });
    }

    public function down(): void
    {
        Schema::table('crm_kontakte', function (Blueprint $table) {
            $table->dropColumn([
                'rechtsform', 'leitweg_id', 'weicht_rechnungsanschrift_ab',
                'rechnung_firma', 'rechnung_strasse', 'rechnung_hausnummer',
                'rechnung_adresszusatz', 'rechnung_plz', 'rechnung_ort'
            ]);
        });
    }
};
