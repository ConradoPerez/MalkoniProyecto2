<!-- Metrics Section -->
<div class="grid grid-cols-1 md:grid-cols-3 gap-4 lg:gap-6">
    <!-- Clientes Digitalizados -->
    <div class="bg-white rounded-lg p-6 border border-gray-200 shadow-sm">
        <div class="flex items-start space-x-4">
            <div class="p-2 bg-purple-100 rounded-lg">
                <svg class="w-6 h-6 text-primary" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-2a6 6 0 0112 0v2z"></path>
                </svg>
            </div>
            <div>
                <p class="text-xs lg:text-sm text-gray-600 font-medium">
                    Clientes Digitalizados
                </p>
                <p class="text-xl lg:text-2xl font-syncopate font-bold text-gray-900 mt-1">
                    {{ number_format($metrics['clientes_digitalizados'] ?? 0) }}
                </p>
            </div>
        </div>
    </div>

    <!-- Cotizaciones en Proceso -->
    <div class="bg-white rounded-lg p-6 border border-gray-200 shadow-sm">
        <div class="flex items-start space-x-4">
            <div class="p-2 bg-blue-100 rounded-lg">
                <svg class="w-6 h-6 text-primary" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                </svg>
            </div>
            <div>
                <p class="text-xs lg:text-sm text-gray-600 font-medium">
                    Cotizaciones en proceso
                </p>
                <p class="text-xl lg:text-2xl font-syncopate font-bold text-gray-900 mt-1">
                    {{ number_format($metrics['cotizaciones_proceso'] ?? 0) }}
                </p>
            </div>
        </div>
    </div>

    <!-- Comisiones Este Mes -->
    <div class="bg-white rounded-lg p-6 border border-gray-200 shadow-sm">
        <div class="flex items-start space-x-4">
            <div class="p-2 bg-green-100 rounded-lg">
                <svg class="w-6 h-6 text-primary" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
            </div>
            <div>
                <p class="text-xs lg:text-sm text-gray-600 font-medium">
                    Comisiones este mes
                </p>
                <p class="text-xl lg:text-2xl font-syncopate font-bold text-gray-900 mt-1">
                    ${{ number_format($metrics['comisiones_mes'] ?? 0, 0, ',', '.') }}
                </p>
            </div>
        </div>
    </div>
</div>