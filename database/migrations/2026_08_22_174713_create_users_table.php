<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('betrieb_id')->index()->comment('Zugehörigkeit zum Weinbaubetrieb');
            
            // 🎯 CORE-FIX: Einzigartiger Login-Name statt E-Mail-Zwang
            $table->string('username')->unique()->comment('Eindeutiger Login-Name im ERP');
            $table->string('name')->comment('Anzeigename des Mitarbeiters');
            
            $table->string('password');
            
            // 🛡️ RECHTSSCHRANKE: Nur der Hauptnutzer darf andere Accounts verwalten!
            $table->boolean('is_hauptnutzer')->default(false)->comment('Flag für den Betriebsadministrator');
            
            $table->json('erlaubte_gemarkungen')->nullable()->comment('Geografisches Scoping (Array von Gemarkungen)');
            $table->rememberToken();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('users');
    }
};
