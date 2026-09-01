<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('crm_kontakte', function (Blueprint $table) {
            // 🚚 Erweiterte Logistik (Abweichende Lieferanschrift)
            $table->string('liefer_strasse_nr')->nullable()->after('ort');
            $table->string('liefer_plz', 10)->nullable()->after('liefer_strasse_nr');
            $table->string('liefer_ort')->nullable()->after('liefer_plz');
            $table->string('liefer_land', 3)->default('DEU')->after('liefer_ort');

            // 💰 Erweiterte Fakturierung & B2B
            $table->decimal('skonto_prozent', 4, 2)->default(0.00)->after('individueller_rabatt_prozent');
            $table->integer('skonto_tage')->default(0)->after('skonto_prozent');
            $table->string('lieferbedingungen', 50)->default('ab_hof')->comment('ab_hof, frei_haus, dhl');

            // 🍷 Weinbau-Marketing-Attribute
            $table->string('bevorzugte_weinstilistik', 50)->nullable()->comment('trocken, feinherb, edelsuess, rotwein');
            $table->boolean('newsletter_erlaubt')->default(false);
        });
    }

    public function down(): void
    {
        Schema::table('crm_kontakte', function (Blueprint $table) {
            $table->dropColumn([
                'liefer_strasse_nr', 'liefer_plz', 'liefer_ort', 'liefer_land',
                'skonto_prozent', 'skonto_tage', 'lieferbedingungen',
                'bevorzugte_weinstilistik', 'newsletter_erlaubt'
            ]);
        });
    }
};
