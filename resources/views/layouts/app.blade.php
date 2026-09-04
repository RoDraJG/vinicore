<!DOCTYPE html>
<html lang="de" class="theme-light">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>vinicore - Smart Wine ERP</title>
    <!-- 🎯 KORREKT (Schaltet die Live-Pipeline scharf): -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <meta name="csrf-token" content="{{ csrf_token() }}">
    <!-- Platzhalter für modulbezogene Skripte (z.B. Kataster-Karte) -->
    @yield('styles_scripts')
</head>

<body class="bg-bg-base text-text-main transition-all duration-300 font-sans antialiased h-screen w-screen overflow-hidden">

<!-- 🎯 REPARATUR 1: Mobiles Overlay liegt hier isoliert und ungebunden auf z-40 -->
<div id="sidebarOverlay" onclick="ZündeSidebarKollaps()" class="hidden fixed inset-0 bg-slate-950/40 backdrop-blur-xs z-40 md:hidden transition-opacity duration-300"></div>

<div class="flex h-full w-full overflow-hidden bg-bg-base relative">
    <!-- 📱 Mobiler Top-Header (Vollständig reaktiv an dein helles/dunkles Theme gekoppelt) -->
    <div class="md:hidden flex items-center justify-between bg-bg-surface text-text-main border-b border-border-main w-full h-14 px-4 fixed top-0 left-0 z-40 shadow-2xs">
        <div class="font-mono font-bold text-sm tracking-tight flex items-center space-x-2">
            <svg class="h-5 w-5 text-accent-brand" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 3v1m0 16v2m9-9h-1M4 12H3m15.364-6.364l-.707.707M6.343 17.657l-.707.707m12.728 0l-.707-.707M6.343 6.343l-.707-.707M14 12a2 2 0 11-4 0 2 2 0 014 0z" /></svg>
            <span>vinicore</span>
        </div>
        <button type="button" onclick="ZündeSidebarKollaps()" class="p-2 rounded-xl bg-bg-input text-text-muted hover:text-text-main border border-border-main focus:outline-none cursor-pointer transition-colors flex items-center justify-center">
            <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 12h16M4 18h16" />
            </svg>
        </button>
    </div>

    <!-- 🎯 REPARATUR 1: Blendet das normale Hauptmenü aus, wenn wir uns in den Einstellungen befinden -->
    @if(!request()->routeIs('admin.einstellungen'))
    <aside id="vinicoreSidebar" 
           class="fixed inset-y-0 left-0 md:relative transform -translate-x-full md:translate-x-0 transition-all duration-300 ease-in-out z-50 flex flex-col h-full bg-white border-r border-border-main shadow-lg md:shadow-none w-60
                  {{ (session()->has('sidebar_collapsed') && session('sidebar_collapsed')) ? 'md:w-14' : 'md:w-60' }}">
        
        <div class="h-16 flex items-center px-3 border-b border-border-main bg-bg-base/30 gap-3 flex-shrink-0 overflow-hidden">
            <button onclick="ZündeSidebarKollaps()" 
                    class="bg-bg-input hover:bg-border-main text-text-main w-8 h-8 rounded-xl border border-border-main transition-all flex items-center justify-center cursor-pointer flex-shrink-0"
                    title="Menü umschalten">
                <svg id="sidebarTriggerIcon" class="w-4 h-4 text-text-main" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                    @if(session('sidebar_collapsed'))
                        <path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 12h16M4 18h16" />
                    @else
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                    @endif
                </svg>
            </button>
            <span id="sidebarBrandText" class="font-mono font-bold tracking-tight text-text-main transition-opacity duration-200 {{ session('sidebar_collapsed') ? 'md:opacity-0 md:hidden' : 'opacity-100' }}">
                vinicore <span class="text-accent-brand font-normal text-xs">ERP</span>
            </span>
        </div>

        <div class="flex-1 overflow-y-auto p-2 space-y-4 overflow-x-hidden">
            <div class="space-y-0.5">
                <div class="section-geo-label px-2 mb-1.5 text-[9px] uppercase font-mono tracking-wider font-bold text-text-muted transition-opacity duration-200 {{ session('sidebar_collapsed') ? 'md:opacity-0 md:hidden' : 'opacity-100' }}">🏛️ Kataster (ALKIS)</div>
                <a href="/parzellen/uebersicht" class="flex items-center h-10 px-2.5 rounded-xl text-sm font-medium no-underline gap-3 transition-colors {{ Request::is('parzellen/uebersicht') ? 'bg-blue-50 text-blue-700 font-bold border border-blue-100' : 'text-text-main hover:bg-bg-input' }}">
                    <svg class="w-4 h-4 flex-shrink-0 transition-colors {{ Request::is('parzellen/uebersicht') ? 'text-blue-600' : 'text-text-muted' }}" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" /></svg>
                    <span class="sidebar-menu-text truncate transition-opacity duration-200 text-xs {{ session('sidebar_collapsed') ? 'md:opacity-0 md:hidden' : 'opacity-100' }}">Flurstücksliste</span>
                </a>
                <a href="/kataster/parzellen-karte" class="sidebar-sub-item flex items-center h-9 px-2 rounded-xl text-sm font-medium no-underline gap-3 transition-all ml-4 {{ Request::is('kataster/parzellen-karte') ? 'bg-blue-50/60 text-blue-600 font-bold border border-blue-100/40' : 'text-text-muted hover:bg-bg-input hover:text-text-main' }} {{ session('sidebar_collapsed') ? 'md:hidden' : '' }}">
                    <svg class="w-3.5 h-3.5 flex-shrink-0 transition-colors {{ Request::is('kataster/parzellen-karte') ? 'text-blue-500' : 'text-text-muted' }}" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M3.055 11H5a2 2 0 012 2v1a2 2 0 002 2 2 2 0 012 2v2.945M8 3.935V5.5A2.5 2.5 0 0010.5 8h.5a2 2 0 012 2v1a2 2 0 002 2h2.1l.6 2.4c.1.4.5.6.9.6h1.5a1 1 0 001.8-.6l.3-1.2A9 9 0 103.055 11z" /></svg>
                    <span class="sidebar-menu-text truncate text-xs">➔ Karten-Ansicht</span>
                </a>
            </div>

            @can('check-permission', 'crm.view')
            <div class="space-y-0.5 pt-1">
                <div class="section-geo-label px-2 mb-1.5 text-[9px] uppercase font-mono tracking-wider font-bold text-text-muted transition-opacity duration-200 {{ session('sidebar_collapsed') ? 'md:opacity-0 md:hidden' : 'opacity-100' }}">🏪 Handel &amp; CRM</div>
                <a href="{{ route('crm.index') }}" class="flex items-center h-10 px-2.5 rounded-xl text-sm font-medium no-underline gap-3 transition-colors {{ Request::is('crm*') ? 'bg-emerald-50 text-emerald-700 font-bold border border-emerald-100' : 'text-text-main hover:bg-bg-input' }}">
                    <svg class="w-4 h-4 flex-shrink-0 transition-colors {{ Request::is('crm*') ? 'text-emerald-600' : 'text-text-muted' }}" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" /></svg>
                    <span class="sidebar-menu-text truncate transition-opacity duration-200 text-xs {{ session('sidebar_collapsed') ? 'md:opacity-0 md:hidden' : 'opacity-100' }}">Partner-Register</span>
                </a>
            </div>
            @endcan

            <div class="space-y-1">
                <div class="section-geo-label px-2 text-[9px] uppercase font-mono tracking-wider font-bold text-text-muted transition-opacity duration-200 {{ session('sidebar_collapsed') ? 'md:opacity-0 md:hidden' : 'opacity-100' }}">🚜 Außenbetrieb</div>
                <a href="/schlaege/schlag-karte" class="flex items-center h-10 px-2.5 rounded-xl text-sm font-medium no-underline gap-3 transition-colors {{ Request::is('schlaege/*') ? 'bg-emerald-50 text-emerald-700 font-bold border border-emerald-100' : 'text-text-main hover:bg-bg-input' }}">
                    <svg class="w-4 h-4 flex-shrink-0 transition-colors {{ Request::is('schlaege/*') ? 'text-emerald-600' : 'text-text-muted' }}" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 20l-5.447-2.724A1 1 0 013 16.382V5.618a1 1 0 011.447-.894L9 7m0 13l6-3m-6 3V7m6 10l4.553 2.276A1 1 0 0021 18.382V7.618a1 1 0 00-.553-.894L15 4m0 13V4m0 4L9 7" /></svg>
                    <span class="sidebar-menu-text truncate transition-opacity duration-200 text-xs {{ session('sidebar_collapsed') ? 'md:opacity-0 md:hidden' : 'opacity-100' }}">Schläge &amp; Anlagen</span>
                </a>
            </div>

            <div class="space-y-1">
                <div class="section-geo-label px-2 text-[9px] uppercase font-mono tracking-wider font-bold text-text-muted transition-opacity duration-200 {{ session('sidebar_collapsed') ? 'md:opacity-0 md:hidden' : 'opacity-100' }}">🍷 Innenbetrieb</div>
                <a href="#" class="flex items-center h-10 px-2.5 rounded-xl text-sm font-medium no-underline gap-3 text-text-main/40 hover:bg-bg-input cursor-not-allowed opacity-50">
                    <svg class="w-4 h-4 flex-shrink-0 text-text-muted" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M19.428 15.428a2 2 0 00-1.022-.547l-2.387-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L6.05 15.21a2 2 0 00-1.806.547M8 4h8l-1 1v5.172a2 2 0 00.586 1.414l5 5c1.26 1.26.367 3.414-1.415 3.414H4.828c-1.782 0-2.674-2.154-1.414-3.414l5-5A2 2 0 009 10.172V5L8 4z" /></svg>
                    <span class="sidebar-menu-text truncate text-xs {{ session('sidebar_collapsed') ? 'md:opacity-0 md:hidden' : 'opacity-100' }}">Kellerbuch &amp; Analysen</span>
                </a>
            </div>
        </div>

        <div id="sidebarFooter" class="p-3 border-t border-border-main bg-bg-base/20 text-center font-sans text-[9px] text-text-muted font-normal tracking-wide truncate flex-shrink-0 transition-opacity duration-200 {{ session('sidebar_collapsed') ? 'md:opacity-0 md:hidden' : 'opacity-100' }}">
            &copy; 2026 vinicore
        </div>
    </aside>
    @endif

    <div class="flex-1 flex flex-col min-w-0 mt-14 md:mt-0 relative transition-all duration-300 h-full overflow-hidden">
        
        <!-- 🏢 Der obere Kontroll-Header (Vollständig repariert und abgesichert) -->
        <header class="h-16 bg-white border-b border-border-main flex items-center px-4 justify-between flex-shrink-0 z-30 shadow-2xs">
            <div class="flex items-center space-x-2">
                <svg class="w-3.5 h-3.5 text-text-muted" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M8 9l3 3-3 3m5 0h3M5 20h14a2 2 0 002-2V6a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" /></svg>
                <div class="text-[11px] font-mono tracking-wide text-text-muted">
                    vinicore_core // <span class="text-text-main font-semibold">erp_cockpit</span>
                </div>
            </div>

            <div class="flex items-center space-x-2">
                <!-- 🎯 REPARATUR 2: Zentraler, geschützter Absprung-Link in den neuen Einstellungs-Hub -->
                @if(auth()->user() && (Gate::check('admin.view') || Gate::check('nummernkreise bearbeiten') || Gate::check('betrieb verwalten')))
                    <a href="{{ route('admin.einstellungen', ['tab' => 'nummernkreise']) }}" class="flex items-center space-x-1.5 bg-bg-input hover:bg-border-main text-text-main px-3 py-1.5 rounded-xl border border-border-main transition-colors font-medium text-xs no-underline shadow-3xs cursor-pointer">
                        <svg class="w-3.5 h-3.5 text-text-muted" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z" /><circle cx="12" cy="12" r="3" /></svg>
                        <span>Systemeinstellungen</span>
                    </a>
                @endif

                <div class="h-5 w-px bg-border-main hidden sm:block"></div>

                <!-- Rechte Flanke: Authentifizierungs-Ebene -->
                @auth
                    <div class="flex items-center space-x-1 bg-slate-50 border border-slate-200 p-0.5 rounded-xl">
                        <button onclick="window.location.href='/user/profile'" class="flex items-center space-x-1.5 bg-white hover:bg-bg-input text-text-main px-3 py-1 rounded-lg transition-colors font-semibold text-xs cursor-pointer shadow-3xs">
                            <svg class="w-3.5 h-3.5 text-emerald-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" /></svg>
                            <span>{{ auth()->user()->name ?? 'Winzer' }}</span>
                        </button>

                        <form action="{{ route('logout') }}" method="POST" class="inline m-0">
                            @csrf
                            <button type="submit" class="p-1 text-red-500 hover:text-red-700 hover:bg-red-50 rounded-lg cursor-pointer transition-colors text-xs font-mono border-0 bg-transparent" title="Sitzung beenden">✕ Off</button>
                        </form>
                    </div>
                @else
                    <!-- Fallback-Button, falls keine Sitzung aktiv ist -->
                    <a href="/login" class="flex items-center bg-slate-900 hover:bg-slate-800 text-white px-3 py-1.5 rounded-xl text-xs font-mono no-underline shadow-3xs">🔑 Login</a>
                @endauth
            </div>
        </header>
        <!-- 🎯 GLOBALER TOAST-INJEKTOR: Fängt Erfolge UND scharfe Validierungsfehler ab -->
        @if(session('success') || session('status'))
            <div id="vinicoreToast" class="fixed top-20 right-6 z-50 flex items-center gap-3 bg-slate-900 border border-slate-800 text-white text-xs font-mono px-4 py-3 rounded-xl shadow-xl transition-all duration-300 transform translate-y-2 opacity-0">
                <span class="text-emerald-500 font-sans text-sm">✅</span>
                <div class="flex-1 font-medium tracking-wide">
                    {{ session('success') ?? session('status') }}
                </div>
                <button type="button" onclick="SchliesseToast()" class="text-slate-400 hover:text-white font-bold font-sans text-sm bg-transparent border-0 cursor-pointer pl-2">&times;</button>
            </div>
        @endif

        <!-- 🚨 NEU: DER FEHLER-TOAST (Zeigt Validierungsfehler sofort als edle Warnung an) -->
        @if($errors->any())
            <div id="vinicoreToast" class="fixed top-20 right-6 z-50 flex items-center gap-3 bg-red-950 border border-red-800 text-white text-xs font-mono px-4 py-3 rounded-xl shadow-xl transition-all duration-300 transform translate-y-2 opacity-0">
                <span class="text-red-500 font-sans text-sm">⚠️</span>
                <div class="flex-1 font-medium tracking-wide">
                    @foreach($errors->all() as $error)
                        <div>• {{ $error }}</div>
                    @endforeach
                </div>
                <button type="button" onclick="SchliesseToast()" class="text-red-400 hover:text-white font-bold font-sans text-sm bg-transparent border-0 cursor-pointer pl-2">&times;</button>
            </div>
        @endif

        <!-- 🔒 DIE INHALTS-FLEXBOX -->
        <div class="flex-1 h-[calc(100vh-64px)] w-full overflow-hidden p-3 md:p-4 bg-bg-base flex gap-3">
            <main class="flex-1 h-full min-w-0 overflow-hidden relative flex flex-col">
                @yield('content')
            </main>

            <!-- Der vinicore System-Inspektor (Rechte Sidebar) -->
            <aside id="globalVinicoreInspektor" 
                   class="hidden w-0 h-full bg-white rounded-xl border border-slate-200 shadow-sm overflow-hidden flex flex-col flex-shrink-0 transition-all duration-300 ease-in-out">
                <div class="p-3 border-b border-slate-100 flex justify-between items-center bg-slate-50/50 flex-shrink-0">
                    <h4 id="globalInspektorTitel" class="font-bold text-slate-800 font-mono tracking-wider text-[11px] uppercase flex items-center gap-1.5">📋 System-Inspektor</h4>
                    <button onclick="SchliesseGlobalenInspektor()" class="text-slate-400 hover:text-slate-600 font-bold text-base cursor-pointer px-1">&times;</button>
                </div>
                <div id="globalInspektorBody" class="flex-1 p-3 overflow-y-auto min-h-0 text-sm">
                    @yield('inspektor_content')
                </div>
            </aside>

        </div>
    </div>

</div>

<!-- 🧠 KORRIGIERTE SIDEBAR- & RESPONSIVE-LOGIK -->
<script>
    function ZündeSidebarKollaps() {
        const sidebar = document.getElementById('vinicoreSidebar');
        const overlay = document.getElementById('sidebarOverlay');
        const brandText = document.getElementById('sidebarBrandText');
        const footer = document.getElementById('sidebarFooter');

        if (!sidebar) return;
        const token = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');

        // 📱 REAKTIVES MOBIL-HANDLING (Wechselt sauber zwischen den mobilen Sichtbarkeits-Klassen)
        if (window.innerWidth < 768) {
            const istAusgeblendet = sidebar.classList.contains('-translate-x-full');
            if (istAusgeblendet) {
                sidebar.classList.remove('-translate-x-full');
                sidebar.classList.add('translate-x-0');
                if (overlay) overlay.classList.remove('hidden');
            } else {
                sidebar.classList.add('-translate-x-full');
                sidebar.classList.remove('translate-x-0');
                if (overlay) overlay.classList.add('hidden');
            }
            return;
        }

        // 🖥️ DESKTOP-COLLAPSE LOGIK (Mit md: Präfixen isoliert, um mobiles Lappen zu verhindern)
        const istBreit = sidebar.classList.contains('md:w-60');

        if (istBreit) {
            sidebar.classList.remove('md:w-60');
            sidebar.classList.add('md:w-14');
            
            if (brandText) brandText.classList.add('md:opacity-0', 'md:hidden');
            if (footer) footer.classList.add('md:opacity-0', 'md:hidden');
        } else {
            sidebar.classList.remove('md:w-14');
            sidebar.classList.add('md:w-60');
            
            if (brandText) brandText.classList.remove('md:opacity-0', 'md:hidden');
            if (footer) footer.classList.remove('md:opacity-0', 'md:hidden');
        }
    }
    // 🎯 GLOBALER ASYNC-EINTRAG: Macht den System-Inspektor für alle ERP-Module steuerbar
    window.OeffneGlobalenInspektor = function(titel, htmlInhalt, breiteKlasse = 'w-72') {
        const container = document.getElementById('globalVinicoreInspektor');
        const titelElement = document.getElementById('globalInspektorTitel');
        const bodyElement = document.getElementById('globalInspektorBody');

        if (!container || !bodyElement) return;

        if (titelElement && titel) titelElement.innerHTML = titel;
        bodyElement.innerHTML = htmlInhalt;

        // Bereinigt alte Breiten-Klassen und blendet das Element ein
        container.className = container.className.replace(/w-\d+/g, '').trim();
        container.classList.remove('hidden');
        
        setTimeout(() => {
            container.classList.add(breiteKlasse, 'border-l');
            if (typeof map !== 'undefined' && map !== null) { map.invalidateSize({ animate: true }); }
        }, 10);
    };

    window.SchliesseGlobalenInspektor = function() {
        const container = document.getElementById('globalVinicoreInspektor');
        if (!container) return;

        container.className = container.className.replace(/w-\d+/g, '').trim();
        container.classList.add('hidden');

        if (typeof map !== 'undefined' && map !== null) {
            setTimeout(() => { map.invalidateSize({ animate: true }); }, 10);
        }
    };
    /**
     * 🎯 GLOBALER TOAST-PARSER: Schaltet die Animationen systemweit scharf
     */
    function InitialisiereGlobalenToast() {
        const toast = document.getElementById('vinicoreToast');
        if (!toast) return;

        // Flüssiges Hereingleiten
        setTimeout(() => {
            toast.classList.remove('translate-y-2', 'opacity-0');
            toast.classList.add('translate-y-0', 'opacity-100');
        }, 50);

        // Automatisches Zerstören nach 4 Sekunden
        setTimeout(() => {
            SchliesseToast();
        }, 4000);
    }

    function SchliesseToast() {
        const toast = document.getElementById('vinicoreToast');
        if (!toast) return;

        toast.classList.remove('translate-y-0', 'opacity-100');
        toast.classList.add('translate-y-[-10px]', 'opacity-0');
        
        setTimeout(() => { toast.remove(); }, 400);
    }

    // Klinkt sich sauber in dein bestehendes DOMContentLoaded-Event ein
    document.addEventListener('DOMContentLoaded', function() {
        // Deine bisherige Sidebar- & Inspektorlogik bleibt hier unberührt...
        
        // Feuert das globale Feedback-System an
        InitialisiereGlobalenToast();
    });

</script>
</body>
</html>
