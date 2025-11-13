<!-- Sidebar -->
<aside class="hidden lg:block w-48 bg-white border-r border-gray-200 p-6 fixed left-0 top-0 h-screen overflow-y-auto">

    <!-- Logo -->
    <div class="mb-8">
        <div class="flex items-center justify-center">
            <img src="{{ asset('logo/logo negro.png') }}" alt="Malkoni Logo" class="h-12 w-auto">
        </div>
    </div>

    <!-- Navigation -->
    <nav class="space-y-2 mb-8">
        <!-- Dashboard -->
        <a href="{{ route('vendedor.dashboard') }}" class="w-full flex items-center space-x-3 px-4 py-3 rounded-lg text-sm font-medium {{ request()->routeIs('vendedor.dashboard') ? 'text-white' : 'text-gray-900 hover:bg-gray-50' }} transition-colors"
           style="{{ request()->routeIs('vendedor.dashboard') ? 'background-color: #D88429;' : '' }}">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
            </svg>
            <span>Dashboard</span>
        </a>

        <!-- Cotizaciones -->
        <a href="{{ route('vendedor.app.cotizaciones.index') }}" class="w-full flex items-center space-x-3 px-4 py-3 rounded-lg text-sm font-medium {{ request()->routeIs('vendedor.app.cotizaciones.*') ? 'text-white' : 'text-gray-900 hover:bg-gray-50' }} transition-colors"
           style="{{ request()->routeIs('vendedor.app.cotizaciones.*') ? 'background-color: #D88429;' : '' }}">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
            </svg>
            <span>Cotizaciones</span>
        </a>

        <!-- Clientes -->
        <a href="{{ route('vendedor.app.clientes.index') }}" class="w-full flex items-center space-x-3 px-4 py-3 rounded-lg text-sm font-medium {{ request()->routeIs('vendedor.app.clientes.*') ? 'text-white' : 'text-gray-900 hover:bg-gray-50' }} transition-colors"
           style="{{ request()->routeIs('vendedor.app.clientes.*') ? 'background-color: #D88429;' : '' }}">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-2a6 6 0 0112 0v2z"></path>
            </svg>
            <span>Clientes</span>
        </a>

        <!-- Grupos de Clientes -->
        <a href="{{ route('vendedor.app.grupos.index') }}" class="w-full flex items-center space-x-3 px-4 py-3 rounded-lg text-sm font-medium {{ request()->routeIs('vendedor.app.grupos.*') ? 'text-white' : 'text-gray-900 hover:bg-gray-50' }} transition-colors"
           style="{{ request()->routeIs('vendedor.app.grupos.*') ? 'background-color: #D88429;' : '' }}">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.856-1.487M15 10a3 3 0 11-6 0 3 3 0 016 0zM6 20a9 9 0 0118 0v2H4v-2a9 9 0 0118 0z"></path>
            </svg>
            <span>Grupo de clientes</span>
        </a>
    </nav>
    
    <!-- Métricas Rápidas Section -->
    <div class="mb-4">
        <h3 class="text-sm font-syncopate font-bold text-gray-900 mb-4">
            MÉTRICAS RÁPIDAS
        </h3>
    </div>
    
    <!-- Indicadores (solo vista) -->
    <div class="mt-2 space-y-4">
        <!-- Mensajes -->
        <div class="border border-gray-300 rounded-xl p-4 bg-white">
            <div class="flex items-center gap-3">
                <!-- Icono sobre (envelope) -->
                <div class="w-12 h-12 rounded-md border border-gray-400 grid place-items-center">
                    <svg class="w-6 h-6 text-gray-800" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8"
                              d="M3 8l9 6 9-6M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                    </svg>
                </div>
                <div class="text-sm font-semibold">7 Mensajes</div>
            </div>
        </div>

        <!-- Clientes -->
        <div class="border border-gray-300 rounded-xl p-4 bg-white">
            <div class="flex items-center gap-3">
                <!-- Icono usuarios -->
                <div class="w-12 h-12 rounded-md border border-gray-400 grid place-items-center">
                    <svg class="w-6 h-6 text-gray-800" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8"
                              d="M17 20h5v-2a3 3 0 00-5.356-1.857M9 20H4v-2a4 4 0 017.33-2.5M15 11a3 3 0 11-6 0 3 3 0 016 0z"/>
                    </svg>
                </div>
                <div class="text-sm font-semibold">20 clientes</div>
            </div>
        </div>

        <!-- Comisiones -->
        <div class="border border-gray-300 rounded-xl p-4 bg-white">
            <div class="flex items-center gap-3">
                <!-- Icono $ -->
                <div class="w-12 h-12 rounded-md border border-gray-400 grid place-items-center">
                    <svg class="w-6 h-6 text-gray-800" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8"
                              d="M12 8c-3.333 0-3.333-4 0-4s3.333 4 0 4m0 8c3.333 0 3.333 4 0 4s-3.333-4 0-4m0-8v8"/>
                    </svg>
                </div>
                <div class="text-sm font-semibold">$4000 en comisiones</div>
            </div>
        </div>
    </div>

</aside>

<!-- Mobile sidebar toggle button (for future mobile implementation) -->
<div class="lg:hidden flex-1 ml-48"></div>