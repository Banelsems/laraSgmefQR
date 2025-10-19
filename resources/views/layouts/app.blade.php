<!DOCTYPE html>
<html lang="fr">
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
    <!-- Font Awesome (fallback pour certains icônes) -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    @stack('styles')

    <!-- Alpine.js -->
    <script defer src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js"></script>
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    @stack('styles')
</head>
<body class="bg-gray-50 font-sans">
    <div class="min-h-screen flex">
        <!-- Sidebar -->
        <aside class="w-64 bg-white shadow-lg">
            <div class="p-6 border-b border-gray-200">
                <h1 class="text-xl font-bold text-gray-800">
                    <i class="fas fa-receipt text-blue-600 mr-2"></i>
                    LaraSgmefQR
                </h1>
            </div>
            
            <nav class="mt-6">
                <div class="px-6 py-3">
                    <p class="text-xs font-semibold text-gray-400 uppercase tracking-wider">
                        Navigation
                    </p>
                </div>
                
                <a href="{{ route('sgmef.dashboard') }}" 
                   class="flex items-center px-6 py-3 text-gray-700 hover:bg-blue-50 hover:text-blue-600 transition-colors {{ request()->routeIs('sgmef.dashboard') ? 'bg-blue-50 text-blue-600 border-r-2 border-blue-600' : '' }}">
                    <i class="fas fa-chart-dashboard w-5 mr-3"></i>
                    Tableau de bord
                </a>
                
                <a href="{{ route('sgmef.invoices.index') }}" 
                   class="flex items-center px-6 py-3 text-gray-700 hover:bg-blue-50 hover:text-blue-600 transition-colors {{ request()->routeIs('sgmef.invoices.*') ? 'bg-blue-50 text-blue-600 border-r-2 border-blue-600' : '' }}">
                    <i class="fas fa-file-invoice w-5 mr-3"></i>
                    Factures
                </a>
                
                <a href="{{ route('sgmef.config.index') }}" 
                   class="flex items-center px-6 py-3 text-gray-700 hover:bg-blue-50 hover:text-blue-600 transition-colors {{ request()->routeIs('sgmef.config.*') ? 'bg-blue-50 text-blue-600 border-r-2 border-blue-600' : '' }}">
                    <i class="fas fa-cog w-5 mr-3"></i>
                    Configuration
                </a>
            </nav>
        </aside>

        <!-- Main Content -->
        <main class="flex-1 overflow-x-hidden">
            <!-- Header -->
            <header class="bg-white shadow-sm border-b border-gray-200">
                <div class="px-6 py-4">
                    <div class="flex items-center justify-between">
                        <div>
                            <h2 class="text-2xl font-semibold text-gray-800">
                                @yield('page-title', 'Dashboard')
                            </h2>
                            @hasSection('breadcrumb')
                                <nav class="text-sm text-gray-500 mt-1">
                                    @yield('breadcrumb')
                                </nav>
                            @endif
                        </div>
                        
                        <div class="flex items-center space-x-4">
                            @yield('header-actions')
                        </div>
                    </div>
                </div>
            </header>

            <!-- Content -->
            <div class="p-6">
                <!-- Flash Messages -->
                @if(session('success'))
                    <div class="mb-6 bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded-lg" role="alert">
                        <div class="flex items-center">
                            <i class="fas fa-check-circle mr-2"></i>
                            <span>{{ session('success') }}</span>
                        </div>
                    </div>
                @endif

                @if(session('error'))
                    <div class="mb-6 bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded-lg" role="alert">
                        <div class="flex items-center">
                            <i class="fas fa-exclamation-circle mr-2"></i>
                            <span>{{ session('error') }}</span>
                        </div>
                    </div>
                @endif

                @if($errors->any())
                    <div class="mb-6 bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded-lg" role="alert">
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
    @include('partials.toast', ['type' => session('toast_type', 'success'), 'message' => session('toast_message', '')])
    <!-- Tooltips Alpine.js : rien à ajouter ici, ils sont inclus dans les partials -->
    <!-- Skeleton loader : à utiliser dans les vues lors des chargements -->
</body>
</html>
