<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Die Tabelle für die vom Admin erstellten Rollen
        Schema::create('vinicore_rollen', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique(); // z.B. "keller_chef"
            $table->string('anzeige_name');   // z.B. "Keller-Chef"
            $table->text('beschreibung')->nullable();
            $table->timestamps();
        });

        // 2. Die Tabelle für den festen System-Rechte-Katalog
        Schema::create('vinicore_berechtigungen', function (Blueprint $table) {
            $table->id();
            $table->string('slug')->unique(); // z.B. "keller_bearbeiten"
            $table->string('modul');          // z.B. "keller"
            $table->string('aktion');         // z.B. "bearbeiten" oder "ansehen"
            $table->timestamps();
        });

        // 3. Die m:n Kreuztabelle zwischen Rollen und Berechtigungen
        Schema::create('berechtigung_rolle', function (Blueprint $table) {
            $table->id();
            $table->foreignId('rolle_id')->constrained('vinicore_rollen')->onDelete('cascade');
            $table->foreignId('berechtigung_id')->constrained('vinicore_berechtigungen')->onDelete('cascade');
            $table->unique(['rolle_id', 'berechtigung_id']);
        });

        // 4. Modifikation der Standard-User-Tabelle für die Rollenbindung
        Schema::table('users', function (Blueprint $table) {
            $table->foreignId('vinicore_rolle_id')->nullable()->after('id')->constrained('vinicore_rollen')->onDelete('set null');
            $table->boolean('ist_aktiv')->default(true)->after('password');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['vinicore_rolle_id']);
            $table->dropColumn(['vinicore_rolle_id', 'ist_aktiv']);
        });
        
        Schema::dropIfExists('berechtigung_rolle');
        Schema::dropIfExists('vinicore_berechtigungen');
        Schema::dropIfExists('vinicore_rollen');
    }
};
