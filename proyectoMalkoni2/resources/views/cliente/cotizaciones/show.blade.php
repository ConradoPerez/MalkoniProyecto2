@extends('layouts.app')

@section('title', 'Detalle de Cotización')

@section('content')
<div class="min-h-screen text-gray-900" style="background-color: #e1dfd9;">
    <div class="flex">
        @include('cliente.components.sidebar')

        <main class="flex-1 overflow-y-auto md:ml-64 transition-all duration-300">
            
            <div class="p-4 lg:p-8">


                <nav class="flex mb-6 mt-4" aria-label="Breadcrumb">
                    <ol class="inline-flex items-center space-x-1 md:space-x-3">
                        <li>
                            <a href="{{ route('cliente.dashboard') }}" class="inline-flex items-center text-sm font-medium text-gray-500 hover:text-[#D88429]">
                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path></svg>
                                Mis Cotizaciones
                            </a>
                        </li>
                        <li>
                            <div class="flex items-center">
                                <svg class="w-6 h-6 text-gray-300" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd"></path></svg>
                                <span class="ml-1 text-sm font-medium text-gray-400">Cotización #{{ $cotizacion->numero }}</span>
                            </div>
                        </li>
                    </ol>
                </nav>

                <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 mb-8">
                    <div>
                        <h2 class="text-2xl font-syncopate font-bold text-gray-800 flex items-center gap-3">
                            COTIZACIÓN <span class="text-[#D88429]">#{{ $cotizacion->numero }}</span>
                        </h2>
                        <p class="text-sm text-gray-500 mt-1 flex items-center gap-2 font-medium">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                            Creada el {{ $cotizacion->fyh->timezone('America/Argentina/Buenos_Aires')->format('d/m/Y') }}
                        </p>
                    </div>

                    <div class="flex gap-3">
                        @if(!empty($cotizacion->pdf_url))
                            <a href="{{ $cotizacion->pdf_url }}" target="_blank" class="inline-flex items-center px-4 py-2 bg-[#D88429] text-white rounded-lg text-sm font-medium hover:bg-[#c7731f] transition-all shadow-md">
                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"></path></svg>
                                Ver Plano (PDF)
                            </a>
                        @endif
                        <button onclick="window.print()" class="inline-flex items-center px-4 py-2 bg-gray-900 text-white rounded-lg text-sm font-medium hover:bg-gray-800 transition-all shadow-md">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"></path></svg>
                            Imprimir
                        </button>
                    </div>
                </div>

                <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
                    
                    <div class="lg:col-span-2 space-y-6">
                        
                        <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
                            <div class="px-6 py-4 border-b border-gray-100 bg-gray-50/50 flex justify-between items-center">
                                <h3 class="font-bold text-gray-800">Productos Solicitados</h3>
                                <span class="text-xs font-medium px-2.5 py-0.5 rounded-full bg-blue-100 text-blue-800">
                                    {{ $cotizacion->items->count() }} Items
                                </span>
                            </div>

                            @php
                                $estadoNombre = $cotizacion->estado;
                                $esCotizado   = $estadoNombre === 'Cotizado';
                            @endphp

                            <div class="overflow-x-auto">
                                <table class="w-full whitespace-nowrap">
                                    <thead class="bg-gray-50">
                                        <tr class="text-left text-xs font-semibold tracking-wide text-gray-500 uppercase border-b border-gray-100">
                                            <th class="px-6 py-3">Producto</th>
                                            <th class="px-6 py-3 text-center">Cant.</th>
                                            @if($esCotizado)
                                                <th class="px-6 py-3 text-right">P. Unitario</th>
                                                <th class="px-6 py-3 text-right">Subtotal</th>
                                            @endif
                                        </tr>
                                    </thead>
                                    <tbody class="divide-y divide-gray-50">
                                        @forelse($cotizacion->items as $item)
                                            <tr class="hover:bg-gray-50/50 transition-colors">
                                                <td class="px-6 py-4">
                                                    <div class="flex items-center">
                                                        <div class="h-10 w-10 shrink-0 rounded bg-gray-100 flex items-center justify-center text-gray-400 mr-3">
                                                            @if($item->producto && $item->producto->foto)
                                                                <img src="{{ asset($item->producto->foto) }}" class="h-10 w-10 rounded object-cover">
                                                            @else
                                                                <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                                                            @endif
                                                        </div>
                                                        <div>
                                                            @if($item->producto)
                                                                <p class="font-medium text-gray-900">{{ $item->producto->nombre }}</p>
                                                                <p class="text-xs text-gray-500">COD: {{ $item->producto->codigo ?? '---' }}</p>
                                                            @else
                                                                @php
                                                                    $payloadOrigen = is_string($cotizacion->payload_origen) ? json_decode($cotizacion->payload_origen, true) : $cotizacion->payload_origen;
                                                                @endphp
                                                                <p class="font-medium text-gray-900">{{ $payloadOrigen['mat_descri'] ?? 'Material de corte OPT' }}</p>
                                                                <p class="text-xs text-gray-500">Placa de madera importada</p>
                                                            @endif
                                                        </div>
                                                    </div>
                                                </td>
                                                <td class="px-6 py-4 text-center">
                                                    @if(!$esCotizado && $item->producto)
                                                        <div class="flex items-center justify-center gap-1.5">
                                                            <!-- Decrementar (-) -->
                                                            <form action="{{ route('cliente.cotizacion.actualizar_cantidad', ['cotizacionId' => $cotizacion->id, 'itemId' => $item->id_item]) }}" method="POST" class="inline m-0">
                                                                @csrf
                                                                <input type="hidden" name="change" value="-1">
                                                                <button type="submit" class="w-6 h-6 bg-gray-100 hover:bg-gray-200 text-gray-700 hover:text-gray-900 rounded font-bold text-xs flex items-center justify-center transition-all border border-gray-200 active:scale-95" title="Disminuir cantidad">
                                                                    -
                                                                </button>
                                                            </form>

                                                            <!-- Cantidad -->
                                                            <span class="inline-flex items-center justify-center font-mono font-bold text-gray-800 bg-gray-50 border border-gray-200 rounded px-2.5 py-0.5 text-xs min-w-[28px] text-center shadow-sm">
                                                                {{ $item->cantidad }}
                                                            </span>

                                                            <!-- Incrementar (+) -->
                                                            <form action="{{ route('cliente.cotizacion.actualizar_cantidad', ['cotizacionId' => $cotizacion->id, 'itemId' => $item->id_item]) }}" method="POST" class="inline m-0">
                                                                @csrf
                                                                <input type="hidden" name="change" value="1">
                                                                <button type="submit" class="w-6 h-6 bg-gray-100 hover:bg-gray-200 text-gray-700 hover:text-gray-900 rounded font-bold text-xs flex items-center justify-center transition-all border border-gray-200 active:scale-95" title="Aumentar cantidad">
                                                                    +
                                                                </button>
                                                            </form>

                                                            <!-- Basura (Eliminar) -->
                                                            <form action="{{ route('cliente.cotizacion.eliminar_item', ['cotizacionId' => $cotizacion->id, 'itemId' => $item->id_item]) }}" method="POST" class="inline m-0 ml-1.5" onsubmit="return confirm('¿Desea eliminar este producto de la cotización?');">
                                                                @csrf
                                                                @method('DELETE')
                                                                <button type="submit" class="p-1 text-red-500 hover:bg-red-50 hover:text-red-700 rounded transition-all active:scale-95" title="Eliminar producto">
                                                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                                                    </svg>
                                                                </button>
                                                            </form>
                                                        </div>
                                                    @else
                                                        <span class="inline-flex items-center justify-center px-2 py-1 text-xs font-bold leading-none text-gray-700 bg-gray-100 rounded">
                                                            {{ $item->cantidad }}
                                                        </span>
                                                    @endif
                                                </td>
                                                @if($esCotizado)
                                                    <td class="px-6 py-4 text-right text-sm text-gray-700">
                                                        ${{ number_format($item->precio_unitario ?? 0, 2, ',', '.') }}
                                                    </td>
                                                    <td class="px-6 py-4 text-right text-sm font-semibold text-gray-900">
                                                        ${{ number_format(($item->precio_unitario ?? 0) * ($item->cantidad ?? 1), 2, ',', '.') }}
                                                    </td>
                                                @endif
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="{{ $esCotizado ? 4 : 2 }}" class="px-6 py-10 text-center text-gray-500">
                                                    No hay items en esta cotización.
                                                    <a href="{{ route('cliente.cotizacion.agregar_productos', ['id' => $cotizacion->id]) }}" class="text-[#D88429] hover:underline ml-1">Agregar productos</a>
                                                </td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                    @if($esCotizado && $cotizacion->items->count() > 0)
                                        <tfoot>
                                            <tr class="bg-gray-50 border-t-2 border-gray-200">
                                                <td colspan="3" class="px-6 py-3 text-right text-sm font-bold text-gray-700 uppercase tracking-wide">Total</td>
                                                <td class="px-6 py-3 text-right text-base font-bold text-green-700">
                                                    ${{ number_format($cotizacion->precio_total ?? 0, 2, ',', '.') }}
                                                </td>
                                            </tr>
                                        </tfoot>
                                    @endif
                                </table>
                            </div>
                            
                            @if(!$esCotizado)
                            <div class="bg-gray-50 px-6 py-3 border-t border-gray-100">
                                <a href="{{ route('cliente.cotizacion.agregar_productos', ['id' => $cotizacion->id]) }}" class="text-sm font-medium text-[#166379] hover:text-[#0e4555] hover:underline flex items-center justify-center sm:justify-start">
                                    <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                                    Agregar más productos
                                </a>
                            </div>
                            @endif
                        </div>

                        <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden" id="chat-box">
                            <div class="px-6 py-4 border-b border-gray-100 bg-gray-50/50 flex items-center gap-2">
                                <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z"></path></svg>
                                <h3 class="font-bold text-gray-800">Comunicación con el Vendedor</h3>
                            </div>

                            {{-- Área de mensajes --}}
                            <div id="chat-mensajes" class="flex flex-col gap-3 p-4 h-72 overflow-y-auto bg-gray-50/30">
                                <p class="text-center text-xs text-gray-400 mt-auto" id="chat-empty">Cargando mensajes...</p>
                            </div>

                            {{-- Input de envío --}}
                            <div class="border-t border-gray-100 p-4 bg-white">
                                <form id="chat-form" class="flex gap-2">
                                    <input
                                        type="text"
                                        id="chat-input"
                                        placeholder="Escribí tu mensaje..."
                                        maxlength="2000"
                                        autocomplete="off"
                                        class="flex-1 px-4 py-2 text-sm border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-[#D88429]/30 focus:border-[#D88429]"
                                    >
                                    <button
                                        type="submit"
                                        class="px-4 py-2 rounded-lg text-white text-sm font-medium transition hover:opacity-90 disabled:opacity-50"
                                        style="background-color:#D88429;"
                                    >
                                        Enviar
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>

                    <div class="space-y-6">
                        
                        @php
                            $estadoConfig = match($estadoNombre) {
                                'Cotizado'    => [
                                    'card'   => 'bg-green-50 border-green-200',
                                    'header' => 'bg-green-100 border-green-200',
                                    'title'  => 'text-green-900',
                                    'icon'   => 'bg-green-200',
                                    'iconColor' => 'text-green-600',
                                    'badge'  => 'text-green-900',
                                    'desc'   => 'text-green-700',
                                    'label'  => 'Presupuesto listo',
                                    'msg'    => 'Tu cotización fue presupuestada por el vendedor. Podés ver los precios en la tabla de productos.',
                                    'svgPath'=> 'M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z',
                                ],
                                'En entrega'  => [
                                    'card'   => 'bg-blue-50 border-blue-200',
                                    'header' => 'bg-blue-100 border-blue-200',
                                    'title'  => 'text-blue-900',
                                    'icon'   => 'bg-blue-200',
                                    'iconColor' => 'text-blue-600',
                                    'badge'  => 'text-blue-900',
                                    'desc'   => 'text-blue-700',
                                    'label'  => 'En entrega',
                                    'msg'    => 'Tu pedido está en proceso de entrega.',
                                    'svgPath'=> 'M5 8h14M5 8a2 2 0 110-4h14a2 2 0 110 4M5 8l1 12a2 2 0 002 2h8a2 2 0 002-2L19 8M10 12v4m4-4v4',
                                ],
                                default       => [
                                    'card'   => 'bg-yellow-50 border-yellow-200',
                                    'header' => 'bg-yellow-100 border-yellow-200',
                                    'title'  => 'text-yellow-900',
                                    'icon'   => 'bg-yellow-200',
                                    'iconColor' => 'text-yellow-600',
                                    'badge'  => 'text-yellow-900',
                                    'desc'   => 'text-yellow-700',
                                    'label'  => 'Cotización enviada',
                                    'msg'    => 'Esperando presupuesto del asesor. Los precios se mostrarán una vez procesada la cotización.',
                                    'svgPath'=> 'M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z',
                                ],
                            };
                        @endphp

                        <div class="rounded-xl shadow-sm border overflow-hidden {{ $estadoConfig['card'] }}">
                            <div class="p-6 border-b {{ $estadoConfig['header'] }}">
                                <h3 class="text-lg font-bold {{ $estadoConfig['title'] }} flex items-center gap-2">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="{{ $estadoConfig['svgPath'] }}"></path></svg>
                                    Estado de Cotización
                                </h3>
                            </div>
                            <div class="p-6 text-center">
                                <div class="inline-flex items-center justify-center w-16 h-16 rounded-full mb-4 {{ $estadoConfig['icon'] }}">
                                    <svg class="w-8 h-8 {{ $estadoConfig['iconColor'] }}" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="{{ $estadoConfig['svgPath'] }}"></path></svg>
                                </div>
                                <p class="text-sm font-semibold mb-2 {{ $estadoConfig['badge'] }}">{{ $estadoConfig['label'] }}</p>
                                <p class="text-xs {{ $estadoConfig['desc'] }}">{{ $estadoConfig['msg'] }}</p>
                                @if($esCotizado && $cotizacion->precio_total)
                                    <div class="mt-4 pt-4 border-t border-green-200">
                                        <p class="text-xs text-green-600 font-medium uppercase tracking-wide mb-1">Total presupuestado</p>
                                        <p class="text-2xl font-bold text-green-800">${{ number_format($cotizacion->precio_total, 2, ',', '.') }}</p>
                                    </div>
                                @endif
                            </div>
                        </div>

                        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
                            <h3 class="text-xs font-bold text-gray-400 uppercase tracking-wider mb-4">Vendedor Asignado</h3>
                            <div class="flex items-center gap-4">
                                <div class="h-12 w-12 rounded-full bg-gray-100 flex items-center justify-center overflow-hidden border border-gray-200">
                                    @if($cotizacion->empleado && $cotizacion->empleado->foto)
                                        <img src="{{ asset($cotizacion->empleado->foto) }}" class="h-full w-full object-cover">
                                    @else
                                        <span class="text-lg font-bold text-gray-400">{{ substr($cotizacion->empleado->nombre ?? 'A', 0, 1) }}</span>
                                    @endif
                                </div>
                                <div>
                                    <p class="font-medium text-gray-900">{{ $cotizacion->empleado->nombre ?? 'Sin asignar' }}</p>
                                    <p class="text-xs text-gray-500">{{ $cotizacion->empleado->email ?? '' }}</p>
                                </div>
                            </div>
                        </div>

                        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
                            <h3 class="text-xs font-bold text-gray-400 uppercase tracking-wider mb-4">Datos de Facturación</h3>
                            <div class="text-sm text-gray-600 space-y-2">
                                @if($cotizacion->empresa)
                                    <div>
                                        <span class="block text-xs text-gray-400 uppercase font-bold tracking-wider mb-1">Razón Social</span>
                                        <p class="font-medium text-gray-900">{{ $cotizacion->empresa->razon_social ?? $cotizacion->empresa->nombre }}</p>
                                    </div>
                                    @if($cotizacion->empresa->cod_cond_iva)
                                    <div>
                                        <span class="block text-xs text-gray-400 uppercase font-bold tracking-wider mb-1">Condición IVA</span>
                                        <p class="font-medium text-gray-900">
                                            @if($cotizacion->empresa->cod_cond_iva === 'CF') Consumidor Final
                                            @elseif($cotizacion->empresa->cod_cond_iva === 'RI') Responsable Inscripto
                                            @elseif($cotizacion->empresa->cod_cond_iva === 'MT') Monotributo
                                            @elseif($cotizacion->empresa->cod_cond_iva === 'EX') Exento
                                            @else {{ $cotizacion->empresa->cod_cond_iva }}
                                            @endif
                                        </p>
                                    </div>
                                    @endif
                                    @if($cotizacion->empresa->cuit)
                                    <div>
                                        <span class="block text-xs text-gray-400 uppercase font-bold tracking-wider mb-1">CUIT</span>
                                        <p class="font-mono font-medium text-gray-900">{{ $cotizacion->empresa->cuit }}</p>
                                    </div>
                                    @endif
                                @elseif($cotizacion->persona)
                                    <p class="font-medium text-gray-900">{{ trim(($cotizacion->persona->nombre ?? '') . ' ' . ($cotizacion->persona->apellido ?? '')) }}</p>
                                    @if($cotizacion->persona->dni)
                                        <p>DNI: {{ $cotizacion->persona->dni }}</p>
                                    @endif
                                @else
                                    <p class="italic text-gray-400">Sin datos asociados</p>
                                @endif
                            </div>
                        </div>

                    </div>
                </div>

            </div>
        </main>
    </div>
</div>
{{-- ============================================================ --}}
{{-- ÁREA DE IMPRESIÓN (solo visible al imprimir)               --}}
{{-- ============================================================ --}}
<div id="print-area">

    {{-- Encabezado: Malkoni + datos cotización --}}
    <div class="print-header">
        <div class="print-company">
            <div class="print-logo-text">MALKONI HNOS.</div>
            <div class="print-company-data">
                <span>CUIT: 30-71234567-0</span>
                <span>Av. Corrientes 1234, Buenos Aires</span>
                <span>Tel: (011) 4123-4567</span>
                <span>ventas@malkonihnos.com</span>
            </div>
        </div>
        <div class="print-doc-info">
            <div class="print-doc-title">COTIZACIÓN</div>
            <div class="print-doc-number">#{{ str_pad($cotizacion->numero, 7, '0', STR_PAD_LEFT) }}</div>
            <table class="print-meta-table">
                <tr><td>Fecha emisión:</td><td>{{ $cotizacion->fyh->timezone('America/Argentina/Buenos_Aires')->format('d/m/Y') }}</td></tr>
                @if($cotizacion->fecha_cotizado)
                <tr><td>Fecha presupuesto:</td><td>{{ $cotizacion->fecha_cotizado->timezone('America/Argentina/Buenos_Aires')->format('d/m/Y') }}</td></tr>
                @endif
                <tr><td>Estado:</td><td><strong>{{ $estadoNombre }}</strong></td></tr>
            </table>
        </div>
    </div>

    <hr class="print-divider">

    {{-- Cliente + Vendedor --}}
    <div class="print-parties">
        <div class="print-party">
            <div class="print-party-title">CLIENTE</div>
            @if($cotizacion->empresa)
                <p class="print-party-name">{{ $cotizacion->empresa->razon_social ?? $cotizacion->empresa->nombre }}</p>
                @if($cotizacion->empresa->cuit)<p>CUIT: {{ $cotizacion->empresa->cuit }}</p>@endif
                @if($cotizacion->empresa->email)<p>{{ $cotizacion->empresa->email }}</p>@endif
            @elseif($cotizacion->persona)
                <p class="print-party-name">{{ trim(($cotizacion->persona->nombre ?? '') . ' ' . ($cotizacion->persona->apellido ?? '')) }}</p>
                @if($cotizacion->persona->dni)<p>DNI: {{ $cotizacion->persona->dni }}</p>@endif
                @if($cotizacion->persona->email)<p>{{ $cotizacion->persona->email }}</p>@endif
            @endif
        </div>
        <div class="print-party">
            <div class="print-party-title">VENDEDOR ASIGNADO</div>
            <p class="print-party-name">{{ $cotizacion->empleado->nombre ?? 'Sin asignar' }}</p>
            @if($cotizacion->empleado?->email)<p>{{ $cotizacion->empleado->email }}</p>@endif
        </div>
    </div>

    <hr class="print-divider">

    {{-- Tabla de productos --}}
    <table class="print-table">
        <thead>
            <tr>
                <th class="text-left" style="width:50%">Producto</th>
                <th class="text-center" style="width:10%">Cant.</th>
                @if($esCotizado)
                    <th class="text-right" style="width:20%">P. Unitario</th>
                    <th class="text-right" style="width:20%">Subtotal</th>
                @endif
            </tr>
        </thead>
        <tbody>
            @forelse($cotizacion->items as $item)
                <tr>
                    <td>
                        @if($item->producto)
                            <strong>{{ $item->producto->nombre }}</strong>
                            @if($item->producto->codigo)
                                <br><small>COD: {{ $item->producto->codigo }}</small>
                            @endif
                        @else
                            @php $po = is_string($cotizacion->payload_origen) ? json_decode($cotizacion->payload_origen, true) : $cotizacion->payload_origen; @endphp
                            <strong>{{ $po['mat_descri'] ?? 'Material de corte OPT' }}</strong>
                        @endif
                    </td>
                    <td class="text-center">{{ $item->cantidad }}</td>
                    @if($esCotizado)
                        <td class="text-right">${{ number_format($item->precio_unitario ?? 0, 2, ',', '.') }}</td>
                        <td class="text-right">${{ number_format(($item->precio_unitario ?? 0) * ($item->cantidad ?? 1), 2, ',', '.') }}</td>
                    @endif
                </tr>
            @empty
                <tr><td colspan="{{ $esCotizado ? 4 : 2 }}" class="text-center">Sin productos</td></tr>
            @endforelse
        </tbody>
        @if($esCotizado && $cotizacion->precio_total)
            <tfoot>
                <tr>
                    <td colspan="{{ $esCotizado ? 3 : 1 }}" class="text-right"><strong>TOTAL</strong></td>
                    <td class="text-right print-total">${{ number_format($cotizacion->precio_total, 2, ',', '.') }}</td>
                </tr>
            </tfoot>
        @endif
    </table>

    {{-- Pie de página --}}
    <div class="print-footer">
        Documento generado el {{ now()->timezone('America/Argentina/Buenos_Aires')->format('d/m/Y H:i') }} hs.
    </div>

</div>
{{-- ============================================================ --}}

@push('scripts')
<style>
/* Ocultar el área de impresión en pantalla */
#print-area { display: none; }

@media print {
    @page {
        size: A4 portrait;
        margin: 15mm 18mm;
    }

    /* Ocultar toda la UI de la app */
    body > * { display: none !important; }

    /* Mostrar solo el área de impresión */
    #print-area {
        display: block !important;
        width: 100%;
        font-family: Arial, sans-serif;
        font-size: 11pt;
        color: #111;
    }

    /* Encabezado */
    .print-header {
        display: flex;
        justify-content: space-between;
        align-items: flex-start;
        margin-bottom: 12pt;
    }
    .print-logo-text {
        font-size: 22pt;
        font-weight: 900;
        letter-spacing: 2px;
        color: #D88429;
    }
    .print-company-data {
        display: flex;
        flex-direction: column;
        gap: 2pt;
        font-size: 9pt;
        color: #555;
        margin-top: 4pt;
    }
    .print-doc-info {
        text-align: right;
    }
    .print-doc-title {
        font-size: 14pt;
        font-weight: bold;
        letter-spacing: 3px;
        color: #333;
    }
    .print-doc-number {
        font-size: 20pt;
        font-weight: 900;
        color: #D88429;
        margin-bottom: 6pt;
    }
    .print-meta-table td {
        padding: 1pt 6pt 1pt 0;
        font-size: 9pt;
        color: #555;
    }
    .print-meta-table td:first-child {
        color: #999;
        white-space: nowrap;
    }

    /* Divisor */
    .print-divider {
        border: none;
        border-top: 1.5px solid #ddd;
        margin: 10pt 0;
    }

    /* Partes: cliente + vendedor */
    .print-parties {
        display: flex;
        gap: 20pt;
        margin-bottom: 12pt;
    }
    .print-party {
        flex: 1;
        padding: 8pt 10pt;
        border: 1px solid #e5e7eb;
        border-radius: 4pt;
        background: #fafafa;
    }
    .print-party-title {
        font-size: 7pt;
        font-weight: bold;
        letter-spacing: 2px;
        color: #999;
        text-transform: uppercase;
        margin-bottom: 4pt;
    }
    .print-party-name {
        font-size: 11pt;
        font-weight: bold;
        color: #111;
        margin-bottom: 2pt;
    }
    .print-party p {
        margin: 0;
        font-size: 9pt;
        color: #555;
    }

    /* Tabla de productos */
    .print-table {
        width: 100%;
        border-collapse: collapse;
        margin-bottom: 10pt;
        font-size: 10pt;
    }
    .print-table thead tr {
        background: #f3f4f6;
        border-bottom: 2px solid #d1d5db;
    }
    .print-table th {
        padding: 6pt 8pt;
        font-size: 8pt;
        font-weight: bold;
        letter-spacing: 1px;
        text-transform: uppercase;
        color: #6b7280;
    }
    .print-table td {
        padding: 6pt 8pt;
        border-bottom: 1px solid #f0f0f0;
        vertical-align: top;
    }
    .print-table td small {
        font-size: 8pt;
        color: #999;
    }
    .print-table tbody tr:last-child td {
        border-bottom: 1.5px solid #d1d5db;
    }
    .print-table tfoot td {
        padding: 8pt;
        border-top: 2px solid #333;
    }
    .print-total {
        font-size: 14pt;
        font-weight: 900;
        color: #111;
    }
    .text-left   { text-align: left; }
    .text-center { text-align: center; }
    .text-right  { text-align: right; }

    /* Pie de página */
    .print-footer {
        margin-top: 16pt;
        font-size: 8pt;
        color: #bbb;
        text-align: center;
        border-top: 1px solid #eee;
        padding-top: 6pt;
    }
}
</style>
<script>
(function () {
    const COTIZACION_ID = {{ $cotizacion->id }};
    const URL_INDEX    = '{{ route("cliente.cotizacion.mensajes.index",  ["id" => $cotizacion->id]) }}';
    const URL_STORE    = '{{ route("cliente.cotizacion.mensajes.store",  ["id" => $cotizacion->id]) }}';
    const URL_LEIDOS   = '{{ route("cliente.cotizacion.mensajes.leidos", ["id" => $cotizacion->id]) }}';
    const CSRF         = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

    const container = document.getElementById('chat-mensajes');
    const form      = document.getElementById('chat-form');
    const input     = document.getElementById('chat-input');
    const empty     = document.getElementById('chat-empty');

    let lastId = 0;
    let polling = null;

    function buildBubble(msg) {
        const wrap = document.createElement('div');
        wrap.className = 'flex flex-col ' + (msg.mine ? 'items-end' : 'items-start');
        wrap.dataset.id = msg.id;

        const bubble = document.createElement('div');
        bubble.className = [
            'max-w-xs lg:max-w-sm px-4 py-2 rounded-2xl text-sm break-words',
            msg.mine
                ? 'bg-[#D88429] text-white rounded-br-sm'
                : 'bg-white border border-gray-200 text-gray-800 rounded-bl-sm shadow-sm',
        ].join(' ');
        bubble.textContent = msg.mensaje;

        const meta = document.createElement('span');
        meta.className = 'text-[10px] text-gray-400 mt-0.5 px-1';
        meta.textContent = (msg.mine ? '' : msg.sender_nombre + ' · ') + msg.created_at;

        wrap.appendChild(bubble);
        wrap.appendChild(meta);
        return wrap;
    }

    function scrollBottom() {
        container.scrollTop = container.scrollHeight;
    }

    async function cargarMensajes() {
        try {
            const res  = await fetch(URL_INDEX, { headers: { 'X-Requested-With': 'XMLHttpRequest' } });
            const data = await res.json();

            const nuevos = data.mensajes.filter(m => m.id > lastId);
            if (nuevos.length === 0) return;

            if (data.mensajes.length > 0 && empty) empty.remove();

            nuevos.forEach(msg => {
                container.appendChild(buildBubble(msg));
                lastId = Math.max(lastId, msg.id);
            });

            scrollBottom();

            // Marcar como leídos los del vendedor
            fetch(URL_LEIDOS, {
                method: 'POST',
                headers: { 'X-CSRF-TOKEN': CSRF, 'X-Requested-With': 'XMLHttpRequest' },
            });
        } catch (e) {
            // fallo silencioso — el polling reintentará
        }
    }

    form.addEventListener('submit', async (e) => {
        e.preventDefault();
        const texto = input.value.trim();
        if (!texto) return;

        const btn = form.querySelector('button[type="submit"]');
        btn.disabled = true;
        input.value = '';

        try {
            const res  = await fetch(URL_STORE, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': CSRF,
                    'X-Requested-With': 'XMLHttpRequest',
                },
                body: JSON.stringify({ mensaje: texto }),
            });
            const data = await res.json();
            if (data.success) {
                if (empty && empty.parentNode) empty.remove();
                container.appendChild(buildBubble(data.mensaje));
                lastId = Math.max(lastId, data.mensaje.id);
                scrollBottom();
            }
        } catch (e) {
            input.value = texto; // restaurar si falla
        } finally {
            btn.disabled = false;
            input.focus();
        }
    });

    // Carga inicial + polling cada 5s
    cargarMensajes();
    polling = setInterval(cargarMensajes, 5000);

    // Detener polling si la página pierde foco (ahorro de requests)
    document.addEventListener('visibilitychange', () => {
        if (document.hidden) {
            clearInterval(polling);
        } else {
            cargarMensajes();
            polling = setInterval(cargarMensajes, 5000);
        }
    });
})();
</script>
@endpush

@endsection
