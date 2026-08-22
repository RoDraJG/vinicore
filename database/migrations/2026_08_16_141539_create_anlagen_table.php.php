<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('anlagen', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('schlag_id');
            $table->string('name');
            
            // Lebenszyklus & Pessimistic-Locking für den Mehrbenutzer-Planmodus
            $table->enum('plan_status', ['in_planung', 'aktiv_bewirtschaftet', 'gerodet'])->default('in_planung');
            $table->unsignedBigInteger('locked_by_user_id')->nullable();
            $table->timestamp('locked_until')->nullable();

            // Geometrische Parameter (Kopf-Vorgewende & Seiten-Randabstände)
            $table->integer('vorgewende_start_cm')->default(400);
            $table->integer('vorgewende_ende_cm')->default(400);
            $table->integer('randabstand_links_cm')->default(120);
            $table->integer('randabstand_rechts_cm')->default(120);
            
            // Mechanische Drahtrahmen-Zugparameter
            $table->integer('abstand_anker_endpfahl_cm')->default(150);
            $table->integer('abstand_endpfahl_rebe_cm')->default(80);
            
            // Biologische Durchschnittswerte (Fallback für Schätzungen)
            $table->integer('ziel_gassenbreite_cm')->default(200);
            $table->integer('stockabstand_cm')->default(100);
            $table->integer('reihenpfahl_abstand_cm')->default(450);

            $table->timestamps();

            $table->foreign('schlag_id')->references('id')->on('schlaege')->onDelete('cascade');
        });
    }

    public function down(): void {
        Schema::dropIfExists('anlagen');
    }
};
