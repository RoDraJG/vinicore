<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Führt die Migration aus.
     */
    public function up(): void 
    {
        Schema::create('betrieb_user', function (Blueprint $table) {
            $table->id();
            
            // 🛡️ FK 1: Verknüpfung zum registrierten Winzer-User
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            
            // 🛡️ FK 2: Verknüpfung zu deiner spezifischen 'betriebsdaten'-Tabelle!
            $table->foreignId('betrieb_id')->constrained('betriebsdaten')->onDelete('cascade');
            
            $table->timestamps();
        });
    }

    /**
     * Macht die Migration rückgängig.
     */
    public function down(): void 
    {
        Schema::dropIfExists('betrieb_user');
    }
};
