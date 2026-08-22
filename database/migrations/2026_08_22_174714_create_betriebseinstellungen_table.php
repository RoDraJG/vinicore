<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('betriebseinstellungen', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('betrieb_id')->unique();
            $table->boolean('vier_augen_kataster')->default(false)->comment('Schalter für optionale Admin-Freigabe');
            $table->string('standard_allokation', 20)->default('modell_a')->comment('Standard-Kostenschlüssel: modell_a (Hektar) oder modell_c (Pauschal)');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('betriebseinstellungen');
    }
};
