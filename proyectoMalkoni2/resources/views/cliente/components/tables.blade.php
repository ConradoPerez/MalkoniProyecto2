<div class="space-y-8">
    <div class="bg-white rounded-lg p-6 border border-gray-200 shadow-sm">
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="border-b border-gray-200">
                        <th class="text-center py-3 px-2 font-semibold text-gray-600">
                            Estado
                        </th>
                        <th class="text-center py-3 px-2 font-semibold text-gray-600">
                            Número de pedido
                        </th>
                        <th class="text-center py-3 px-2 font-semibold text-gray-600">
                            Fecha de Inicio
                        </th>
                        <th class="text-center py-3 px-2 font-semibold text-gray-600">
                            Vendedor
                        </th>
                        <th class="text-center py-3 px-2 font-semibold text-gray-600">
                            Total
                        </th>
                        <th class="text-center py-3 px-2 font-semibold text-gray-600">
                            Acciones
                        </th>
                    </tr>
                </thead>
                <tbody>
                    @php
                        $estadoColores = [
                            'Nuevo' => 'bg-blue-100 text-blue-800',
                            'Abierto' => 'bg-yellow-100 text-yellow-800',
                            'Cotizado' => 'bg-green-100 text-green-800',
                            'En entrega' => 'bg-purple-100 text-purple-800',
                        ];
                    @endphp
                    @if(isset($ultimasCotizaciones) && $ultimasCotizaciones->count() > 0)
                        @foreach($ultimasCotizaciones as $cotizacion)
                            <tr class="border-b border-gray-200 hover:bg-gray-50">
                                <td class="py-3 px-2 text-center">
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $estadoColores[$cotizacion->estado_actual->nombre ?? 'Nuevo'] ?? 'bg-gray-100 text-gray-800' }}">
                                        {{ $cotizacion->estado_actual->nombre ?? 'Nuevo' }}
                                    </span>
                                </td>
                                <td class="py-3 px-2 text-center text-gray-900 font-semibold">#{{ $cotizacion->numero }}</td>
                                <td class="py-3 px-2 text-center text-gray-900">{{ $cotizacion->fyh ? $cotizacion->fyh->timezone('America/Argentina/Buenos_Aires')->format('d/m/Y') : '-' }}</td>
                                <td class="py-3 px-2 text-center text-gray-900">{{ $cotizacion->empleado->nombre ?? 'Sin vendedor' }}</td>
                                <td class="py-3 px-2 text-center text-gray-900 font-semibold">
                                    @php
                                        $estadoActual = $cotizacion->estado_actual->nombre ?? 'Nuevo';
                                        $sinPrecio = in_array($estadoActual, ['Nuevo', 'Abierto']) || !$cotizacion->precio_total || $cotizacion->precio_total <= 0;
                                    @endphp
                                    @if($sinPrecio)
                                        <span class="text-gray-500 text-sm italic">Sin Cotizar</span>
                                    @else
                                        ${{ number_format($cotizacion->precio_total, 0, ',', '.') }}
                                    @endif
                                </td>
                                <td class="py-3 px-2 text-gray-900">
                                    <div class="flex items-center justify-center gap-2">
                                        <a href="{{ route('cliente.cotizacion.ver', ['id' => $cotizacion->id, 'persona_id' => request('persona_id', 1)]) }}" class="inline-flex items-center px-3 py-1.5 border border-gray-300 rounded-lg text-gray-700 text-sm font-medium bg-white hover:bg-gray-50 transition-colors">
                                            <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                            </svg>
                                            Ver detalle
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    @else
                        <tr>
                            <td colspan="6" class="py-8 px-2 text-center text-gray-500">
                                No hay cotizaciones registradas
                            </td>
                        </tr>
                    @endif
                </tbody>
            </table>
        </div>
    </div>
</div>

