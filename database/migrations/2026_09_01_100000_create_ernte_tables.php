<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        // Harvest campaigns (Erntebetreuung)
        Schema::create('ernte_kampagnen', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('betrieb_id')->index();
            $table->integer('jahr')->comment('Harvest year (e.g., 2026)');
            $table->enum('status', ['geplant', 'aktiv', 'abgeschlossen'])->default('geplant');
            $table->date('geplanter_start')->nullable();
            $table->date('geplantes_ende')->nullable();
            $table->date('tatsachlicher_start')->nullable();
            $table->date('tatsachliches_ende')->nullable();
            $table->text('notizen')->nullable();
            $table->timestamps();
            
            $table->foreign('betrieb_id')->references('id')->on('users')->onDelete('cascade');
            $table->unique(['betrieb_id', 'jahr']);
        });

        // Individual harvest passes (Lesegänge)
        Schema::create('lesegaenge', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('kampagne_id')->index();
            $table->uuid('schlag_id')->index()->comment('Field/Schlag UUID');
            $table->date('lesedatum')->index();
            $table->time('lesebeginn')->nullable();
            $table->time('leseende')->nullable();
            $table->decimal('mostgewicht_grad_oechsle', 4, 1)->nullable()->comment('Must weight in °Oechsle');
            $table->decimal('gesamtsaeure_g_l', 4, 2)->nullable()->comment('Total acidity g/L');
            $table->decimal('ph_wert', 3, 2)->nullable()->comment('pH value');
            $table->enum('leseart', ['handlese', 'vollernter', 'selektive_lese'])->default('handlese');
            $table->text('notizen')->nullable();
            $table->timestamps();
            
            $table->foreign('kampagne_id')->references('id')->on('ernte_kampagnen')->onDelete('cascade');
        });

        // Worker assignments to harvest passes
        Schema::create('lesetermine', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('lesegan_id')->index();
            $table->unsignedBigInteger('arbeiter_id')->index();
            $table->integer('reifegrad_prozent')->nullable()->comment('Ripeness % estimate');
            $table->decimal('geplante_menge_kg', 8, 2)->nullable();
            $table->enum('status', ['geplant', 'in_arbeit', 'abgeschlossen', 'abgebrochen'])->default('geplant');
            $table->timestamps();
            
            $table->foreign('lesegan_id')->references('id')->on('lesegaenge')->onDelete('cascade');
            $table->foreign('arbeiter_id')->references('id')->on('arbeitskraefte')->onDelete('cascade');
            $table->unique(['lesegan_id', 'arbeiter_id']);
        });

        // Harvest outcome (results per pass)
        Schema::create('ernte_ergebnisse', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('lesegan_id')->unique();
            $table->decimal('menge_kg', 10, 2)->comment('Total harvest weight in kg');
            $table->string('lesegut_zustand')->nullable()->comment('Grape condition: healthy, Botrytis, damaged');
            $table->text('qualitaetsnotiz')->nullable();
            $table->decimal('durchschnittliche_restzucker_g_l', 5, 2)->nullable();
            $table->timestamps();
            
            $table->foreign('lesegan_id')->references('id')->on('lesegaenge')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ernte_ergebnisse');
        Schema::dropIfExists('lesetermine');
        Schema::dropIfExists('lesegaenge');
        Schema::dropIfExists('ernte_kampagnen');
    }
};
