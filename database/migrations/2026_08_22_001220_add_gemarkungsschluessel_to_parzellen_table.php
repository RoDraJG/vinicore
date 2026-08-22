<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Führt die Migration aus (Spalte hinzufügen).
     */
    public function up(): void
    {
        Schema::table('parzellen', function (Blueprint $table) {
            // 🚀 ARCHITEKTUR-FIX: Fügt das Feld nach der Gemarkung ein und erlaubt Nullwerte für Altbestände
            $table->string('gemarkungsschuelser', 20)
                  ->nullable()
                  ->after('gemarkung')
                  ->comment('Amtlicher ALKIS Gemarkungsschlüssel (gemaschl)');
        });
    }

    /**
     * Macht die Migration rückgängig (Spalte wieder löschen).
     */
    public function down(): void
    {
        Schema::table('parzellen', function (Blueprint $table) {
            // 🛡️ REVISIONS-SCHUTZ: Ermöglicht ein sauberes Rollback
            $table->dropColumn('gemarkungsschuelser');
        });
    }
};
