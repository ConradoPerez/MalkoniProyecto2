<!-- Metrics Section -->
<div class="grid grid-cols-1 md:grid-cols-3 gap-4 lg:gap-6">
    <!-- Clientes Digitalizados -->
    <div class="bg-white rounded-lg p-6 border border-gray-200 shadow-sm">
        <div class="flex items-start space-x-4">
            <div class="p-2 bg-purple-100 rounded-lg">
                <svg class="w-6 h-6 text-primary" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                </svg>
            </div>
            <div>
                <p class="text-xs lg:text-sm text-gray-600 font-medium">
                    Clientes Online
                </p>
                <p class="text-xl lg:text-2xl font-syncopate font-bold text-gray-900 mt-1">
                    {{ number_format($metrics['clientes_digitalizados'] ?? 0) }}
                </p>
            </div>
        </div>
    </div>

    <!-- Cotizaciones Pendientes -->
    <a href="{{ route('vendedor.app.cotizaciones.index', ['estado' => 'pendientes', 'empleado_id' => $empleadoId ?? 1]) }}" class="bg-white rounded-lg p-6 border border-gray-200 shadow-sm hover:shadow-md transition-shadow duration-200 block">
        <div class="flex items-start justify-between">
            <div class="flex items-start space-x-4">
                <div class="p-2 bg-blue-100 rounded-lg">
                    <svg class="w-6 h-6 text-primary" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                    </svg>
                </div>
                <div>
                    <p class="text-xs lg:text-sm text-gray-600 font-medium">
                        Cotizaciones Pendientes
                    </p>
                    <p class="text-xl lg:text-2xl font-syncopate font-bold text-gray-900 mt-1">
                        {{ number_format($metrics['cotizaciones_pendientes'] ?? 0) }}
                    </p>
                </div>
            </div>
            <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
            </svg>
        </div>
    </a>

    <!-- Comisiones Este Mes -->
    <div class="bg-white rounded-lg p-6 border border-gray-200 shadow-sm">
        <div class="flex items-start space-x-4">
            <div class="p-2 bg-green-100 rounded-lg">
                <svg class="w-6 h-6 text-primary" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
            </div>
            <div class="flex-1">
                <div class="flex items-center gap-2">
                    <p class="text-xs lg:text-sm text-gray-600 font-medium">
                        Comisiones estimadas este mes
                    </p>
                    <div class="relative group">
                        <svg class="w-4 h-4 cursor-help" style="color: #D88429;" fill="currentColor" viewBox="0 0 24 24">
                            <path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm1 17h-2v-2h2v2zm2.07-7.75l-.9.92C13.45 12.9 13 13.5 13 15h-2v-.5c0-1.1.45-2.1 1.17-2.83l1.24-1.26c.37-.36.59-.86.59-1.41 0-1.1-.9-2-2-2s-2 .9-2 2H8c0-2.21 1.79-4 4-4s4 1.79 4 4c0 .88-.36 1.68-.93 2.25z"/>
                        </svg>
                        <div class="absolute left-0 top-full mt-2 w-64 bg-gray-900 text-white text-xs rounded-lg p-3 shadow-lg opacity-0 invisible group-hover:opacity-100 group-hover:visible transition-all duration-200 z-10">
                            <p class="leading-relaxed">
                                La información de comisiones es un estimado sujeto a confirmación de ventas efectivas. Para consultas, comuníquese con su supervisor.
                            </p>
                            <div class="absolute -top-1 left-4 w-2 h-2 bg-gray-900 transform rotate-45"></div>
                        </div>
                    </div>
                </div>
                <p class="text-xl lg:text-2xl font-syncopate font-bold text-gray-900 mt-1">
                    ${{ number_format($metrics['comisiones_mes'] ?? 0, 0, ',', '.') }}
                </p>
            </div>
        </div>
    </div>
</div>