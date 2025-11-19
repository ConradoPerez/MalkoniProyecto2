@extends('layouts.app')

@section('title', 'Detalle de Cotización')

@section('content')
<div class="min-h-screen text-gray-900" style="background-color: #e1dfd9;">
    <div class="flex">
        @include('cliente.components.sidebar')

        <main class="flex-1 overflow-y-auto md:ml-64 transition-all duration-300">
            
            <div class="p-4 lg:p-8">
                @include('cliente.components.header')

                <nav class="flex mb-6 mt-4" aria-label="Breadcrumb">
                    <ol class="inline-flex items-center space-x-1 md:space-x-3">
                        <li>
                            <a href="{{ route('cliente.dashboard', ['persona_id' => $personaId]) }}" class="inline-flex items-center text-sm font-medium text-gray-500 hover:text-[#D88429]">
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
                        <button class="inline-flex items-center px-4 py-2 bg-gray-900 text-white rounded-lg text-sm font-medium hover:bg-gray-800 transition-all shadow-md">
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

                            <div class="overflow-x-auto">
                                <table class="w-full whitespace-nowrap">
                                    <thead class="bg-gray-50">
                                        <tr class="text-left text-xs font-semibold tracking-wide text-gray-500 uppercase border-b border-gray-100">
                                            <th class="px-6 py-3">Producto</th>
                                            <th class="px-6 py-3 text-center">Cant.</th>
                                            <th class="px-6 py-3 text-right">Precio Unit.</th>
                                            <th class="px-6 py-3 text-right">Total</th>
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
                                                            <p class="font-medium text-gray-900">{{ $item->producto->nombre ?? 'Producto no disponible' }}</p>
                                                            <p class="text-xs text-gray-500">COD: {{ $item->producto->codigo ?? '---' }}</p>
                                                        </div>
                                                    </div>
                                                </td>
                                                <td class="px-6 py-4 text-center">
                                                    <span class="inline-flex items-center justify-center px-2 py-1 text-xs font-bold leading-none text-gray-700 bg-gray-100 rounded">
                                                        {{ $item->cantidad }}
                                                    </span>
                                                </td>
                                                <td class="px-6 py-4 text-right text-sm text-gray-600">
                                                    ${{ number_format(($item->producto->precio_final ?? 0) / 100, 2, ',', '.') }}
                                                </td>
                                                <td class="px-6 py-4 text-right text-sm font-medium text-gray-900">
                                                    ${{ number_format((($item->producto->precio_final ?? 0) * $item->cantidad) / 100, 2, ',', '.') }}
                                                </td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="4" class="px-6 py-10 text-center text-gray-500">
                                                    No hay items en esta cotización. 
                                                    <a href="{{ route('cliente.cotizacion.agregar_productos', ['id' => $cotizacion->id, 'persona_id' => $personaId]) }}" class="text-[#D88429] hover:underline ml-1">Agregar productos</a>
                                                </td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                            
                            <div class="bg-gray-50 px-6 py-3 border-t border-gray-100">
                                <a href="{{ route('cliente.cotizacion.agregar_productos', ['id' => $cotizacion->id, 'persona_id' => $personaId]) }}" class="text-sm font-medium text-[#166379] hover:text-[#0e4555] hover:underline flex items-center justify-center sm:justify-start">
                                    <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                                    Agregar más productos
                                </a>
                            </div>
                        </div>

                        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
                            <h3 class="font-bold text-gray-800 mb-4 flex items-center gap-2">
                                <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z"></path></svg>
                                Comunicación con el Vendedor
                            </h3>
                            <div class="bg-gray-50 rounded-lg border border-gray-200 p-8 text-center">
                                <p class="text-gray-500 text-sm mb-3">¿Tienes dudas sobre este pedido?</p>
                                <button class="px-4 py-2 bg-white border border-gray-300 text-gray-700 rounded-lg text-sm font-medium hover:bg-gray-50 transition-colors shadow-sm">
                                    Enviar Mensaje
                                </button>
                            </div>
                        </div>
                    </div>

                    <div class="space-y-6">
                        
                        <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
                            <div class="p-6 bg-gray-900 text-white">
                                <h3 class="text-lg font-syncopate font-bold">Total Estimado</h3>
                            </div>
                            <div class="p-6 space-y-3">
                                <div class="flex justify-between text-sm text-gray-600">
                                    <span>Subtotal</span>
                                    <span>${{ number_format($cotizacion->precio_total / 100, 2, ',', '.') }}</span>
                                </div>
                                <div class="flex justify-between text-sm text-gray-600">
                                    <span>Impuestos (Est.)</span>
                                    <span>$0,00</span>
                                </div>
                                <div class="border-t border-gray-100 pt-3 flex justify-between items-center">
                                    <span class="font-bold text-gray-900">Total</span>
                                    <span class="text-2xl font-bold text-[#D88429]">${{ number_format($cotizacion->precio_total / 100, 2, ',', '.') }}</span>
                                </div>
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
                            <div class="text-sm text-gray-600 space-y-1">
                                @if($cotizacion->empresa)
                                    <p class="font-medium text-gray-900">{{ $cotizacion->empresa->nombre }}</p>
                                    <p>CUIT: {{ $cotizacion->empresa->cuit }}</p>
                                @elseif($cotizacion->persona)
                                    <p class="font-medium text-gray-900">{{ $cotizacion->persona->nombre }}</p>
                                    <p>DNI: {{ $cotizacion->persona->dni }}</p>
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
@endsection
