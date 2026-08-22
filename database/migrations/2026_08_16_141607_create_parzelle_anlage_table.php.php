<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('parzelle_anlage', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('anlage_id');
            $table->string('parzelle_uuid', 36); 
            
            // Teilflächen-Anteil & Planungs-Historie
            $table->decimal('anteil_prozent', 5, 2)->default(100.00);
            $table->boolean('ist_geplant')->default(false);
            $table->timestamp('gerodet_am')->nullable(); 

            $table->string('pflanz_richtung', 20)->default('horizontal');
            $table->timestamps();

            $table->foreign('anlage_id')->references('id')->on('anlagen')->onDelete('cascade');
            $table->foreign('parzelle_uuid')->references('parzelle_uuid')->on('parzellen')->onDelete('cascade');
            
            // Einzigartigkeits-Index schützt vor doppelter Zuordnung
            $table->unique(['anlage_id', 'parzelle_uuid'], 'uid_anl_parz');
        });
    }

    public function down(): void {
        Schema::dropIfExists('parzelle_anlage');
    }
};
