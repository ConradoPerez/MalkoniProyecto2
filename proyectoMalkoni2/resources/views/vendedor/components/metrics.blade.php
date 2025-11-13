<!-- Metrics Section -->
<div class="grid grid-cols-1 md:grid-cols-3 gap-4 lg:gap-6">
    <!-- Mis Cotizaciones -->
    <div class="bg-white rounded-lg p-6 border border-gray-200 shadow-sm">
        <div class="flex items-start space-x-4">
            <div class="p-2 rounded-lg" style="background-color: #FEF2E6;">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" style="color: #D88429;">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                </svg>
            </div>
            <div>
                <p class="text-xs lg:text-sm text-gray-600 font-medium">
                    Mis cotizaciones
                </p>
                <p class="text-xl lg:text-2xl font-syncopate font-bold text-gray-900 mt-1">
                    {{ number_format($metrics['mis_cotizaciones'] ?? 0) }}
                </p>
            </div>
        </div>
    </div>

    <!-- Mis Clientes -->
    <div class="bg-white rounded-lg p-6 border border-gray-200 shadow-sm">
        <div class="flex items-start space-x-4">
            <div class="p-2 rounded-lg" style="background-color: #E6F4F7;">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" style="color: #166379;">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path>
                </svg>
            </div>
            <div>
                <p class="text-xs lg:text-sm text-gray-600 font-medium">
                    Mis clientes
                </p>
                <p class="text-xl lg:text-2xl font-syncopate font-bold text-gray-900 mt-1">
                    {{ number_format($metrics['mis_clientes'] ?? 0) }}
                </p>
            </div>
        </div>
    </div>

    <!-- Ventas del Mes -->
    <div class="bg-white rounded-lg p-6 border border-gray-200 shadow-sm">
        <div class="flex items-start space-x-4">
            <div class="p-2 rounded-lg" style="background-color: #E6F4F7;">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" style="color: #166379;">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
            </div>
            <div>
                <p class="text-xs lg:text-sm text-gray-600 font-medium">
                    Mis ventas del mes
                </p>
                <p class="text-xl lg:text-2xl font-syncopate font-bold text-gray-900 mt-1">
                    ${{ number_format($metrics['ventas_del_mes'] ?? 0, 0, ',', '.') }}
                </p>
            </div>
        </div>
    </div>
</div>