<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * This migration adds foreign key constraints that were deferred
     * because they reference tables created by other migrations.
     * 
     * Dependency: This must run AFTER:
     * - 2026_08_31 (PERSONAL tables with arbeitskraefte, akkordleistungen)
     * - 2026_09_01 (ERNTE tables with lesetermine)
     */
    public function up(): void
    {
        Schema::table('akkordleistungen', function (Blueprint $table) {
            $table->foreign('lesetermin_id')
                  ->references('id')
                  ->on('lesetermine')
                  ->onDelete('set null');
        });
    }

    public function down(): void
    {
        Schema::table('akkordleistungen', function (Blueprint $table) {
            $table->dropForeign(['lesetermin_id']);
        });
    }
};
