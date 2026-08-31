<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Gaarfass;
use App\Models\Gaarprozess;
use Illuminate\Http\JsonResponse;

class KellerwirtschaftController extends Controller
{
    public function gaarfaesser(): JsonResponse
    {
        $faesser = Gaarfass::query()->with('gaarprozesse')->latest()->get();

        return response()->json([
            'data' => $faesser,
        ]);
    }

    public function gaarprozesse(): JsonResponse
    {
        $prozesse = Gaarprozess::query()
            ->with(['fass', 'lesegan'])
            ->latest('start_datum')
            ->get();

        return response()->json([
            'data' => $prozesse,
        ]);
    }
}
