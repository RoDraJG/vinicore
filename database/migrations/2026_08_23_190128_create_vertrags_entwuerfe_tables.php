<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Schaltet die flüchtigen Puffer-Tabellen für schwebende UUID-Entwürfe scharf.
     */
    public function up(): void
    {
        // 1. Temporäre Tabelle für Kopfdaten (Arbeitet mit einer flüchtigen Text-UUID)
        Schema::create('vertrags_entwuerfe', function (Blueprint $table) {
            $table->uuid('id')->primary(); 
            $table->string('vertrag_nummer')->nullable();
            $table->string('typ');
            $table->string('vertragspartner_name')->nullable();
            $table->decimal('gesamtwert', 12, 2)->default(0);
            $table->date('gueltig_von')->nullable();
            $table->date('gueltig_bis')->nullable();
            $table->timestamps();
        });

        // 2. Temporäre Kreuztabelle für ungerenderte GeoJSON-Kartenflächen
        Schema::create('parzelle_entwurf', function (Blueprint $table) {
            $table->id();
            $table->foreignUuid('entwurf_id')->constrained('vertrags_entwuerfe')->onDelete('cascade');
            $table->string('gemarkung');
            $table->integer('flur');
            $table->string('flurstueck_zaehler');
            $table->string('flurstueck_nenner')->nullable();
            $table->integer('amtliche_flaeche_m2')->default(0);
            $table->json('raw_geojson')->nullable(); // Sichert das vollständige visuelle Gedächtnis
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('parzelle_entwurf');
        Schema::dropIfExists('vertrags_entwuerfe');
    }
};
