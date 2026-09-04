<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Zündet das kaufmännische Upgrade für die Partner-Stammdaten.
     */
    public function up(): void
    {
        // 🎯 KORREKT: Läuft direkt auf deiner realen crm_kontakte-Tabelle ein
        Schema::table('crm_kontakte', function (Blueprint $table) {
            
            // 📊 Buchhaltung, Steuern & Fiskal-Erweiterung
            if (!Schema::hasColumn('crm_kontakte', 'steuernummer')) {
                $table->string('steuernummer', 50)->nullable()->after('ust_id')->comment('Inländische Steuernummer für B2B');
            }
            if (!Schema::hasColumn('crm_kontakte', 'buchhaltung_gruppe')) {
                $table->string('buchhaltung_gruppe', 50)->default('inland')->after('debitorennummer')->comment('inland, eu_steuerfrei, drittland');
            }
            
            // 💸 Skonto- & Konditionen-Getriebe
            if (!Schema::hasColumn('crm_kontakte', 'skonto_prozent')) {
                $table->decimal('skonto_prozent', 5, 2)->default(0.00)->after('standard_zahlungsziel_tage');
            }
            if (!Schema::hasColumn('crm_kontakte', 'skonto_tage')) {
                $table->integer('skonto_tage')->default(0)->after('skonto_prozent');
            }
            
            // 🛡️ Risikomanagement, Ansprechpartner & Logistik
            if (!Schema::hasColumn('crm_kontakte', 'ist_gesperrt')) {
                $table->boolean('ist_gesperrt')->default(false)->after('ist_lieferant')->comment('Harte Liefersperre bei Zahlungsverzug');
            }
            if (!Schema::hasColumn('crm_kontakte', 'ansprechpartner_name')) {
                $table->string('ansprechpartner_name', 255)->nullable()->after('firma')->comment('Einkäufer, Sommelier oder Küchenchef');
            }
            if (!Schema::hasColumn('crm_kontakte', 'speditions_hinweis')) {
                $table->string('speditions_hinweis', 255)->nullable()->after('versanddienstleister')->comment('z.B. Hebebühne erforderlich');
            }
        });
    }

    /**
     * Rollback-Sicherung.
     */
    public function down(): void
    {
        Schema::table('crm_kontakte', function (Blueprint $table) {
            $table->dropColumn([
                'steuernummer', 
                'buchhaltung_gruppe', 
                'skonto_prozent', 
                'skonto_tage', 
                'ist_gesperrt', 
                'ansprechpartner_name',
                'speditions_hinweis'
            ]);
        });
    }
};
