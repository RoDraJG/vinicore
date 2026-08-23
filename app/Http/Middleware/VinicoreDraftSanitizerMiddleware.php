<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class VinicoreDraftSanitizerMiddleware
{
    /**
     * 🛰️ GLOBALER VINICORE ROUTEN-WÄCHTER
     * Löscht schwebende Vertrags-Entwürfe sofort, wenn der Benutzer das Modul verlässt! [4]
     */
    public function handle(Request $request, Closure $next): Response
    {
        $aktuelleRoute = $request->path();

        // 🛡️ SICHERHEITS-SCHRÄNKE: In diesen Pfaden bleibt der Entwurf im RAM geschützt [2]
        $erlaubtePfade = [
            'kataster',
            'api/kataster',
            'finanzen/vertrag-anlegen',
            'api/geojson'
        ];

        // Prüft, ob der Klick außerhalb des Kataster-Verbunds liegt
        $verlaesstModul = true;
        foreach ($erlaubtePfade as $pfad) {
            if (str_starts_with($aktuelleRoute, $pfad) || $aktuelleRoute === '/') {
                $verlaesstModul = false;
                break;
            }
        }

        // 💥 DER LÖSCH-BLITZ: Wenn er wegnaktiviert, fegen wir den Server-RAM sofort leer! [4]
        if ($verlaesstModul) {
            if (session()->has('vinicore_schwebe_vertrag')) {
                session()->forget('vinicore_schwebe_vertrag');
            }
        }

        return $next($request);
    }
}
