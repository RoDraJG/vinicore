<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        // Worker profiles (Arbeitskräfte)
        Schema::create('arbeitskraefte', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('betrieb_id')->index();
            $table->unsignedBigInteger('benutzer_id')->nullable()->unique()->comment('Link to user account if registered');
            $table->string('vorname');
            $table->string('nachname');
            $table->enum('rolle_im_betrieb', ['saisonkraft', 'dauerarbeit', 'kellermeister', 'verkaufsleiter', 'aussenwirtschaft'])->default('saisonkraft');
            $table->string('qr_code')->nullable()->unique()->comment('QR code for time clock');
            $table->string('personalnummer')->nullable();
            $table->date('einstellungsdatum')->nullable();
            $table->date('austritt_datum')->nullable();
            $table->enum('status', ['aktiv', 'inaktiv', 'suspended'])->default('aktiv');
            $table->string('kontakt_telefon')->nullable();
            $table->string('kontakt_email')->nullable();
            $table->text('notizen')->nullable();
            $table->timestamps();
            
            $table->foreign('betrieb_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('benutzer_id')->references('id')->on('users')->onDelete('set null');
        });

        // Time clock entries (Zeiterfassung)
        Schema::create('zeiterfassungen', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('arbeitskraft_id')->index();
            $table->date('arbeitstag')->index();
            $table->dateTime('eintrag_datetime')->comment('Clock-in time');
            $table->dateTime('austrag_datetime')->nullable()->comment('Clock-out time');
            $table->integer('pausenminuten')->default(0)->comment('Total break time in minutes');
            $table->decimal('arbeitsminuten', 6, 0)->nullable()->comment('Net work time in minutes');
            $table->decimal('arbeitsstunden', 8, 2)->nullable()->comment('Work hours (calculated)');
            $table->string('qr_code_eingescannt')->nullable();
            $table->enum('status', ['offen', 'abgeschlossen', 'genehmigt', 'bezahlt'])->default('offen');
            $table->text('notizen')->nullable();
            $table->timestamps();
            
            $table->foreign('arbeitskraft_id')->references('id')->on('arbeitskraefte')->onDelete('cascade');
            $table->index(['arbeitskraft_id', 'arbeitstag']);
        });

        // Tasks/assignments (Aufgaben)
        Schema::create('aufgaben', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('betrieb_id')->index();
            $table->string('titel');
            $table->text('beschreibung')->nullable();
            $table->string('modul_kontext')->nullable()->comment('e.g., schlagkartei, ernte, keller');
            $table->uuid('related_entity_id')->nullable()->comment('UUID of related entity (Schlag, Lesegan, etc.)');
            $table->dateTime('erstellt_am');
            $table->dateTime('faellig_am')->nullable();
            $table->enum('prioritaet', ['low', 'medium', 'high', 'critical'])->default('medium');
            $table->string('zustaendig_rolle')->nullable()->comment('Role assigned (aussenwirtschaft, saisonkraft)');
            $table->enum('status', ['neu', 'zugewiesen', 'in_arbeit', 'erledigt', 'abgebrochen'])->default('neu');
            $table->timestamps();
            
            $table->foreign('betrieb_id')->references('id')->on('users')->onDelete('cascade');
        });

        // Task assignments (M:N mapping tasks to workers)
        Schema::create('aufgaben_zuordnungen', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('aufgabe_id')->index();
            $table->unsignedBigInteger('arbeitskraft_id')->index();
            $table->dateTime('zugewiesen_am');
            $table->dateTime('angenommen_am')->nullable();
            $table->dateTime('abgeschlossen_am')->nullable();
            $table->enum('prioritaet', ['low', 'medium', 'high', 'critical'])->default('medium');
            $table->enum('status', ['zugewiesen', 'angenommen', 'in_arbeit', 'erledigt', 'abgebrochen'])->default('zugewiesen');
            $table->text('notizen')->nullable();
            $table->timestamps();
            
            $table->foreign('aufgabe_id')->references('id')->on('aufgaben')->onDelete('cascade');
            $table->foreign('arbeitskraft_id')->references('id')->on('arbeitskraefte')->onDelete('cascade');
            $table->unique(['aufgabe_id', 'arbeitskraft_id'], 'uk_aufgabe_arbeiter');
        });

        // Piece-rate tracking (Akkordlohn - per box/kg harvested)
        Schema::create('akkordleistungen', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('arbeitskraft_id')->index();
            $table->unsignedBigInteger('lesetermin_id')->nullable()->comment('Link to harvest assignment');
            $table->date('leistung_datum');
            $table->decimal('menge_kisten_oder_kg', 10, 2)->comment('Boxes or kg harvested');
            $table->string('einheit')->comment('kisten or kg');
            $table->decimal('betrag_pro_einheit', 8, 2)->comment('Price per unit');
            $table->decimal('gesamtbetrag', 12, 2)->comment('Total amount earned');
            $table->text('notizen')->nullable();
            $table->timestamps();
            
            $table->foreign('arbeitskraft_id')->references('id')->on('arbeitskraefte')->onDelete('cascade');
            // Note: FK to lesetermine is added AFTER lesetermine table exists (via separate constraint migration)
        });

        // Payroll records (aggregated from zeiterfassung)
        Schema::create('lohnabrechnungen', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('arbeitskraft_id')->index();
            $table->unsignedBigInteger('betrieb_id')->index();
            $table->integer('abrechnungsmonat');
            $table->integer('abrechnungsjahr');
            $table->decimal('gesamtstunden', 10, 2)->comment('Total hours worked');
            $table->decimal('stundenlohn_euro', 8, 2)->nullable()->comment('Hourly rate');
            $table->decimal('grundlohn_euro', 12, 2)->nullable()->comment('Base salary for hours');
            $table->decimal('akkordlohn_euro', 12, 2)->default(0)->comment('Piece-rate earnings');
            $table->decimal('bonuszulage_euro', 12, 2)->default(0)->comment('Bonus/allowances');
            $table->decimal('abzuege_euro', 12, 2)->default(0)->comment('Deductions');
            $table->decimal('nettoauszahlung_euro', 12, 2)->comment('Net payment');
            $table->enum('status', ['entwurf', 'freigegeben', 'bezahlt', 'storniert'])->default('entwurf');
            $table->dateTime('bezahlungsdatum')->nullable();
            $table->string('zahlungsart')->nullable()->comment('bank_transfer, cash, etc.');
            $table->text('notizen')->nullable();
            $table->timestamps();
            
            $table->foreign('arbeitskraft_id')->references('id')->on('arbeitskraefte')->onDelete('restrict');
            $table->foreign('betrieb_id')->references('id')->on('users')->onDelete('cascade');
            $table->unique(['arbeitskraft_id', 'abrechnungsmonat', 'abrechnungsjahr'], 'uk_lohn_arbeiter_monat');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('lohnabrechnungen');
        Schema::dropIfExists('akkordleistungen');
        Schema::dropIfExists('aufgaben_zuordnungen');
        Schema::dropIfExists('aufgaben');
        Schema::dropIfExists('zeiterfassungen');
        Schema::dropIfExists('arbeitskraefte');
    }
};
