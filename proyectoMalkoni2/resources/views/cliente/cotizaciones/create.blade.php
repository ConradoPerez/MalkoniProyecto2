@extends('layouts.app')

@section('title', 'Nueva Cotización')

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
                                Volver
                            </a>
                        </li>
                        <li>
                            <div class="flex items-center">
                                <svg class="w-6 h-6 text-gray-300" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd"></path></svg>
                                <span class="ml-1 text-sm font-medium text-gray-400">Nueva Cotización</span>
                            </div>
                        </li>
                    </ol>
                </nav>

                <div class="max-w-5xl mx-auto">
                    
                    <div class="flex flex-col md:flex-row md:items-center justify-between mb-8 gap-4">
                        <div>
                            <h2 class="text-2xl font-syncopate font-bold text-gray-800">NUEVA SOLICITUD</h2>
                            <p class="text-gray-500 mt-1 font-medium">Completa los datos para iniciar el proceso de cotización.</p>
                        </div>
                        
                        <div class="bg-white px-5 py-3 rounded-lg border border-gray-200 shadow-sm flex flex-wrap gap-4 md:gap-6 text-sm items-center">
                            <div>
                                <span class="block text-xs text-gray-400 uppercase font-bold tracking-wider">Fecha</span>
                                <span class="font-mono font-medium text-gray-700">{{ now()->timezone('America/Argentina/Buenos_Aires')->format('d/m/Y') }}</span>
                            </div>
                            <div class="border-l border-gray-200 pl-4 md:pl-6">
                                <span class="block text-xs text-gray-400 uppercase font-bold tracking-wider">Nº Pedido</span>
                                <span class="font-mono font-medium text-[#D88429]">{{ $numero_pedido ?? '---' }}</span>
                            </div>
                            @if(isset($cotizacion) && $cotizacion && $cotizacion->pedido_opt_id)
                                <div class="border-l border-gray-200 pl-4 md:pl-6">
                                    <span class="block text-xs text-gray-400 uppercase font-bold tracking-wider">Cliente</span>
                                    <span class="font-medium text-gray-700">{{ trim(($cotizacion->persona->nombre ?? '') . ' ' . ($cotizacion->persona->apellido ?? '')) ?: 'Sin datos' }}</span>
                                </div>
                                @if($cotizacion->empresa && $cotizacion->empresa->cod_cond_iva !== 'CF')
                                    <div class="border-l border-gray-200 pl-4 md:pl-6">
                                        <span class="block text-xs text-gray-400 uppercase font-bold tracking-wider">Empresa Activa</span>
                                        <span class="font-medium text-gray-700">{{ $cotizacion->empresa->razon_social ?? $cotizacion->empresa->nombre ?? 'Sin empresa' }}</span>
                                    </div>
                                @endif
                                @if($cotizacion->empresa && $cotizacion->empresa->cuit)
                                    <div class="border-l border-gray-200 pl-4 md:pl-6">
                                        <span class="block text-xs text-gray-400 uppercase font-bold tracking-wider">{{ $cotizacion->empresa->cod_cond_iva === 'CF' ? 'CUIL' : 'CUIT' }}</span>
                                        <span class="font-mono font-medium text-gray-700">{{ $cotizacion->empresa->cuit }}</span>
                                    </div>
                                @endif
                            @endif
                        </div>
                    </div>

                    <form action="{{ route('cliente.cotizacion.preparar') }}" method="POST" id="formCotizacion">
                        @csrf
                        <input type="hidden" name="numero_pedido" value="{{ $numero_pedido }}">
                        <input type="hidden" name="cotizacion_id" value="{{ $cotizacion->id ?? '' }}">

                        <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden mb-8">
                            <div class="p-6 border-b border-gray-100 bg-gray-50/50">
                                <h3 class="text-lg font-semibold text-gray-800">1. Selecciona tu Asesor</h3>
                                <p class="text-sm text-gray-500">Elige quién gestionará tu cotización.</p>
                            </div>

                            <div class="p-6 md:p-8">
                                <div class="grid grid-cols-2 md:grid-cols-4 lg:grid-cols-5 gap-6">
                                    @forelse($vendedores as $vendedor)
                                        <label class="relative cursor-pointer group">
                                            <input type="radio" name="id_empleados" value="{{ $vendedor->id_empleado }}" class="peer sr-only" required {{ (int) old('id_empleados', $cotizacion->id_empleados ?? 0) === (int) $vendedor->id_empleado ? 'checked' : '' }}>
                                            
                                            <div class="flex flex-col items-center p-4 rounded-xl border-2 border-transparent bg-gray-50 transition-all duration-200 hover:bg-white hover:shadow-md hover:border-gray-200 peer-checked:border-[#D88429] peer-checked:bg-white peer-checked:shadow-lg peer-checked:scale-105">
                                                
                                                <div class="relative w-20 h-20 mb-3">
                                                    @if($vendedor->foto)
                                                        <img src="{{ asset($vendedor->foto) }}" alt="{{ $vendedor->nombre }}" class="w-full h-full rounded-full object-cover border-2 border-white shadow-sm">
                                                    @else
                                                        <div class="w-full h-full rounded-full bg-gray-200 border-2 border-white shadow-sm flex items-center justify-center text-gray-400">
                                                            <svg class="w-10 h-10" fill="currentColor" viewBox="0 0 24 24"><path d="M12 12c2.21 0 4-1.79 4-4s-1.79-4-4-4-4 1.79-4 4 1.79 4 4 4zm0 2c-2.67 0-8 1.34-8 4v2h16v-2c0-2.66-5.33-4-8-4z" /></svg>
                                                        </div>
                                                    @endif
                                                    
                                                    <div class="absolute bottom-0 right-0 bg-[#D88429] text-white rounded-full p-1 shadow-sm opacity-0 peer-checked:opacity-100 transition-opacity transform scale-75 peer-checked:scale-100">
                                                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"></path></svg>
                                                    </div>
                                                </div>

                                                <span class="text-sm font-semibold text-gray-700 text-center leading-tight group-hover:text-gray-900 peer-checked:text-[#D88429]">
                                                    {{ $vendedor->nombre }}
                                                </span>
                                                <span class="text-xs text-gray-400 mt-1">Vendedor</span>
                                            </div>
                                        </label>
                                    @empty
                                        <div class="col-span-full text-center py-8 text-gray-500 bg-gray-50 rounded-lg border border-dashed border-gray-300">
                                            No hay vendedores disponibles en este momento.
                                        </div>
                                    @endforelse
                                </div>
                                
                                @error('id_empleados')
                                    <p class="text-red-500 text-sm mt-4 text-center bg-red-50 p-2 rounded border border-red-100">
                                        ⚠️ Por favor selecciona un vendedor para continuar.
                                    </p>
                                @enderror
                            </div>
                        </div>

                        @if(isset($cotizacion) && $cotizacion && $cotizacion->pdf_url)
                        <div class="bg-white rounded-xl border border-gray-200 overflow-hidden mb-8 shadow-sm">
                            <div class="p-5 border-b border-gray-200 bg-gray-50/50 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                                <div>
                                    <h3 class="text-base font-semibold text-gray-800 flex items-center gap-2">
                                        <svg class="w-5 h-5 text-[#D88429]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                                        Plano de Cortes
                                    </h3>
                                    <p class="text-xs text-gray-500 mt-0.5">Visualización interactiva del archivo de cortes importado.</p>
                                </div>
                                <div class="flex items-center gap-3">
                                    <a href="{{ $cotizacion->pdf_url }}" target="_blank" class="inline-flex items-center gap-2 px-4 py-2 rounded-lg text-xs font-bold text-gray-700 bg-white border border-gray-300 hover:bg-gray-50 transition-all shadow-sm">
                                        <svg class="w-4 h-4 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"></path></svg>
                                        Abrir en ventana completa
                                    </a>
                                </div>
                            </div>
                            <div class="relative w-full bg-gray-100" style="height: 950px;">
                                <iframe src="{{ $cotizacion->pdf_url }}" class="w-full h-full border-0" style="height: 950px;" title="Plano de cortes"></iframe>
                            </div>
                        </div>
                        @endif

                        <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden mb-8">
                            <div class="p-6 border-b border-gray-100 bg-gray-50/50">
                                <h3 class="text-lg font-semibold text-gray-800">{{ isset($cotizacion) && $cotizacion && $cotizacion->pedido_opt_id ? '2' : '2' }}. Mensaje Inicial <span class="text-gray-400 font-normal text-sm">(Opcional)</span></h3>
                                <p class="text-sm text-gray-500">Añade notas o instrucciones especiales para el vendedor.</p>
                            </div>
                            
                            <div class="p-6">
                                <textarea name="mensaje_inicial" 
                                          rows="4"
                                          placeholder="Escribe aquí cualquier detalle importante sobre tu pedido..." 
                                          class="w-full px-4 py-3 rounded-lg border border-gray-200 bg-gray-50 text-gray-900 focus:bg-white focus:border-[#D88429] focus:ring-2 focus:ring-[#D88429]/20 outline-none transition-all resize-none placeholder-gray-400">{{ old('mensaje_inicial') }}</textarea>
                            </div>
                        </div>

                        <div class="flex flex-col-reverse md:flex-row items-center justify-end gap-4 pt-4">
                            <a href="{{ route('cliente.dashboard') }}" class="w-full md:w-auto px-6 py-3 text-sm font-medium text-gray-600 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 hover:text-gray-900 transition-colors text-center shadow-sm">
                                Cancelar
                            </a>
                            
                            <button type="submit" id="btnContinuar" class="w-full md:w-auto px-8 py-3 text-sm font-bold text-white bg-[#D88429] rounded-lg hover:bg-[#c7731f] focus:ring-4 focus:ring-[#D88429]/30 transition-all shadow-md hover:shadow-lg flex items-center justify-center group">
                                Continuar y Agregar Productos
                                <svg class="w-5 h-5 ml-2 transform group-hover:translate-x-1 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"></path>
                                </svg>
                            </button>
                        </div>

                    </form>

                </div>
            </div>
        </main>
    </div>
</div>
@endsection
