<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        // Tank and barrel registry (Gärfässer)
        Schema::create('gaarfaesser', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('betrieb_id')->index();
            $table->string('fass_nr')->comment('Tank/barrel ID');
            $table->enum('fass_typ', ['edelstahltank', 'holzfass', 'barrique', 'amphore'])->default('edelstahltank');
            $table->decimal('volumen_l', 10, 2)->comment('Tank volume in liters');
            $table->string('standort')->nullable()->comment('Cellar location/rack');
            $table->enum('status', ['leer', 'gefuellt', 'reparatur', 'retired'])->default('leer');
            $table->date('erwerb_datum')->nullable()->comment('Acquisition date');
            $table->integer('erstellungsjahr')->nullable()->comment('Year built (for barrels)');
            $table->text('notizen')->nullable();
            $table->timestamps();
            
            $table->foreign('betrieb_id')->references('id')->on('users')->onDelete('cascade');
            $table->unique(['betrieb_id', 'fass_nr']);
        });

        // Fermentation processes (Gärprozesse)
        Schema::create('gaarprozesse', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('fass_id')->index();
            $table->unsignedBigInteger('lesegan_id')->nullable()->index()->comment('Source harvest pass');
            $table->string('wein_id')->nullable()->comment('Wine batch/lot ID');
            $table->string('rebsorte')->nullable();
            $table->date('start_datum');
            $table->date('ende_datum')->nullable();
            $table->enum('status', ['gaerig', 'trocken', 'im_ausbau', 'abgefuellt'])->default('gaerig');
            $table->decimal('anfangsgewicht_oechsle', 4, 1)->nullable();
            $table->decimal('anfangstemperatur_celsius', 4, 2)->nullable();
            $table->text('notizen')->nullable();
            $table->timestamps();
            
            $table->foreign('fass_id')->references('id')->on('gaarfaesser')->onDelete('restrict');
            $table->foreign('lesegan_id')->references('id')->on('lesegaenge')->onDelete('set null');
        });

        // Fermentation measurements (Gär-Messwerte)
        Schema::create('gaar_messwerte', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('gaarprozess_id')->index();
            $table->dateTime('messdatum')->index();
            $table->decimal('temperatur_celsius', 5, 2)->comment('Temperature');
            $table->decimal('dichte', 6, 4)->comment('Specific gravity');
            $table->decimal('alkohol_prozent', 5, 2)->nullable()->comment('Estimated alcohol %');
            $table->decimal('restzucker_g_l', 6, 2)->nullable()->comment('Residual sugar g/L');
            $table->string('beobachter')->nullable()->comment('Observer name');
            $table->text('notizen')->nullable();
            $table->timestamps();
            
            $table->foreign('gaarprozess_id')->references('id')->on('gaarprozesse')->onDelete('cascade');
        });

        // Cellar treatment log (Kellerbuch - cellar operations)
        Schema::create('keller_behandlungen', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('gaarprozess_id')->index();
            $table->dateTime('durchgefuehrt_am');
            $table->enum('behandlungstyp', [
                'schwefelung',
                'anreicherung',
                'saeure_regulierung',
                'schoenung',
                'filtration',
                'abstich_hefenabzug',
                'verschnitt',
                'other'
            ]);
            $table->string('stoff_oder_massnahme')->comment('Substance/procedure name');
            $table->decimal('menge', 10, 2)->nullable();
            $table->string('einheit')->nullable()->comment('Unit (kg, g, L, etc.)');
            $table->string('durchgefuehrt_von')->nullable()->comment('Staff member name');
            $table->text('notizen')->nullable();
            $table->timestamps();
            
            $table->foreign('gaarprozess_id')->references('id')->on('gaarprozesse')->onDelete('cascade');
        });

        // Lab analytics data (Analytik-Datenbank)
        Schema::create('keller_laborwerte', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('gaarprozess_id')->index();
            $table->date('analysedatum');
            $table->string('labor_name')->nullable()->comment('External lab name');
            $table->decimal('alkohol_vol_prozent', 5, 2)->nullable();
            $table->decimal('gesamtsaeure_g_l', 5, 2)->nullable();
            $table->decimal('fluechtbige_saeure_g_l', 5, 2)->nullable();
            $table->decimal('freier_so2_mg_l', 6, 2)->nullable()->comment('Free SO₂');
            $table->decimal('gesamt_so2_mg_l', 6, 2)->nullable()->comment('Total SO₂');
            $table->decimal('restzucker_g_l', 6, 2)->nullable();
            $table->decimal('ph_wert', 4, 2)->nullable();
            $table->decimal('extrakt_g_l', 6, 2)->nullable();
            $table->text('zusatzliche_parameter')->nullable()->comment('JSON for extended data');
            $table->timestamps();
            
            $table->foreign('gaarprozess_id')->references('id')->on('gaarprozesse')->onDelete('cascade');
        });

        // Cellar inventory (Keller-Lagerbestand)
        Schema::create('keller_lagerbestaende', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('betrieb_id')->index();
            $table->unsignedBigInteger('artikel_id')->nullable()->comment('Product/bottle SKU');
            $table->string('artikel_name')->comment('E.g., "Riesling 2025", "Spätburgunder Reserve"');
            $table->string('standort')->comment('Storage rack/location');
            $table->decimal('menge_l', 10, 2)->comment('Quantity in liters');
            $table->decimal('restmenge_l', 10, 2)->default(0)->comment('Quantity remaining (tracking consumption)');
            $table->date('abfuell_datum')->nullable()->comment('Bottling date');
            $table->integer('abfuell_menge_flaschen')->nullable();
            $table->decimal('kosten_euro', 12, 2)->nullable()->comment('Storage/production cost');
            $table->timestamps();
            
            $table->foreign('betrieb_id')->references('id')->on('users')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('keller_laborwerte');
        Schema::dropIfExists('keller_behandlungen');
        Schema::dropIfExists('gaar_messwerte');
        Schema::dropIfExists('gaarprozesse');
        Schema::dropIfExists('gaarfaesser');
        Schema::dropIfExists('keller_lagerbestaende');
    }
};
