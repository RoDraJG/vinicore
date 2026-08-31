<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Arbeitskraft;
use App\Models\Zeiterfassung;
use Illuminate\Http\JsonResponse;

class PersonalController extends Controller
{
    public function arbeitskraefte(): JsonResponse
    {
        $arbeitskraefte = Arbeitskraft::query()->latest()->get();

        return response()->json([
            'data' => $arbeitskraefte,
        ]);
    }

    public function zeiterfassungen(): JsonResponse
    {
        $zeiterfassungen = Zeiterfassung::query()
            ->with('arbeitskraft')
            ->latest('arbeitstag')
            ->get();

        return response()->json([
            'data' => $zeiterfassungen,
        ]);
    }
}
