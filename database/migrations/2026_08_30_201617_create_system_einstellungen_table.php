<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('system_einstellungen', function (Blueprint $table) {
            $table->id();
            // Eindeutiger System-Key zur Abfrage im Controller
            $table->string('schluessel')->unique(); 
            // Speichert den flexiblen Wert (z.B. den HEX-Farbcode)
            $table->string('wert'); 
            $table->string('beschreibung')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('system_einstellungen');
    }
};
