<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('betriebsdaten', function (Blueprint $table) {
            $table->id();
            $table->string('weingut_name')->default('Mein Vinicore Weingut');
            $table->string('gemeinde')->default('Wintrich');
            $table->decimal('lat', 10, 8)->default(49.82860000); // Breitengrad für Karte
            $table->decimal('lng', 11, 8)->default(6.94580000);  // Längengrad für Karte
            $table->string('strasse_nr')->nullable();
            $table->string('plz', 10)->nullable();
            $table->timestamps();
        });

        // Wir fügen direkt bei der Erstellung den ersten Standard-Datensatz ein!
        DB::table('betriebsdaten')->insert([
            'weingut_name' => 'Weinbau-Zentrale',
            'gemeinde'     => 'Wintrich',
            'lat'          => 50.0000,
            'lng'          => 7.0000,
            'created_at'   => now(),
            'updated_at'   => now()
        ]);
    }

    public function down(): void {
        Schema::dropIfExists('betriebsdaten');
    }
};
