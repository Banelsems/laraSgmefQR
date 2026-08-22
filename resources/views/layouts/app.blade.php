<!DOCTYPE html>
<html lang="fr" :class="{ 'dark': dark }">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'LaraSgmefQR') - Gestion des Factures</title>

    <!-- Typo Inter -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap" rel="stylesheet">
    <!-- Tailwind CSS (palette custom via config inline) -->
    <script>
      tailwind.config = {
        darkMode: 'class',
        theme: {
          extend: {
            fontFamily: { sans: ['Inter', 'sans-serif'] },
            colors: {
              primary: { DEFAULT: '#2563eb', light: '#3b82f6', dark: '#1e40af' },
              success: { DEFAULT: '#22c55e' },
              warning: { DEFAULT: '#f59e42' },
              error: { DEFAULT: '#ef4444' },
            },
          },
        },
      }
    </script>
    <script src="https://cdn.tailwindcss.com"></script>
    <!-- Heroicons (SVG) -->
    <script src="https://unpkg.com/heroicons@2.0.16/dist/heroicons.min.js"></script>
    <!-- Alpine.js -->
    <script defer src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    @stack('styles')
</head>
<body class="bg-gray-50 dark:bg-gray-900 font-sans text-gray-900 dark:text-gray-100 transition-colors duration-200"
      x-data="{ dark: localStorage.getItem('sgmef_dark') === '1', sidebarOpen: false }"
      x-init="() => { const mql = matchMedia('(prefers-color-scheme: dark)'); if (localStorage.getItem('sgmef_dark') === null) { dark = mql.matches; localStorage.setItem('sgmef_dark', dark ? '1' : '0'); } }"
      x-bind:class="dark ? 'dark bg-gray-900 text-gray-100' : 'bg-gray-50 text-gray-900'">
    <div class="min-h-screen flex">
        <!-- Sidebar — desktop fixe, mobile overlay -->
        <aside class="fixed lg:static inset-y-0 left-0 z-40 w-64 bg-white dark:bg-gray-800 shadow-lg transform transition-transform duration-200 lg:translate-x-0"
               :class="sidebarOpen ? 'translate-x-0' : '-translate-x-full lg:translate-x-0'">
            <div class="p-6 border-b border-gray-200 dark:border-gray-700">
                <h1 class="text-xl font-bold text-gray-800 dark:text-gray-100">
                    <i class="fas fa-receipt text-blue-600 mr-2"></i>
                    LaraSgmefQR
                </h1>
            </div>

            <nav class="mt-6">
                <div class="px-6 py-3">
                    <p class="text-xs font-semibold text-gray-400 dark:text-gray-500 uppercase tracking-wider">
                        Navigation
                    </p>
                </div>

                <a href="{{ route('sgmef.dashboard') }}"
                   class="flex items-center px-6 py-3 text-gray-700 dark:text-gray-300 hover:bg-blue-50 dark:hover:bg-gray-700 hover:text-blue-600 dark:hover:text-blue-400 transition-colors {{ request()->routeIs('sgmef.dashboard') ? 'bg-blue-50 dark:bg-gray-700 text-blue-600 dark:text-blue-400 border-r-2 border-blue-600' : '' }}">
                    <i class="fas fa-chart-line w-5 mr-3"></i>
                    Tableau de bord
                </a>

                <a href="{{ route('sgmef.invoices.index') }}"
                   class="flex items-center px-6 py-3 text-gray-700 dark:text-gray-300 hover:bg-blue-50 dark:hover:bg-gray-700 hover:text-blue-600 dark:hover:text-blue-400 transition-colors {{ request()->routeIs('sgmef.invoices.*') ? 'bg-blue-50 dark:bg-gray-700 text-blue-600 dark:text-blue-400 border-r-2 border-blue-600' : '' }}">
                    <i class="fas fa-file-invoice w-5 mr-3"></i>
                    Factures
                </a>

                <a href="{{ route('sgmef.config.index') }}"
                   class="flex items-center px-6 py-3 text-gray-700 dark:text-gray-300 hover:bg-blue-50 dark:hover:bg-gray-700 hover:text-blue-600 dark:hover:text-blue-400 transition-colors {{ request()->routeIs('sgmef.config.*') ? 'bg-blue-50 dark:bg-gray-700 text-blue-600 dark:text-blue-400 border-r-2 border-blue-600' : '' }}">
                    <i class="fas fa-cog w-5 mr-3"></i>
                    Configuration
                </a>
            </nav>
        </aside>

        <!-- Overlay mobile -->
        <div x-show="sidebarOpen" x-transition.opacity class="fixed inset-0 bg-black/50 z-30 lg:hidden" @click="sidebarOpen = false"></div>

        <!-- Main Content -->
        <main class="flex-1 overflow-x-hidden">
            <!-- Header -->
            <header class="bg-white dark:bg-gray-800 shadow-sm border-b border-gray-200 dark:border-gray-700 sticky top-0 z-20">
                <div class="px-4 sm:px-6 py-4">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center space-x-3">
                            <!-- Burger mobile -->
                            <button type="button" @click="sidebarOpen = true"
                                    class="lg:hidden inline-flex h-10 w-10 items-center justify-center rounded-md border border-gray-300 dark:border-gray-600 text-gray-600 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700">
                                <i class="fas fa-bars"></i>
                            </button>
                            <div>
                                <h2 class="text-xl sm:text-2xl font-semibold text-gray-800 dark:text-gray-100">
                                    @yield('page-title', 'Dashboard')
                                </h2>
                                @hasSection('breadcrumb')
                                    <nav class="text-sm text-gray-500 dark:text-gray-400 mt-1 hidden sm:block">
                                        @yield('breadcrumb')
                                    </nav>
                                @endif
                            </div>
                        </div>

                        <div class="flex items-center space-x-2 sm:space-x-4">
                            <!-- Notifications badge -->
                            <div x-data="{ pending: 0 }"
                                 x-init="setInterval(async () => { try { const r = await fetch('{{ route('sgmef.dashboard.stats') }}', { headers: { 'Accept': 'application/json' } }); const d = await r.json(); pending = d.pending_invoices || 0; } catch(e) {} }, {{ max(5, (int) config('lara_sgmef_qr.web_interface.polling_seconds', 30)) }} * 1000)"
                                 class="relative">
                                <span class="inline-flex h-10 w-10 items-center justify-center rounded-md border border-gray-300 dark:border-gray-600 text-gray-600 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700 cursor-pointer"
                                      title="Factures en attente">
                                    <i class="fas fa-bell"></i>
                                </span>
                                <span x-show="pending > 0"
                                      class="absolute -top-1 -right-1 bg-red-500 text-white text-xs font-bold rounded-full h-5 w-5 flex items-center justify-center"
                                      x-text="pending"></span>
                            </div>
                            <!-- Dark mode toggle -->
                            <button type="button"
                                    @click="dark = !dark; localStorage.setItem('sgmef_dark', dark ? '1' : '0')"
                                    class="inline-flex h-10 w-10 items-center justify-center rounded-md border border-gray-300 dark:border-gray-600 text-gray-600 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700"
                                    title="Mode sombre">
                                <i class="fas fa-moon" x-show="!dark"></i>
                                <i class="fas fa-sun" x-show="dark" x-cloak></i>
                            </button>
                            @yield('header-actions')
                        </div>
                    </div>
                </div>
            </header>

            <!-- Content -->
            <div class="p-4 sm:p-6">
                <!-- Flash Messages -->
                @if(session('success'))
                    <div class="mb-6 bg-green-100 dark:bg-green-900/40 border border-green-400 dark:border-green-700 text-green-700 dark:text-green-300 px-4 py-3 rounded-lg" role="alert">
                        <div class="flex items-center">
                            <i class="fas fa-check-circle mr-2"></i>
                            <span>{{ session('success') }}</span>
                        </div>
                    </div>
                @endif

                @if(session('error'))
                    <div class="mb-6 bg-red-100 dark:bg-red-900/40 border border-red-400 dark:border-red-700 text-red-700 dark:text-red-300 px-4 py-3 rounded-lg" role="alert">
                        <div class="flex items-center">
                            <i class="fas fa-exclamation-circle mr-2"></i>
                            <span>{{ session('error') }}</span>
                        </div>
                    </div>
                @endif

                @if($errors->any())
                    <div class="mb-6 bg-red-100 dark:bg-red-900/40 border border-red-400 dark:border-red-700 text-red-700 dark:text-red-300 px-4 py-3 rounded-lg" role="alert">
                        <div class="flex items-center mb-2">
                            <i class="fas fa-exclamation-triangle mr-2"></i>
                            <span class="font-medium">Erreurs de validation :</span>
                        </div>
                        <ul class="list-disc list-inside space-y-1">
                            @foreach($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                @yield('content')
            </div>
        </main>
    </div>

    @stack('scripts')
    <style>
        [x-cloak] { display: none !important; }
        /* Dark mode overrides for dynamic Tailwind classes */
        .dark .bg-white { background-color: #1f2937 !important; }
        .dark .bg-gray-50 { background-color: #111827 !important; }
        .dark .bg-gray-100 { background-color: #1f2937 !important; }
        .dark .hover\:bg-gray-50:hover { background-color: #374151 !important; }
        .dark .text-gray-900, .dark .text-gray-800, .dark .text-gray-700 { color: #f9fafb !important; }
        .dark .text-gray-500 { color: #d1d5db !important; }
        .dark .text-gray-600 { color: #9ca3af !important; }
        .dark .border-gray-200, .dark .border-gray-300 { border-color: #374151 !important; }
        .dark .divide-gray-200 > * { border-color: #374151 !important; }
        .dark .bg-blue-50 { background-color: rgba(37, 99, 235, 0.15) !important; }
        .dark .bg-green-100 { background-color: rgba(34, 197, 94, 0.15) !important; }
        .dark .bg-yellow-100 { background-color: rgba(245, 158, 66, 0.15) !important; }
        .dark .bg-red-100 { background-color: rgba(239, 68, 68, 0.15) !important; }
    </style>
    @include('lara-sgmef-qr::partials.toast')
</body>
</html>
