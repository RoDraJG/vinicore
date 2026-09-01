<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('crm_kontakte', function (Blueprint $table) {
            // 🚚 Adress-Splittung & Logistik
            $table->string('adresszusatz')->nullable()->after('nachname');
            $table->string('liefer_adresszusatz')->nullable()->after('liefer_ort');
            $table->string('versanddienstleister', 30)->default('dhl')->after('lieferbedingungen');
            $table->string('speditions_hinweis')->nullable()->after('versanddienstleister');

            // 💳 Finanzen & DATEV
            $table->string('debitorennummer', 20)->unique()->nullable()->after('kundennummer');
            $table->string('kreditorennummer', 20)->unique()->nullable()->after('lieferantennummer');
            $table->string('steuernummer', 30)->nullable()->after('ust_id');
            $table->boolean('ist_steuerbefreit')->default(false)->after('steuernummer');
            $table->string('steuerbefreiung_grund')->nullable()->after('ist_steuerbefreit');
            $table->string('iban', 34)->nullable()->after('steuerbefreiung_grund');
            $table->string('bic', 11)->nullable()->after('iban');

            // 🍇 Wein-Marketing & Recht
            $table->date('geburtsdatum')->nullable()->after('nachname');
            $table->string('herkunft_kontakt', 50)->default('direkt')->after('bevorzugte_weinstilistik');
        });
    }

    public function down(): void
    {
        Schema::table('crm_kontakte', function (Blueprint $table) {
            $table->dropColumn([
                'adresszusatz', 'liefer_adresszusatz', 'versanddienstleister', 'speditions_hinweis',
                'debitorennummer', 'kreditorennummer', 'steuernummer', 'ist_steuerbefreit',
                'steuerbefreiung_grund', 'iban', 'bic', 'geburtsdatum', 'herkunft_kontakt'
            ]);
        });
    }
};
