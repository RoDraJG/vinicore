<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Führt die physikalische Trennung von Straße und Hausnummer aus.
     */
    public function up(): void
    {
        Schema::table('crm_kontakte', function (Blueprint $table) {
            // Hauptadresse anpassen
            $table->string('strasse', 150)->nullable()->after('ansprechpartner_name');
            $table->string('hausnummer', 20)->nullable()->after('strasse');
            
            // Lieferadresse anpassen
            $table->string('liefer_strasse', 150)->nullable()->after('adresszusatz');
            $table->string('liefer_hausnummer', 20)->nullable()->after('liefer_strasse');

            // Die alten Kombi-Felder sicherheitshalber löschen, falls sie nicht mehr gebraucht werden
            if (Schema::hasColumn('crm_kontakte', 'strasse_nr')) {
                $table->dropColumn('strasse_nr');
            }
            if (Schema::hasColumn('crm_kontakte', 'liefer_strasse_nr')) {
                $table->dropColumn('liefer_strasse_nr');
            }
        });
    }

    /**
     * Rollback-Sicherung.
     */
    public function down(): void
    {
        Schema::table('crm_kontakte', function (Blueprint $table) {
            $table->string('strasse_nr', 255)->nullable();
            $table->string('liefer_strasse_nr', 255)->nullable();
            $table->dropColumn(['strasse', 'hausnummer', 'liefer_strasse', 'liefer_hausnummer']);
        });
    }
};
