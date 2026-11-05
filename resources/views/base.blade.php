<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Gestion') - Votre Atelier</title>

    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr@4.6.13/dist/flatpickr.min.css">
    <script src="https://cdn.jsdelivr.net/npm/flatpickr@4.6.13/dist/flatpickr.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/flatpickr@4.6.13/dist/l10n/fr.js"></script>
    <script src="https://cdn.tailwindcss.com"></script>
    <!-- CSS de Bootstrap 5 -->
<link href="https://jsdelivr.net" rel="stylesheet">

<!-- JS de Bootstrap 5 (Requis pour faire fonctionner le modal !) -->
<script src="https://jsdelivr.net" defer></script>
    <script>
        tailwind.config = {
            theme: { extend: {
                colors: {
                    atelier: { navy: '#12263f', blue: '#3478e5', gold: '#e7b83f', ink: '#172b4d', muted: '#75849b', canvas: '#f5f7fa' }
                },
                boxShadow: { soft: '0 8px 24px rgba(18, 38, 63, .07)' }
            }}
        };
    </script>
    <script src="https://unpkg.com/lucide@latest"></script>
    <style>
        html { scroll-behavior: smooth; }
        body { font-family: Inter, ui-sans-serif, system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", sans-serif; }
        .sidebar-link { transition: background .2s ease, color .2s ease; }
        .sidebar-link.active { background: #203f6c; border-left: 4px solid #e7b83f; padding-left: 10px; color: #fff; }
        .sidebar-link.active [data-lucide] { color: #e7b83f; }
        [hidden] { display: none !important; }
    </style>
    @stack('styles')
</head>
<body class="bg-atelier-canvas text-slate-700 antialiased">
    @php
        // Les noms de routes sont testés avant leur utilisation pour éviter les exceptions.
        $formationRouteExists = \Illuminate\Support\Facades\Route::has('formation.index');
        $apprenantRouteExists = \Illuminate\Support\Facades\Route::has('apprenant.index');
        $cohorteRouteExists = \Illuminate\Support\Facades\Route::has('cohorte.index');

        $formationActive = request()->routeIs('formation.*');
        $apprenantActive = request()->routeIs('apprenant.*');
        $cohorteActive = request()->routeIs('cohorte.*');

    @endphp

    <div class="min-h-screen lg:grid lg:grid-cols-[296px_minmax(0,1fr)]">
        <aside id="sidebar" class="fixed inset-y-0 left-0 z-40 flex w-74 -translate-x-full flex-col bg-atelier-navy px-3 text-slate-200 transition-transform duration-300 lg:sticky lg:top-0 lg:h-screen lg:translate-x-0">
            <div class="flex h-27.5 shrink-0 items-center gap-3 border-b border-white/10 px-2">
                <div class="grid h-10 w-10 place-items-center rounded-full border border-atelier-gold text-xl font-serif text-atelier-gold">V</div>
                <div class="leading-tight">
                    <p class="font-serif text-lg font-bold text-white">Votre Atelier</p>
                    <p class="mt-1 text-[9px] uppercase tracking-[.24em] text-atelier-gold">Gastronomique</p>
                </div>
                <button id="closeSidebar" type="button" class="ml-auto rounded-lg p-2 hover:bg-white/10 lg:hidden" aria-label="Fermer le menu"><i data-lucide="x"></i></button>
            </div>

            <nav class="flex-1 overflow-y-auto py-6" aria-label="Navigation principale">
                <p class="mb-3 px-3 text-[10px] font-bold uppercase tracking-[.2em] text-slate-400">Opérations</p>

                <a href="{{ \Illuminate\Support\Facades\Route::has('dashboard') ? route('dashboard') : url('/') }}"
                   class="sidebar-link flex items-center gap-3 rounded-xl px-3 py-3 text-sm hover:bg-white/10 {{ request()->routeIs('dashboard') ? 'active' : '' }}"
                   @if(request()->routeIs('dashboard')) aria-current="page" @endif>
                    <i data-lucide="layout-dashboard" class="h-5 w-5"></i> Tableau de bord
                </a>

{{--                 
                <a href="{{ $sessionRouteExists ? route('session.index') : '#' }}"
                   class="sidebar-link flex items-center gap-3 rounded-xl px-3 py-3 text-sm hover:bg-white/10 {{ $sessionActive ? 'active' : '' }}"
                   @if($sessionActive) aria-current="page" @endif
                   @if(!$sessionRouteExists) aria-disabled="true" title="Crée la route session.index pour activer ce lien" @endif>
                    <i data-lucide="graduation-cap" class="h-5 w-5"></i> Session
                </a> --}}

                <a href="{{ $formationRouteExists ? route('formation.index') : '#' }}"
                   class="sidebar-link flex items-center gap-3 rounded-xl px-3 py-3 text-sm hover:bg-white/10 {{ $formationActive ? 'active' : '' }}"
                   @if($formationActive) aria-current="page" @endif
                   @if(!$formationRouteExists) aria-disabled="true" title="Crée la route formation.index pour activer ce lien" @endif>
                    <i data-lucide="graduation-cap" class="h-5 w-5"></i> Formations
                </a>

        
                <a href="{{ $cohorteRouteExists ? route('cohorte.index') : '#' }}"
                   class="sidebar-link flex items-center gap-3 rounded-xl px-3 py-3 text-sm hover:bg-white/10 {{ $cohorteActive ? 'active' : '' }}"
                   @if($cohorteActive) aria-current="page" @endif
                   @if(!$cohorteRouteExists) aria-disabled="true" title="Crée la route formation.index pour activer ce lien" @endif>
                    <i data-lucide="graduation-cap" class="h-5 w-5"></i> Cohortes
                </a>

               {{-- <a href="#" class="sidebar-link flex items-center gap-3 rounded-xl px-3 py-3 text-sm hover:bg-white/10"><i data-lucide="cooking-pot" class="h-5 w-5"></i> Productions</a>
                <a href="#" class="sidebar-link flex items-center gap-3 rounded-xl px-3 py-3 text-sm hover:bg-white/10"><i data-lucide="concierge-bell" class="h-5 w-5"></i> Prestations</a> --}}

                <a href="{{ $apprenantRouteExists ? route('apprenant.index') : '#' }}"
                   class="sidebar-link flex items-center gap-3 rounded-xl px-3 py-3 text-sm hover:bg-white/10 {{ $apprenantActive ? 'active' : '' }}"
                   @if($apprenantActive) aria-current="page" @endif>
                    <i data-lucide="users-round" class="h-5 w-5"></i> Apprenants
                </a>

                <a href="#" class="sidebar-link flex items-center gap-3 rounded-xl px-3 py-3 text-sm hover:bg-white/10"><i data-lucide="calendar-days" class="h-5 w-5"></i> Planning</a>

                <p class="mb-3 mt-7 px-3 text-[10px] font-bold uppercase tracking-[.2em] text-slate-400">Gestion</p>
                <a href="#" class="sidebar-link flex items-center gap-3 rounded-xl px-3 py-3 text-sm hover:bg-white/10">
                    <i data-lucide="boxes" class="h-5 w-5"></i>
                     Achats & stocks
                </a>
                <a href="#" class="sidebar-link flex items-center gap-3 rounded-xl px-3 py-3 text-sm hover:bg-white/10"><i data-lucide="wallet" class="h-5 w-5"></i> Finances</a>
                <a href="#" class="sidebar-link flex items-center gap-3 rounded-xl px-3 py-3 text-sm hover:bg-white/10"><i data-lucide="user-cog" class="h-5 w-5"></i> Personnel</a>
                <a href="#" class="sidebar-link flex items-center gap-3 rounded-xl px-3 py-3 text-sm hover:bg-white/10"><i data-lucide="files" class="h-5 w-5"></i> Documents</a>
                <a href="#" class="sidebar-link flex items-center gap-3 rounded-xl px-3 py-3 text-sm hover:bg-white/10"><i data-lucide="settings" class="h-5 w-5"></i> Paramètres</a>
            </nav>
            <div class="shrink-0 border-t border-white/10 px-3 py-4 text-xs text-slate-400">Votre Atelier · Administration</div>
        </aside>

        <div id="sidebarOverlay" class="fixed inset-0 z-30 bg-slate-950/50 lg:hidden" hidden></div>

        <div class="flex min-h-screen min-w-0 flex-col">
            <header class="sticky top-0 z-20 flex min-h-21 items-center justify-between gap-4 border-b border-slate-200 bg-white px-4 sm:px-7 lg:px-9">
                <div class="flex min-w-0 flex-1 items-center gap-3">
                    <button id="openSidebar" type="button" class="rounded-lg border border-slate-200 p-2 text-slate-600 hover:bg-slate-50 lg:hidden" aria-label="Ouvrir le menu"><i data-lucide="menu"></i></button>
                    <label class="flex w-full max-w-131.25 items-center gap-3 rounded-xl border border-slate-200 bg-slate-50 px-3 py-3 focus-within:border-atelier-blue">
                        <i data-lucide="search" class="h-5 w-5 shrink-0 text-slate-400"></i>
                        <input id="globalSearch" type="search" placeholder="Rechercher dans la page..." class="w-full min-w-0 bg-transparent text-sm outline-none placeholder:text-slate-400" aria-label="Recherche dans la page">
                    </label>
                </div>
                <div class="flex shrink-0 items-center gap-3 sm:gap-5">
                    <button type="button" class="relative rounded-xl border border-slate-200 p-3 text-slate-500 hover:bg-slate-50" aria-label="Notifications"><i data-lucide="bell" class="h-5 w-5"></i><span class="absolute right-2 top-2 h-1.5 w-1.5 rounded-full bg-rose-500"></span></button>
                    <div class="flex items-center gap-3">
                        <div class="grid h-10 w-10 place-items-center rounded-full bg-atelier-navy text-xs font-bold text-atelier-gold">VA</div>
                        <div class="hidden sm:block"><p class="text-sm font-semibold text-atelier-ink">Administration</p><p class="mt-0.5 text-xs text-slate-400">Votre Atelier</p></div>
                    </div>
                </div>
            </header>

            <main class="flex-1 px-4 py-7 sm:px-7 lg:px-9 lg:py-9">
                @yield('content')
            </main>

            <footer class="flex flex-col gap-2 border-t border-slate-200 bg-white px-4 py-5 text-xs text-slate-400 sm:flex-row sm:items-center sm:justify-between sm:px-9">
                <p>© {{ date('Y') }} Votre Atelier Gastronomique. Tous droits réservés.</p>
                <div class="flex gap-4"><a href="#" class="hover:text-atelier-blue">Aide</a><a href="#" class="hover:text-atelier-blue">Confidentialité</a><span>Version 1.0</span></div>
            </footer>
        </div>
    </div>

    <script>
        (() => {
            if (window.lucide) window.lucide.createIcons();

            const sidebar = document.getElementById('sidebar');
            const overlay = document.getElementById('sidebarOverlay');
            const openButton = document.getElementById('openSidebar');
            const closeButton = document.getElementById('closeSidebar');

            const setSidebarOpen = (isOpen) => {
                if (!sidebar || !overlay) return;
                sidebar.classList.toggle('-translate-x-full', !isOpen);
                overlay.hidden = !isOpen;
                document.body.classList.toggle('overflow-hidden', isOpen);
            };

            openButton?.addEventListener('click', () => setSidebarOpen(true));
            closeButton?.addEventListener('click', () => setSidebarOpen(false));
            overlay?.addEventListener('click', () => setSidebarOpen(false));
            document.addEventListener('keydown', (event) => {
                if (event.key === 'Escape') setSidebarOpen(false);
            });

            // Recherche simple dans le contenu visible de la page, sans dépendance supplémentaire.
            const searchInput = document.getElementById('globalSearch');
            searchInput?.addEventListener('keydown', (event) => {
                if (event.key !== 'Enter') return;
                const query = searchInput.value.trim().toLocaleLowerCase('fr');
                const main = document.querySelector('main');
                if (!main) return;
                const rows = main.querySelectorAll('tbody tr');
                if (!rows.length) return;
                rows.forEach((row) => {
                    row.hidden = Boolean(query) && !row.innerText.toLocaleLowerCase('fr').includes(query);
                });
            });
        })();
    </script>
    @stack('scripts')
</body>
</html>
