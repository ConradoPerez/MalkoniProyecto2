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
        <a href="{{ route('vendedor.dashboard') }}?empleado_id={{ request('empleado_id', 1) }}" class="w-full flex items-center space-x-3 px-4 py-3 rounded-lg text-sm font-medium {{ request()->routeIs('vendedor.dashboard') ? 'text-white' : 'text-gray-900 hover:bg-gray-50' }} transition-colors"
           style="{{ request()->routeIs('vendedor.dashboard') ? 'background-color: #D88429;' : '' }}">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
            </svg>
            <span>Dashboard</span>
        </a>

        <!-- Cotizaciones -->
        <a href="{{ route('vendedor.app.cotizaciones.index') }}?empleado_id={{ request('empleado_id', 1) }}" class="w-full flex items-center space-x-3 px-4 py-3 rounded-lg text-sm font-medium {{ request()->routeIs('vendedor.app.cotizaciones.*') ? 'text-white' : 'text-gray-900 hover:bg-gray-50' }} transition-colors"
           style="{{ request()->routeIs('vendedor.app.cotizaciones.*') ? 'background-color: #D88429;' : '' }}">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
            </svg>
            <span>Cotizaciones</span>
        </a>

        <!-- Clientes -->
        <a href="{{ route('vendedor.app.clientes.index') }}?empleado_id={{ request('empleado_id', 1) }}" class="w-full flex items-center space-x-3 px-4 py-3 rounded-lg text-sm font-medium {{ request()->routeIs('vendedor.app.clientes.*') ? 'text-white' : 'text-gray-900 hover:bg-gray-50' }} transition-colors"
           style="{{ request()->routeIs('vendedor.app.clientes.*') ? 'background-color: #D88429;' : '' }}">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
            </svg>
            <span>Clientes</span>
        </a>

        <!-- Grupos de Clientes -->
        <a href="{{ route('vendedor.app.grupos.index') }}?empleado_id={{ request('empleado_id', 1) }}" class="w-full flex items-center space-x-3 px-4 py-3 rounded-lg text-sm font-medium {{ request()->routeIs('vendedor.app.grupos.*') ? 'text-white' : 'text-gray-900 hover:bg-gray-50' }} transition-colors"
           style="{{ request()->routeIs('vendedor.app.grupos.*') ? 'background-color: #D88429;' : '' }}">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"></path>
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
    
    <!-- Indicadores con datos reales -->
    <div class="mt-2 space-y-3">
        @php
            $empleadoId = request('empleado_id', 1);
            $clientesCount = \App\Models\Empresa::whereHas('cotizaciones', function($q) use ($empleadoId) {
                $q->where('id_empleados', $empleadoId);
            })->count();
            
            $comisionesMes = \App\Models\Cotizacion::where('id_empleados', $empleadoId)
                ->whereNotNull('fecha_cotizado')
                ->whereMonth('fecha_cotizado', now()->month)
                ->whereYear('fecha_cotizado', now()->year)
                ->sum('precio_total');
        @endphp

        <!-- Mensajes -->
        <div class="bg-gradient-to-br from-gray-50 to-gray-100 border border-gray-200 rounded-xl p-3.5 shadow-sm hover:shadow-md transition-shadow">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-lg grid place-items-center shadow-sm" style="background-color: #B1B7BB;">
                    <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                    </svg>
                </div>
                <div>
                    <div class="text-xs font-medium" style="color: #6b7280;">Mensajes</div>
                    <div class="text-lg font-bold text-gray-900">7</div>
                </div>
            </div>
        </div>

        <!-- Clientes -->
        <div class="bg-gradient-to-br from-teal-50 to-teal-100 border rounded-xl p-3.5 shadow-sm hover:shadow-md transition-shadow" style="border-color: #99f6e4;">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-lg grid place-items-center shadow-sm" style="background-color: #166379;">
                    <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/>
                    </svg>
                </div>
                <div>
                    <div class="text-xs font-medium" style="color: #0f766e;">Clientes</div>
                    <div class="text-lg font-bold" style="color: #115e59;">{{ $clientesCount }}</div>
                </div>
            </div>
        </div>

        <!-- Comisiones -->
        <div class="bg-gradient-to-br from-orange-50 to-orange-100 border rounded-xl p-3.5 shadow-sm hover:shadow-md transition-shadow" style="border-color: #fce2cc;">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-lg grid place-items-center shadow-sm" style="background-color: #D88429;">
                    <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </div>
                <div>
                    <div class="text-xs font-medium" style="color: #b86d21;">Este mes</div>
                    <div class="text-lg font-bold" style="color: #945720;">${{ number_format($comisionesMes, 0, ',', '.') }}</div>
                </div>
            </div>
        </div>
    </div>

</aside>

<!-- Mobile sidebar toggle button (for future mobile implementation) -->
<div class="lg:hidden flex-1 ml-56"></div>