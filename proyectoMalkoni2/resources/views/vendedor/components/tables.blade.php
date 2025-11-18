<!-- Tables Section -->
<div class="grid grid-cols-1 xl:grid-cols-2 gap-8">
    <!-- Últimas Cotizaciones -->
    <div class="bg-white rounded-lg p-6 border border-gray-200 shadow-sm">
        <h2 class="text-lg font-syncopate font-bold text-gray-900 mb-4">
            ÚLTIMAS COTIZACIONES
        </h2>
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="bg-gray-50 border-b border-gray-200">
                        <th class="text-left py-3 px-2 font-semibold text-gray-600">
                            Estado
                        </th>
                        <th class="text-left py-3 px-2 font-semibold text-gray-600">
                            N° cotización
                        </th>
                        <th class="text-left py-3 px-2 font-semibold text-gray-600">
                            Cliente
                        </th>
                        <th class="text-left py-3 px-2 font-semibold text-gray-600">
                            Monto
                        </th>
                        <th class="text-left py-3 px-2 font-semibold text-gray-600">
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
                                <td class="py-3 px-2 text-gray-900">{{ $cotizacion->cliente_nombre }}</td>
                                <td class="py-3 px-2 text-gray-900">
                                    @php
                                        $estadoActual = $cotizacion->estadoActual->nombre ?? 'Nuevo';
                                        $sinPrecio = in_array($estadoActual, ['Nuevo', 'Abierto']) || !$cotizacion->precio_total || $cotizacion->precio_total <= 0;
                                    @endphp
                                    @if($sinPrecio)
                                        <span class="text-gray-500 text-sm italic">Sin Cotizar</span>
                                    @else
                                        ${{ number_format($cotizacion->precio_total, 0, ',', '.') }}
                                    @endif
                                </td>
                                <td class="py-3 px-2">
                                    <a href="{{ route('vendedor.app.cotizaciones.detalle', ['id' => $cotizacion->id, 'empleado_id' => isset($vendedor) ? $vendedor->id_empleado : 1]) }}" 
                                       class="inline-flex items-center px-3 py-1.5 border border-gray-300 rounded-lg text-gray-700 text-xs font-medium bg-white hover:bg-gray-50 transition-colors shadow-sm hover:shadow">
                                        <svg class="w-3.5 h-3.5 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                        </svg>
                                        Ver detalle
                                    </a>
                                </td>
                            </tr>
                        @endforeach
                    @else
                        <tr>
                            <td colspan="5" class="py-8 px-2 text-center text-gray-600">
                                No hay cotizaciones registradas
                            </td>
                        </tr>
                    @endif
                </tbody>
            </table>
        </div>
    </div>

    <!-- Product Ranking -->
    <div class="bg-white rounded-lg p-6 border border-gray-200 shadow-sm">
        <h2 class="text-lg font-syncopate font-bold text-gray-900 mb-4">
            RANKING DE MIS PRODUCTOS
        </h2>
        <div class="space-y-2">
            @if(isset($productosRanking) && $productosRanking->count() > 0)
                @foreach($productosRanking as $index => $producto)
                    <div class="flex items-center justify-between py-3 px-2 {{ !$loop->last ? 'border-b border-gray-200' : '' }}">
                        <div class="flex items-center space-x-3">
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
                                <span class="inline-flex items-center justify-center w-8 h-8 rounded-full bg-gray-200 text-gray-700 font-bold text-xs">
                                    {{ $index + 1 }}
                                </span>
                            @endif
                            <span class="text-sm text-gray-900">{{ $producto->nombre }}</span>
                        </div>
                        <span class="text-sm font-bold text-gray-900">{{ $producto->total_cotizaciones }}</span>
                    </div>
                @endforeach
            @else
                <div class="text-center py-8 text-gray-600">
                    <p>No hay productos cotizados</p>
                </div>
            @endif
        </div>
    </div>
</div>