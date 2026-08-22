<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>vinicore ERP - Anmeldung</title>
    @vite(['resources/css/app.css'])
</head>
<body class="bg-slate-50 font-sans antialiased text-slate-700 flex items-center justify-center min-h-screen p-4">

    <div class="w-full max-w-md bg-white p-6 rounded-2xl border border-slate-200 shadow-sm space-y-6">
        
        <!-- Header -->
        <div class="text-center space-y-1">
            <span class="text-3xl">🍇</span>
            <h1 class="text-lg font-bold text-slate-900 tracking-tight">Anmeldung am Betrieb</h1>
            <p class="text-xs text-slate-500">Willkommen zurück bei vinicore ERP</p>
        </div>

        <!-- Fehleranzeige -->
        @if ($errors->any())
            <div class="p-3 bg-red-50 border border-red-100 rounded-xl text-xs text-red-600 font-medium">
                @foreach ($errors->all() as $error)
                    <div>⚠️ {{ $error }}</div>
                @endforeach
            </div>
        @endif

        <!-- Formular (Mündet in die Breeze-Anmeldung) -->
        <form method="POST" action="{{ route('login') }}" class="space-y-4">
            @csrf

            <!-- 🚀 DESIGN-REPARATUR: Nutzt jetzt 'username' statt E-Mail -->
            <div class="space-y-1">
                <label for="username" class="text-[10px] uppercase font-mono tracking-wider font-bold text-slate-400">Nutzername</label>
                <input type="text" id="username" name="username" value="{{ old('username') }}" required autofocus class="w-full border border-slate-200 rounded-xl px-3 py-2 text-sm focus:outline-none focus:ring-1 focus:ring-emerald-500 font-medium bg-slate-50/50">
            </div>


            <div class="space-y-1">
                <label for="password" class="text-[10px] uppercase font-mono tracking-wider font-bold text-slate-400">Passwort</label>
                <input type="password" id="password" name="password" required class="w-full border border-slate-200 rounded-xl px-3 py-2 text-sm focus:outline-none focus:ring-1 focus:ring-emerald-500 font-medium bg-slate-50/50">
            </div>

            <!-- 🚀 REPARATUR: Der 'Konto erstellen'-Link wurde gelöscht, da das System geschlossen ist! -->
            <div class="flex items-center justify-between text-xs pt-1">
                <label class="flex items-center space-x-2 font-medium cursor-pointer">
                    <input type="checkbox" name="remember" class="rounded border-slate-300 text-emerald-600 focus:ring-emerald-500">
                    <span>Angemeldet bleiben</span>
                </label>
                
                <!-- Reiner Info-Text statt eines kaputten Links -->
                <span class="text-slate-400 font-medium">Geschlossenes System</span>
            </div>

            <button type="submit" class="w-full bg-slate-900 hover:bg-slate-800 text-white font-semibold font-mono text-xs py-2.5 rounded-xl transition shadow-sm cursor-pointer mt-2">
                🔒 In das Cockpit einloggen
            </button>
        </form>

    </div>

</body>
</html>
