<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('parzellen', function (Blueprint $table) {
            $table->id();
            $table->uuid('parzelle_uuid')->unique()->index(); 
            $table->integer('version')->default(1);
            $table->json('polygon_vektoren')->nullable(); 

            // Amtliche Katasterdaten (ALKIS-Standard)
            $table->string('gemeinde')->default('Weinbaugemeinde');
            $table->string('gemarkung');
            $table->string('flur')->default('1');
            $table->string('flurstueck_zaehler');
            $table->string('flurstueck_nenner')->nullable();
            $table->string('flurname_lage'); 
            $table->integer('amtliche_flaeche_m2'); 

            // Historisierungs-Zeitschloss
            $table->string('aenderungsgrund')->nullable(); 
            $table->timestamp('gueltig_von')->useCurrent();
            $table->timestamp('gueltig_bis')->nullable(); 
            
            $table->timestamps();
        });
    }

    public function down(): void {
        Schema::dropIfExists('parzellen');
    }
};
