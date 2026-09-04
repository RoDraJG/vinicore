<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('crm_kontakte_details', function (Blueprint $table) {
            $table->id();
            // Fremdschlüssel-Verbindung zu deinem Haupt-Partner (crm_kontakte)
            $table->foreignId('crm_kontakt_id')->constrained('crm_kontakte')->onDelete('cascade');
            
            $table->string('abteilung', 100)->nullable()->comment('z.B. Einkauf, Buchhaltung, Logistik, Gastro-Leitung');
            $table->string('ansprechpartner_name', 255)->nullable()->comment('Vollständiger Name der Person');
            $table->string('email', 255)->nullable();
            $table->string('telefon', 100)->nullable();
            $table->boolean('ist_hauptkontakt')->default(0)->comment('1 = Dieser Kanal bekommt standardmäßig die Belege');
            $table->text('notiz')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('crm_kontakte_details');
    }
};
