<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('betriebseinstellungen', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('betrieb_id')->unique();
            $table->boolean('vier_augen_kataster')->default(false); // Der optionale Schalter!
            $table->timestamps();
        });
    }


    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('betriebseinstellungs');
    }
};
