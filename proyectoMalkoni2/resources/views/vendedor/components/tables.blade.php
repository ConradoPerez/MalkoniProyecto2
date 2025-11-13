<!-- Tables Section -->
<div class="grid grid-cols-1 xl:grid-cols-2 gap-8">
    <!-- Últimas Cotizaciones -->
    <div class="bg-white rounded-lg p-6 border border-gray-200">
        <h2 class="text-lg font-syncopate font-bold text-gray-900 mb-4">
            ÚLTIMAS COTIZACIONES
        </h2>
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="bg-gray-100 border-b border-gray-200">
                        <th class="text-left py-3 px-2 font-semibold text-gray-700">
                            Estado
                        </th>
                        <th class="text-left py-3 px-2 font-semibold text-gray-700">
                            N° cotización
                        </th>
                        <th class="text-left py-3 px-2 font-semibold text-gray-700">
                            Cliente
                        </th>
                        <th class="text-left py-3 px-2 font-semibold text-gray-700">
                            Monto
                        </th>
                        <th class="text-left py-3 px-2 font-semibold text-gray-700">
                            Acción
                        </th>
                    </tr>
                </thead>
                <tbody>
                    @if(isset($ultimasCotizaciones) && $ultimasCotizaciones->count() > 0)
                        @foreach($ultimasCotizaciones as $cotizacion)
                            <tr class="border-b border-gray-200 hover:bg-gray-50">
                                <td class="py-3 px-2">
                                    @if($cotizacion->estadoActual)
                                        @php
                                            $estadoClass = match($cotizacion->estadoActual->nombre) {
                                                'Nuevo' => 'bg-blue-100 text-blue-800',
                                                'Abierto' => 'bg-yellow-100 text-yellow-800',
                                                'Cotizado' => 'bg-green-100 text-green-800',
                                                'En entrega' => 'bg-purple-100 text-purple-800',
                                                default => 'bg-gray-100 text-gray-600'
                                            };
                                        @endphp
                                        <span class="inline-block px-2 py-1 rounded text-xs font-medium {{ $estadoClass }}">
                                            {{ $cotizacion->estadoActual->nombre }}
                                        </span>
                                    @else
                                        <span class="inline-block px-2 py-1 rounded text-xs font-medium bg-gray-100 text-gray-600">
                                            Sin Estado
                                        </span>
                                    @endif
                                </td>
                                <td class="py-3 px-2 text-gray-900">#{{ str_pad($cotizacion->numero, 3, '0', STR_PAD_LEFT) }}</td>
                                <td class="py-3 px-2 text-gray-900">{{ $cotizacion->empresa->nombre ?? 'Sin cliente' }}</td>
                                <td class="py-3 px-2 text-gray-900">${{ number_format($cotizacion->precio_total, 0, ',', '.') }}</td>
                                <td class="py-3 px-2">
                                    <a href="#" class="text-primary text-xs font-medium hover:underline">
                                        Ver detalle
                                    </a>
                                </td>
                            </tr>
                        @endforeach
                    @else
                        <tr>
                            <td colspan="5" class="py-8 px-2 text-center text-gray-500">
                                No hay cotizaciones registradas
                            </td>
                        </tr>
                    @endif
                </tbody>
            </table>
        </div>
    </div>

    <!-- Product Ranking -->
    <div class="bg-white rounded-lg p-6 border border-gray-200">
        <h2 class="text-lg font-syncopate font-bold text-gray-900 mb-4">
            RANKING DE MIS PRODUCTOS
        </h2>
        <div class="space-y-2">
            @if(isset($productosRanking) && $productosRanking->count() > 0)
                @foreach($productosRanking as $index => $producto)
                    <div class="flex items-center justify-between py-3 px-2 {{ !$loop->last ? 'border-b border-gray-200' : '' }}">
                        <div class="flex items-center space-x-3">
                            <span class="w-6 h-6 rounded-full bg-gray-200 flex items-center justify-center text-xs font-bold">{{ $index + 1 }}</span>
                            <span class="text-sm">{{ $producto->nombre }}</span>
                        </div>
                        <span class="text-sm font-medium">{{ $producto->total_cotizaciones }}</span>
                    </div>
                @endforeach
            @else
                <div class="text-center py-8 text-gray-500">
                    <p>No hay productos cotizados</p>
                </div>
            @endif
        </div>
    </div>
</div>