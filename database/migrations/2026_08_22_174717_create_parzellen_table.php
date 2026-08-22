<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('parzellen', function (Blueprint $table) {
            $table->id();
            $table->string('parzelle_uuid')->index();
            $table->integer('version')->default(1);
            $table->string('freigabe_status', 20)->default('undefiniert')->comment('undefiniert, eingereicht, aktiv');
            $table->text('polygon_vektoren')->nullable();
            $table->string('gemeinde');
            $table->string('gemarkung');
            $table->string('gemarkungsschuelser')->nullable();
            $table->integer('flur');
            $table->string('flurstueck_zaehler', 20);
            $table->string('flurstueck_nenner', 20)->nullable();
            $table->string('flurname_lage')->nullable();
            $table->integer('amtliche_flaeche_m2')->default(0);
            $table->string('besitz_status', 30)->default('undefiniert')->comment('undefiniert, eigentum, gepachtet, verpachtet, verkauft, pacht_beendet');
            $table->string('aenderungsgrund')->nullable();
            $table->timestamp('gueltig_von')->nullable();
            $table->timestamp('gueltig_bis')->nullable();
            $table->foreignId('user_id')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('parzellen');
    }
};
