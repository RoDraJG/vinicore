<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\ErntKampagne;
use App\Models\Lesegan;
use Illuminate\Http\JsonResponse;

class ErnteController extends Controller
{
    public function kampagnen(): JsonResponse
    {
        $kampagnen = ErntKampagne::query()
            ->with('lesegaenge')
            ->latest('jahr')
            ->get();

        return response()->json([
            'data' => $kampagnen,
        ]);
    }

    public function lesegaenge(): JsonResponse
    {
        $lesegaenge = Lesegan::query()
            ->with(['kampagne', 'lesetermine'])
            ->latest('lesedatum')
            ->get();

        return response()->json([
            'data' => $lesegaenge,
        ]);
    }
}
