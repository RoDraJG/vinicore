<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->unsignedBigInteger('betrieb_id')->nullable()->after('id');
            $table->json('erlaubte_gemarkungen')->nullable()->after('email'); // Array von Gemarkungsnamen
        });

        Schema::table('parzellen', function (Blueprint $table) {
            $table->unsignedBigInteger('betrieb_id')->nullable()->after('id');
            // 'aktiv' = im Spiegel, 'eingereicht' = wartet auf 4-Augen-Freigabe
            $table->string('freigabe_status', 20)->default('aktiv')->after('version'); 
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) { $table->dropColumn(['betrieb_id', 'erlaubte_gemarkungen']); });
        Schema::table('parzellen', function (Blueprint $table) { $table->dropColumn(['betrieb_id', 'freigabe_status']); });
    }
};
