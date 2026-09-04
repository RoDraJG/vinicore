<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('crm_einstellungen', function (Blueprint $table) {
            // 1. 🗑️ Den alten, blockierenden Unique-Index restlos löschen
            $table->dropUnique('crm_einstellungen_typ_code_unique');
            
            // 2. 🎯 Einen normalen, flexiblen Index setzen, der Historien-Duplikate erlaubt
            $table->index(['typ', 'code'], 'crm_einstellungen_typ_code_index');
        });
    }

    public function down(): void
    {
        Schema::table('crm_einstellungen', function (Blueprint $table) {
            $table->dropIndex('crm_einstellungen_typ_code_index');
            $table->unique(['typ', 'code'], 'crm_einstellungen_typ_code_unique');
        });
    }
};
