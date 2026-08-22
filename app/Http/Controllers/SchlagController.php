<?php

namespace App\Http\Controllers;

use App\Models\Schlag;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;

class SchlagController extends Controller
{
    /**
     * Listet alle Schläge mitsamt der aggregierten Hektarfläche auf.
     */
    public function index(): JsonResponse
    {
        try {
            $schlaege = DB::table('schlaege')
                ->select([
                    'id',
                    'name',
                    'flaeche_ha',
                    'bodenart',
                    'letzte_bodenprobe',
                    'created_at'
                ])
                ->get();

            return response()->json(['success' => true, 'data' => $schlaege]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    /**
     * Erstellt einen neuen administrativen Schlag.
     */
    public function store(Request $request): JsonResponse
    {
        $request->validate(['name' => 'required|string|max:255']);
        try {
            $schlag = Schlag::create([
                'name' => $request->input('name'),
                'flaeche_ha' => floatval($request->input('flaeche_ha', 0)),
                'bodenart' => $request->input('bodenart'),
                'letzte_bodenprobe' => $request->input('letzte_bodenprobe')
            ]);
            return response()->json(['success' => true, 'data' => $schlag], 201);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    /**
     * Zeigt die Einzeldaten eines Schlags.
     */
    public function show(int $id): JsonResponse
    {
        try {
            $schlag = Schlag::with('anlagen')->find($id);
            if (!$schlag) return response()->json(['success' => false, 'message' => 'Schlag fehlt.'], 404);
            return response()->json(['success' => true, 'data' => $schlag]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    /**
     * Aktualisiert die Basisdaten des Schlags.
     */
    public function update(Request $request, int $id): JsonResponse
    {
        try {
            $schlag = Schlag::find($id);
            if (!$schlag) return response()->json(['success' => false, 'message' => 'Schlag fehlt.'], 404);

            $schlag->update([
                'name' => $request->input('name'),
                'flaeche_ha' => floatval($request->input('flaeche_ha', $schlag->flaeche_ha)),
                'bodenart' => $request->input('bodenart'),
                'letzte_bodenprobe' => $request->input('letzte_bodenprobe')
            ]);

            return response()->json(['success' => true, 'message' => 'Schlag-Stammdaten aktualisiert.']);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }
}
