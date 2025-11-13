<!-- Tables Section -->
<div class="space-y-8">
    <!-- Mis Últimas Cotizaciones -->
    <div class="bg-white rounded-lg p-6 border border-gray-200 shadow-sm">
        <h2 class="text-lg font-syncopate font-bold text-gray-900 mb-4">
            Mis Últimas Cotizaciones
        </h2>
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="border-b border-gray-200">
                        <th class="text-left py-3 px-2 font-semibold text-gray-600">
                            Estado
                        </th>
                        <th class="text-left py-3 px-2 font-semibold text-gray-600">
                            Cliente
                        </th>
                        <th class="text-left py-3 px-2 font-semibold text-gray-600">
                            Número
                        </th>
                        <th class="text-left py-3 px-2 font-semibold text-gray-600">
                            Monto
                        </th>
                    </tr>
                </thead>
                <tbody>
                    @if(isset($misCotizaciones) && $misCotizaciones->count() > 0)
                        @foreach($misCotizaciones as $cotizacion)
                            <tr class="border-b border-gray-200 hover:bg-gray-50">
                                <td class="py-3 px-2">
                                    <span class="inline-block px-3 py-1 rounded text-xs font-medium text-white {{ $cotizacion->estado_clase }}" 
                                          style="{{ $cotizacion->estado_estilo }}">
                                        {{ $cotizacion->estado }}
                                    </span>
                                </td>
                                <td class="py-3 px-2 text-gray-900">{{ $cotizacion->empresa->nombre ?? 'Sin cliente' }}</td>
                                <td class="py-3 px-2 text-gray-900">{{ $cotizacion->numero_formateado }}</td>
                                <td class="py-3 px-2 text-gray-900">{{ $cotizacion->precio_formateado }}</td>
                            </tr>
                        @endforeach
                    @else
                        <tr>
                            <td colspan="4" class="py-8 px-2 text-center text-gray-500">
                                No hay cotizaciones registradas
                            </td>
                        </tr>
                    @endif
                </tbody>
            </table>
        </div>
    </div>

    <!-- Mis Clientes Activos -->
    <div class="bg-white rounded-lg p-6 border border-gray-200 shadow-sm">
        <h2 class="text-lg font-syncopate font-bold text-gray-900 mb-4">
            Mis Clientes Más Activos
        </h2>
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="border-b border-gray-200">
                        <th class="text-left py-3 px-2 font-semibold text-gray-600">
                            Ranking
                        </th>
                        <th class="text-left py-3 px-2 font-semibold text-gray-600">
                            Cliente
                        </th>
                        <th class="text-left py-3 px-2 font-semibold text-gray-600">
                            Cotizaciones
                        </th>
                    </tr>
                </thead>
                <tbody>
                    @if(isset($clientesActivos) && $clientesActivos->count() > 0)
                        @foreach($clientesActivos as $index => $cliente)
                            <tr class="border-b border-gray-200 hover:bg-gray-50">
                                <td class="py-3 px-2 text-gray-900 font-medium">
                                    @php
                                        $bgColor = match($index + 1) {
                                            1 => '#D88429',
                                            2 => '#166379', 
                                            3 => '#B1B7BB',
                                            default => '#6B7280'
                                        };
                                    @endphp
                                    @if($index < 3)
                                        <span class="inline-flex items-center justify-center w-8 h-8 rounded-full text-white font-bold text-xs" 
                                              style="background-color: {{ $bgColor }};">
                                            {{ $index + 1 }}
                                        </span>
                                    @else
                                        {{ $index + 1 }}
                                    @endif
                                </td>
                                <td class="py-3 px-2 text-gray-900">{{ $cliente->nombre }}</td>
                                <td class="py-3 px-2 text-gray-900 font-medium">{{ $cliente->cotizaciones_count }}</td>
                            </tr>
                        @endforeach
                    @else
                        <tr>
                            <td colspan="3" class="py-8 px-2 text-center text-gray-500">
                                No hay clientes con cotizaciones registradas
                            </td>
                        </tr>
                    @endif
                </tbody>
            </table>
        </div>
    </div>
</div>