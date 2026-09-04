<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('crm_einstellungen', function (Blueprint $table) {
            if (!Schema::hasColumn('crm_einstellungen', 'typ')) {
                $table->string('typ', 50)->index()->nullable();
            }
            
            // 🎯 Das neue Muster-Getriebe für flexible Formate (z.B. MM{JJJJ}{ZAEHLER})
            $table->string('modul_key', 50)->index()->nullable();
            $table->string('kreis_key', 50)->index()->nullable();
            $table->string('muster', 100)->default('{ZAEHLER}');
            $table->bigInteger('zaehlerstand')->default(0);
            $table->integer('fuehrende_nullen')->default(0);
            
            // 🎯 Gültigkeitszeiträume für die Historisierung / automatische Jahreswechsel
            $table->dateTime('gueltig_von')->nullable();
            $table->dateTime('gueltig_bis')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('crm_einstellungen', function (Blueprint $table) {
            $table->dropColumn(['modul_key', 'kreis_key', 'muster', 'zaehlerstand', 'fuehrende_nullen', 'gueltig_von', 'gueltig_bis']);
        });
    }
};
