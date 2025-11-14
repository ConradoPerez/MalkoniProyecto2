<!-- Sidebar -->
<aside class="hidden lg:block w-56 bg-white border-r border-gray-200 p-6 fixed left-0 top-0 h-screen overflow-y-auto">
    <!-- Logo -->
    <div class="mb-8">
        <div class="flex items-center justify-center">
            <img src="{{ asset('logo/logo negro.png') }}" alt="Malkoni Logo" class="h-12 w-auto">
        </div>
    </div>

    <!-- Navigation -->
    <nav class="space-y-2 mb-8">
        <!-- Dashboard -->
        <a href="{{ route('cliente.dashboard') }}" class="w-full flex items-center space-x-3 px-4 py-3 rounded-lg text-sm font-medium {{ request()->routeIs('cliente.dashboard') ? 'text-white' : 'text-gray-900 hover:bg-gray-50' }} transition-colors"
           style="{{ request()->routeIs('cliente.dashboard') ? 'background-color: #D88429;' : '' }}">
            <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24">
                <path d="M3 13h2v8H3zm4-8h2v16H7zm4-2h2v18h-2zm4 4h2v14h-2zm4-4h2v18h-2z"/>
            </svg>
            <span>Dashboard</span>
        </a>

        <!-- Cotizaciones -->
        <a href="{{ route('cliente.cotizaciones') }}" class="w-full flex items-center space-x-3 px-4 py-3 rounded-lg text-sm font-medium {{ request()->routeIs('cliente.cotizaciones') ? 'text-white' : 'text-gray-900 hover:bg-gray-50' }} transition-colors"
           style="{{ request()->routeIs('cliente.cotizaciones') ? 'background-color: #D88429;' : '' }}">
            <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                <path d="M10.5 1.5H2v9l8.5 8.5 9-9-8.5-8.5zm-2 5.5c-1.1 0-2-.9-2-2s.9-2 2-2 2 .9 2 2-.9 2-2 2z"/>
            </svg>
            <span>Cotizaciones</span>
        </a>

        <!-- Nueva Cotización -->
        <a href="{{ route('cliente.nueva_cotizacion') }}" class="w-full flex items-center space-x-3 px-4 py-3 rounded-lg text-sm font-medium {{ request()->routeIs('cliente.nueva_cotizacion') ? 'text-white' : 'text-gray-900 hover:bg-gray-50' }} transition-colors"
           style="{{ request()->routeIs('cliente.nueva_cotizacion') ? 'background-color: #D88429;' : '' }}">
            <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24">
                <path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm5 11h-4v4h-2v-4H7v-2h4V7h2v4h4v2z"/>
            </svg>
            <span>Nueva Cotización</span>
        </a>
    </nav>
    
    <!-- Métricas Rápidas Section -->
    <div class="mb-4">
        <h3 class="text-sm font-syncopate font-bold text-gray-900 mb-4">
            RESUMEN RÁPIDO
        </h3>
    </div>
    
    <!-- Indicadores (solo vista) -->
    <div class="mt-2 space-y-4">
        <!-- Cotizaciones Activas -->
        <div class="border border-gray-300 rounded-xl p-4 bg-white">
            <div class="flex items-center gap-3">
                <!-- Icono documento -->
                <div class="w-12 h-12 rounded-md border border-gray-400 grid place-items-center">
                    <svg class="w-6 h-6 text-gray-800" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8"
                              d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                    </svg>
                </div>
                <div class="text-sm font-semibold">{{ $cotizacionesActivas ?? '-' }} Activas</div>
            </div>
        </div>

        <!-- Mensajes -->
        <div class="border border-gray-300 rounded-xl p-4 bg-white">
            <div class="flex items-center gap-3">
                <!-- Icono mensaje (eva-message-circle) -->
                <div class="w-12 h-12 rounded-md border border-gray-400 grid place-items-center">
                    <svg class="w-6 h-6 text-gray-800" fill="currentColor" viewBox="0 0 24 24">
                        <g data-name="message-circle">
                            <path d="M12 2a10 10 0 1 0 10 10A10 10 0 0 0 12 2zm0 18a8 8 0 1 1 8-8 8 8 0 0 1-8 8zm3.5-9a1.5 1.5 0 1 1-1.5-1.5 1.5 1.5 0 0 1 1.5 1.5zm-7 0a1.5 1.5 0 1 1-1.5-1.5 1.5 1.5 0 0 1 1.5 1.5z"/>
                        </g>
                    </svg>
                </div>
                <div class="text-sm font-semibold">- Mensajes</div>
            </div>
        </div>

        <!-- Usuario Actual -->
        <div class="border border-gray-300 rounded-xl p-4 bg-white">
            <div class="flex items-center gap-3">
                <!-- Icono usuario -->
                <div class="w-12 h-12 rounded-md border border-gray-400 grid place-items-center">
                    <svg class="w-6 h-6 text-gray-800" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8"
                              d="M5.121 17.804A13.937 13.937 0 0112 16c2.5 0 4.847.655 6.879 1.804M15 10a3 3 0 11-6 0 3 3 0 016 0zm6 0a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </div>
                <div class="text-sm font-semibold truncate">{{ auth()->user()?->name ?? 'Cliente' }}</div>
            </div>
        </div>
    </div>

</aside>

<!-- Mobile sidebar toggle button (for future mobile implementation) -->
<div class="lg:hidden flex-1 ml-56"></div>