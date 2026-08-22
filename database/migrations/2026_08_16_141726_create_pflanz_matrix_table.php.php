<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('pflanz_matrix', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('anlage_id');
            $table->integer('zeile_nummer');
            $table->integer('position_index');
            
            // 📍 1. Absolute 2D-Geometrie im Raum (Keine starren Formeln!)
            $table->decimal('x_meter', 8, 3);
            $table->decimal('y_meter', 8, 3);
            $table->integer('abstand_zum_vorherigen_cm');

            // 🔀 2. Planmodus-Staging (Verhindert Datenmüll in der Planungsphase)
            $table->boolean('ist_entwurf')->default(true);

            // 🪵 3. Mechanischer Typ & Zustand
            $table->string('objekt_typ', 20); // rebe, endpfahl, reihenpfahl, anker
            $table->string('status', 20)->default('gesund'); // gesund, nachgepflanzt, fehlstelle, tot
            
            // 🧬 4. Genetische Klon-Overrides (Das biologische Mikromosaik einer Einzelrebe)
            $table->string('varietaet_rebsorte', 10)->nullable();
            $table->string('varietaet_klon', 20)->nullable();
            $table->string('varietaet_unterlage', 20)->nullable();
            $table->string('varietaet_unterlage_klon', 20)->nullable();
            $table->integer('nachpflanz_jahr')->nullable();
            
            // ⏳ 5. Temporal Lineage (Das rechtssichere Zeitschloss für unendliche Historie)
            $table->timestamp('gueltig_von')->useCurrent();
            $table->timestamp('gueltig_bis')->nullable(); 

            $table->timestamps();

            // Relationale Absicherung mit Kaskadierung bei Rodung der Gesamtanlage
            $table->foreign('anlage_id')->references('id')->on('anlagen')->onDelete('cascade');
            
            // High-Speed-Index für absolut flüssiges Rendering im WQHD-Spielfeld des Browsers
            $table->index(['anlage_id', 'zeile_nummer', 'ist_entwurf', 'gueltig_bis'], 'idx_gis_render_core');
        });
    }

    public function down(): void {
        Schema::dropIfExists('pflanz_matrix');
    }
};
