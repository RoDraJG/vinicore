<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Führt die Migration aus.
     */
    public function up(): void
    {
        // 🚀 SCHUTZSCHILD: Schaltet Fremdschlüssel-Checks für den Index-Umbau kurz ab!
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');

        Schema::table('parzellen', function (Blueprint $table) {
            // 1. Löscht den alten, blockierenden Einmaligkeits-Index
            $table->dropUnique(['parzelle_uuid']); 
            
            // 2. 🛡️ REVISIONS-SCHILD: Macht den Index im Verbund mit der Version eindeutig!
            // Das erlaubt unendlich viele historische Einträge derselben UUID!
            $table->unique(['parzelle_uuid', 'version']); 
        });

        DB::statement('SET FOREIGN_KEY_CHECKS=1;');
    }

    /**
     * Macht die Migration rückgängig.
     */
    public function down(): void
    {
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');

        Schema::table('parzellen', function (Blueprint $table) {
            $table->dropUnique(['parzelle_uuid', 'version']);
            $table->unique('parzelle_uuid');
        });

        DB::statement('SET FOREIGN_KEY_CHECKS=1;');
    }
};
