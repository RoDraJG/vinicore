<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('schlaege', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('betrieb_id')->index();
            $table->integer('schlagen_nummer');
            $table->string('bezeichnung');
            $table->decimal('flaeche_ha', 10, 4);
            $table->text('polygon_vektoren')->comment('Geometrie-Koordinaten des Großschlags');
            $table->string('rebsorte');
            $table->string('klon')->nullable();
            $table->string('unterlage')->nullable();
            $table->integer('pflanzjahr');
            $table->decimal('gassenbreite_m', 4, 2)->default(2.20);
            $table->decimal('stockabstand_m', 4, 2)->default(1.00);
            $table->string('erziehungssystem')->default('Spalier_Flachbogen');
            $table->integer('zeilen_anzahl')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('schlaege');
    }
};
