<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('crm_einstellungen', function (Blueprint $table) {
            $table->id();
            $table->string('typ', 50)->index()->comment('segment, steuerzone, incoterm, logistiker, stilistik, kanal');
            $table->string('code', 50)->index()->comment('z.B. dhl, gastro, trocken');
            $table->string('wert', 150)->comment('Der Anzeigename im Dropdown');
            $table->integer('sortierung')->default(0);
            $table->timestamps();
            
            // Verhindert doppelte Einträge pro Typ
            $table->unique(['typ', 'code']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('crm_einstellungen');
    }
};
