<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('vinicore_vertraege', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('betrieb_id')->index();
            $table->string('vertrag_nummer')->index();
            $table->string('typ', 20)->comment('kauf, pacht_aufwand, pacht_ertrag'); 
            $table->string('vertragspartner_name')->nullable();
            $table->decimal('gesamtwert', 12, 2)->default(0.00);
            $table->date('vertragsdatum')->nullable();
            $table->date('gueltig_von')->nullable();
            $table->date('gueltig_bis')->nullable();
            $table->foreignId('user_id')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('vinicore_vertraege');
    }
};
