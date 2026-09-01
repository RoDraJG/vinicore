<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('crm_kontakte', function (Blueprint $table) {
            $table->id();
            $table->uuid('kontakt_uuid')->unique();
            $table->unsignedBigInteger('betrieb_id')->default(1); // Mandantenfähigkeit
            
            // 🏢 Kerndaten des Partners
            $table->string('firma')->nullable()->comment('Firmenname bei B2B-Kunden/Lieferanten');
            $table->string('nachname');
            $table->string('vorname')->nullable();
            $table->string('kundennummer')->unique()->nullable();
            $table->string('lieferantennummer')->unique()->nullable();

            // 🚦 DIE STEUERUNGS-FLAGS (Kombiniert in einer Tabelle, getrennt ansteuerbar!)
            $table->boolean('ist_kunde')->default(true);
            $table->boolean('ist_lieferant')->default(false);
            $table->string('kunden_kategorie', 20)->default('privat')->comment('privat, gastro, handel');

            // 📞 Kommunikation & Adresse
            $table->string('email')->nullable();
            $table->string('telefon')->nullable();
            $table->string('strasse_nr')->nullable();
            $table->string('plz', 10)->nullable();
            $table->string('ort')->nullable();
            $table->string('land', 3)->default('DEU');
            
            // 💰 Kaufmännische Parameter
            $table->string('ust_id', 20)->nullable()->comment('USt-IdNr. für B2B/Handel');
            $table->integer('standard_zahlungsziel_tage')->default(14);
            $table->decimal('individueller_rabatt_prozent', 5, 2)->default(0.00);
            
            $table->text('notizen')->nullable();
            $table->timestamps();
            $table->softDeletes(); // Schutz vor versehentlichem Löschen

            // Indexierung für maximale Datenbank-Performance
            $table->index(['betrieb_id', 'ist_kunde', 'ist_lieferant']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('crm_kontakte');
    }
};
