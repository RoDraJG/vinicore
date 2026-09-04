<?php

namespace App\Modules\CRM\Providers;

use Illuminate\Support\ServiceProvider;
use App\Core\Interface\RegistersNummernkreise;
use Illuminate\Support\Facades\Event;

class CRMServiceProvider extends ServiceProvider implements RegistersNummernkreise
{
    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Falls du hier Sichten (Views) oder Routen lädst, lass diese Zeilen einfach drin:
        if (is_dir(__DIR__ . '/../Views')) {
            $this->loadViewsFrom(__DIR__ . '/../Views', 'CRM');
        }

        // 🎯 FIX: Das kaufmännische Und (&$sammler) zwingt Laravel, das reale Array im Controller zu befüllen!
        Event::listen('vinicore.collect_nummernkreise', function (&$sammler) {
            if (is_array($sammler)) {
                $sammler['CRM (Kundenverwaltung)'] = $this->getNummernkreise();
            }
        });
    }

    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Das CRM-Modul definiert hier autonom seine eigenen Nummernkreise
     */
    public function getNummernkreise(): array
    {
        return [
            'kunde' => [
                'label' => 'Nächste freie Kundennummer',
                'default' => '10000'
            ],
            'lieferant' => [
                'label' => 'Nächste freie Lieferantennummer',
                'default' => '50000'
            ]
        ];
    }
}
