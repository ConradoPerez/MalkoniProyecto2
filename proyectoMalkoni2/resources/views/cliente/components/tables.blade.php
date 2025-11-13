<div class="space-y-8">
    <div class="bg-white rounded-lg p-6 border border-gray-200 shadow-sm">
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="border-b border-gray-200">
                        <th class="text-left py-3 px-2 font-semibold text-gray-600">
                            Estado
                        </th>
                        <th class="text-left py-3 px-2 font-semibold text-gray-600">
                            Número de pedido
                        </th>
                        <th class="text-left py-3 px-2 font-semibold text-gray-600">
                            Fecha de Inicio
                        </th>
                        <th class="text-left py-3 px-2 font-semibold text-gray-600">
                            Vendedor
                        </th>
                        <th class="text-left py-3 px-2 font-semibold text-gray-600">
                            Acciones
                        </th>
                    </tr>
                </thead>
                <tbody>
                    {{-- Usaremos los mismos datos, pero mostrando diferentes columnas --}}
                    @if(isset($ultimasCotizaciones) && $ultimasCotizaciones->count() > 0)
                        @foreach($ultimasCotizaciones as $cotizacion)
                            <tr class="border-b border-gray-200 hover:bg-gray-50">
                                <td class="py-3 px-2">
                                    {{-- El círculo de estado (asumimos que $cotizacion->estado_color es una clase o estilo) --}}
                                    <span class="inline-block w-3 h-3 rounded-full {{ $cotizacion->estado_color_clase }}" 
                                          style="background-color: {{ $cotizacion->estado_color_hex }}"></span>
                                </td>
                                <td class="py-3 px-2 text-gray-900">{{ $cotizacion->numero_de_pedido }}</td>
                                <td class="py-3 px-2 text-gray-900">{{ $cotizacion->fecha_inicio }}</td>
                                <td class="py-3 px-2 text-gray-900">{{ $cotizacion->empleado->nombre ?? 'Sin vendedor' }}</td>
                                <td class="py-3 px-2 text-gray-900">
                                    <div class="flex space-x-2">
                                        <a href="{{ route('cliente.cotizacion.ver', $cotizacion->id) }}" class="text-blue-500 hover:text-blue-700">Ver</a>
                                        <a href="{{ route('cliente.cotizacion.editar', $cotizacion->id) }}" class="text-gray-500 hover:text-gray-700">Editar</a>
                                    </div>
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
</div>