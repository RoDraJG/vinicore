<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Führt die Migration aus.
     */
    public function up(): void
    {
        Schema::table('parzellen', function (Blueprint $table) {
            // 🚀 CORE-FIX: Fügt das neue Besitzverhältnis direkt nach der amtlichen Fläche ein!
            $table->enum('besitz_status', ['eigentum', 'gepachtet', 'verpachtet'])
                  ->default('eigentum')
                  ->after('amtliche_flaeche_m2');
        });
    }

    /**
     * Macht die Migration rückgängig.
     */
    public function down(): void
    {
        Schema::table('parzellen', function (Blueprint $table) {
            // Löscht die Spalte sauber, falls die Migration zurückgerollt wird
            $table->dropColumn('besitz_status');
        });
    }
};

