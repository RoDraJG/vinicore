<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('parzelle_vertrag', function (Blueprint $table) {
            $table->id();
            $table->string('parzelle_uuid')->index();
            $table->numericMorphs('vertragable'); // Verweist polymorph auf Verträge oder den LegacyContract-Dummy
            $table->decimal('zugeordneter_wert', 10, 2)->default(0.00);
            $table->integer('zugeordnete_flaeche_m2')->nullable();
            $table->foreignId('user_id')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamps();

            $table->unique(['parzelle_uuid', 'vertragable_id', 'vertragable_type'], 'unique_parzelle_vertrag');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('parzelle_vertrag');
    }
};
