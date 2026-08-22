<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('parzellen', function (Blueprint $table) {
            // 🚀 ARCHITEKTUR-FIX: Verknüpft das Flurstück untrennbar mit dem ausführenden Benutzer
            $table->foreignId('user_id')
                  ->nullable()
                  ->after('parzelle_uuid')
                  ->constrained('users')
                  ->onDelete('set null')
                  ->comment('Der historisierende Winzer/Nutzer aus der Auth-Session');
        });
    }

    public function down(): void
    {
        Schema::table('parzellen', function (Blueprint $table) {
            $table->dropForeign(['user_id']);
            $table->dropColumn('user_id');
        });
    }
};
