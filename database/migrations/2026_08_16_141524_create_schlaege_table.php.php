<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        // database/migrations/create_schlaege_table.php
        Schema::create('schlaege', function (Blueprint $table) {
            $table->id();
            $table->foreignId('betrieb_id')->constrained('betriebsdaten');
            $table->integer('schlagen_nummer');
            $table->string('bezeichnung');
            $table->decimal('flaeche_ha', 10, 4);
            $table->text('polygon_vektoren'); // Speichert die exakten GIS-Grenzen
            
            // 🧬 DIE DNA DES DIGITALEN ZWILLINGS:
            $table->string('rebsorte');            // z.B. "Riesling"
            $table->string('klon');                // z.B. "Standard-Klon 237" (Wichtig für Aromatik)
            $table->string('unterlage');            // z.B. "SO4" (Wurzelsystem gegen Reblaus/Trockenheit)
            $table->integer('pflanzjahr');         // Alter des Weinbergs steuert die Qualität
            
            // 📏 PHYSIKALISCHE GEOMETRIE (FÜR AUTOMATISCHE KALKULATIONEN):
            $table->decimal('gassenbreite_m', 4, 2)->default(2.20);  // Abstand von Zeile zu Zeile
            $table->decimal('stockabstand_m', 4, 2)->default(1.00);  // Abstand von Rebe zu Rebe
            $table->string('erziehungssystem')->default('Spalier_Flachbogen'); // Zeigt die Laubwand-Architektur
            $table->integer('zeilen_anzahl')->nullable();            // Für zeilengenaues Arbeitstracking
            
            $table->timestamps();
        });
    }

    public function down(): void {
        Schema::dropIfExists('schlaege');
    }
};
