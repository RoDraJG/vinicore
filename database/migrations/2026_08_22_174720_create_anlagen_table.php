<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        // Create anlagen table (Biological vine planting units)
        Schema::create('anlagen', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('schlag_id')->index();
            $table->string('name')->nullable()->comment('Anlage designator');
            $table->enum('plan_status', ['geplant', 'aktiv', 'archived'])->default('aktiv');
            
            // Edit locking (pessimistic)
            $table->unsignedBigInteger('locked_by_user_id')->nullable();
            $table->dateTime('locked_until')->nullable();
            
            // Vineyard planting parameters (in cm)
            $table->integer('vorgewende_start_cm')->nullable()->comment('Start margin');
            $table->integer('vorgewende_ende_cm')->nullable()->comment('End margin');
            $table->integer('randabstand_links_cm')->nullable()->comment('Left border distance');
            $table->integer('randabstand_rechts_cm')->nullable()->comment('Right border distance');
            $table->integer('abstand_anker_endpfahl_cm')->nullable()->comment('Anchor to end stake distance');
            $table->integer('abstand_endpfahl_rebe_cm')->nullable()->comment('End stake to vine distance');
            $table->integer('ziel_gassenbreite_cm')->nullable()->comment('Target row width');
            $table->integer('stockabstand_cm')->nullable()->comment('Vine spacing');
            $table->integer('reihenpfahl_abstand_cm')->nullable()->comment('Row stake distance');
            
            $table->timestamps();
            
            $table->foreign('schlag_id')->references('id')->on('schlaege')->onDelete('cascade');
            $table->foreign('locked_by_user_id')->references('id')->on('users')->onDelete('set null');
        });

        // Create parzelle_anlage pivot table (Many-to-many relationship)
        Schema::create('parzelle_anlage', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('anlage_id')->index();
            $table->uuid('parzelle_uuid')->index();
            
            // Pivot columns
            $table->decimal('anteil_prozent', 5, 2)->default(100)->comment('Percentage of parcel used');
            $table->boolean('ist_geplant')->default(false)->comment('Is this planned allocation?');
            $table->date('gerodet_am')->nullable()->comment('Removal/uprooting date');
            $table->string('pflanz_richtung')->nullable()->comment('Planting direction');
            
            $table->timestamps();
            
            $table->foreign('anlage_id')->references('id')->on('anlagen')->onDelete('cascade');
            $table->foreign('parzelle_uuid')->references('parzelle_uuid')->on('parzellen')->onDelete('cascade');
            $table->unique(['anlage_id', 'parzelle_uuid']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('parzelle_anlage');
        Schema::dropIfExists('anlagen');
    }
};
