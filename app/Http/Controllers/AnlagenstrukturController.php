<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;

class AnlagenstrukturController extends Controller
{
    /**
     * Liefert die Rebsorten und Materialien aus dem app/private Verzeichnis.
     */
    public function getOptionen(): JsonResponse
    {
        // KORREKTUR: Zielt nun exakt in deinen storage/app/private/ Ordner!
        $pfad = storage_path('app/private/anlagenstruktur.json');

        if (!file_exists($pfad)) {
            return response()->json([
                'success' => false,
                'message' => 'Konfigurationsdatei anlagenstruktur.json fehlt im app/private Speicher.'
            ], 404);
        }

        $roherInhalt = file_get_contents($pfad);
        $daten = json_decode($roherInhalt, true);

        return response()->json([
            'success' => true,
            'rebsorten' => $daten['rebsorten'] ?? [],
            'material_typen' => $daten['material_typen'] ?? [],
            'status_typen' => $daten['status_typen'] ?? []
        ]);
    }
}

