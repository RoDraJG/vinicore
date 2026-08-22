<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('username')->unique()->index(); 
            // 🚀 CORE-FIX: Aufteilung in Vorname und Nachname für die Helferverwaltung!
            $table->string('vorname');
            $table->string('nachname');
            $table->string('email')->unique();
            $table->timestamp('email_verified_at')->nullable();
            $table->string('password');
            $table->enum('rolle', ['admin', 'mitarbeiter', 'helfer'])->default('helfer');
            $table->rememberToken();
            $table->timestamps();
        });
    }



    public function down(): void {
        Schema::dropIfExists('users');
    }
};
