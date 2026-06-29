@extends('layouts.app')

@section('title', 'Confirmar Cotización')

@section('content')
<div class="min-h-screen text-gray-900" style="background-color: #e1dfd9;">
    <div class="flex">
        @include('cliente.components.sidebar')

        <main class="flex-1 overflow-y-auto lg:ml-56 transition-all duration-300">
            <!-- Mobile Header -->
            <div class="lg:hidden bg-white border-b border-gray-200 p-4 sticky top-0 z-10">
                <div class="flex items-center justify-between">
                    <button id="mobile-menu-button" class="p-2 rounded-md text-gray-600 hover:text-gray-900 hover:bg-gray-100">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"></path>
                        </svg>
                    </button>
                    <div class="flex items-center">
                        <img src="{{ asset('logo/logo negro.png') }}" alt="Malkoni Logo" class="h-8 w-auto">
                    </div>
                    <div class="flex items-center space-x-2">
                        <span class="text-xs font-medium text-gray-900">Resumen</span>
                        <div class="w-8 h-8 rounded-full bg-gray-300 flex items-center justify-center">
                            <svg class="w-4 h-4 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                                      d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                            </svg>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="p-4 lg:p-8">
                <!-- Breadcrumbs -->
                <nav class="flex mb-6 mt-4" aria-label="Breadcrumb">
                    <ol class="inline-flex items-center space-x-1 md:space-x-3">
                        <li>
                            <a href="{{ route('cliente.dashboard') }}" class="inline-flex items-center text-sm font-medium text-gray-500 hover:text-[#D88429]">
                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path></svg>
                                Panel Principal
                            </a>
                        </li>
                        <li>
                            <div class="flex items-center">
                                <svg class="w-6 h-6 text-gray-300" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd"></path></svg>
                                <span class="ml-1 text-sm font-medium text-gray-400">Resumen y Confirmación</span>
                            </div>
                        </li>
                    </ol>
                </nav>

                <!-- Pasos del Wizard -->
                <div class="mb-8 max-w-3xl mx-auto">
                    <div class="flex items-center justify-between">
                        <!-- Paso 1 -->
                        <div class="flex flex-col items-center flex-1">
                            <div class="w-10 h-10 rounded-full bg-green-500 text-white flex items-center justify-center font-bold shadow-md">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"></path>
                                </svg>
                            </div>
                            <span class="text-xs font-semibold text-gray-600 mt-2">1. Vendedor</span>
                        </div>
                        <div class="h-1 bg-green-500 flex-1 -mt-5"></div>

                        <!-- Paso 2 -->
                        <div class="flex flex-col items-center flex-1">
                            <div class="w-10 h-10 rounded-full bg-green-500 text-white flex items-center justify-center font-bold shadow-md">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"></path>
                                </svg>
                            </div>
                            <span class="text-xs font-semibold text-gray-600 mt-2">2. Catálogo</span>
                        </div>
                        <div class="h-1 bg-orange-500 flex-1 -mt-5"></div>

                        <!-- Paso 3 -->
                        <div class="flex flex-col items-center flex-1">
                            <div class="w-10 h-10 rounded-full bg-[#D88429] text-white flex items-center justify-center font-bold shadow-md">
                                3
                            </div>
                            <span class="text-xs font-bold text-gray-800 mt-2">3. Confirmar</span>
                        </div>
                    </div>
                </div>

                <!-- Cabecera de Página -->
                <div class="text-center mb-8">
                    <h1 class="text-3xl font-syncopate font-bold text-gray-900 tracking-wide uppercase">REVISAR Y CONFIRMAR</h1>
                    <p class="text-gray-600 mt-1">Por favor, comprobá los detalles de tu solicitud antes de crear la cotización.</p>
                </div>

                <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
                    <!-- Detalle principal (Columna Izquierda) -->
                    <div class="lg:col-span-2 space-y-6">
                        
                        <!-- Asesor Asignado -->
                        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
                            <h3 class="text-xs font-bold text-gray-400 uppercase tracking-wider mb-4">Vendedor Seleccionado</h3>
                            <div class="flex items-center gap-4">
                                <div class="h-12 w-12 rounded-full bg-gray-100 flex items-center justify-center overflow-hidden border border-gray-200 shrink-0">
                                    @if($vendedor && $vendedor->foto)
                                        <img src="{{ asset($vendedor->foto) }}" class="h-full w-full object-cover">
                                    @else
                                        <span class="text-lg font-bold text-gray-400">{{ substr($vendedor->nombre ?? 'A', 0, 1) }}</span>
                                    @endif
                                </div>
                                <div>
                                    <p class="font-bold text-gray-900 text-base">{{ $vendedor->nombre ?? 'Sin vendedor asignado' }}</p>
                                    <p class="text-xs text-gray-500">{{ $vendedor->email ?? 'correo@malkoni.com.ar' }}</p>
                                </div>
                            </div>
                            @if(!empty($datosCotizacion['mensaje_inicial']))
                                <div class="mt-4 p-3 bg-gray-50 rounded-lg border border-gray-100 text-sm">
                                    <p class="text-gray-500 font-semibold text-xs uppercase mb-1">Tu Mensaje inicial:</p>
                                    <p class="text-gray-700 italic">"{{ $datosCotizacion['mensaje_inicial'] }}"</p>
                                </div>
                            @endif
                        </div>

                        <!-- Tabla de Items -->
                        <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
                            <div class="px-6 py-4 border-b border-gray-100 bg-gray-50/50 flex justify-between items-center">
                                <h3 class="font-bold text-gray-800">Detalle del Pedido</h3>
                                <span class="text-xs font-medium px-2.5 py-0.5 rounded-full bg-blue-100 text-blue-800">
                                    {{ count($productosSeleccionados) + $itemsExistentes->count() }} Items
                                </span>
                            </div>

                            <div class="overflow-x-auto">
                                <table class="w-full text-sm">
                                    <thead class="bg-gray-50">
                                        <tr class="text-left font-semibold text-gray-700 border-b border-gray-100 text-xs uppercase">
                                            <th class="px-6 py-3">Item / Producto</th>
                                            <th class="px-6 py-3 text-center">Cantidad</th>
                                        </tr>
                                    </thead>
                                    <tbody class="divide-y divide-gray-100">
                                        <!-- Plano OPT (si existe) -->
                                        @foreach($itemsExistentes as $item)
                                            <tr class="hover:bg-gray-50/50 transition-colors">
                                                <td class="px-6 py-4">
                                                    <div>
                                                        <p class="font-medium text-gray-900">{{ $item->producto->nombre ?? $item->descripcion ?? 'Plano de Optimización de Cortes (OPT)' }}</p>
                                                        <span class="inline-flex items-center px-1.5 py-0.5 rounded text-[10px] font-medium bg-amber-100 text-amber-800 mt-1">Plano Importado</span>
                                                    </div>
                                                </td>
                                                <td class="px-6 py-4 text-center font-bold text-gray-800">{{ $item->cantidad }}</td>
                                            </tr>
                                        @endforeach

                                        <!-- Productos seleccionados manualmente -->
                                        @foreach($productosSeleccionados as $prod)
                                            <tr class="hover:bg-gray-50/50 transition-colors">
                                                <td class="px-6 py-4">
                                                    <div>
                                                        <p class="font-medium text-gray-900">{{ $prod['producto']->nombre }}</p>
                                                        <p class="text-xs text-gray-500">COD: {{ $prod['producto']->codigo ?? '---' }}</p>
                                                    </div>
                                                </td>
                                                <td class="px-6 py-4 text-center font-bold text-gray-800">{{ $prod['cantidad'] }}</td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>

                    </div>

                    <!-- Sidebar de Confirmación (Columna Derecha) -->
                    <div class="lg:col-span-1">
                        <div class="lg:sticky lg:top-8 space-y-6">
                            <div class="bg-white rounded-xl shadow-lg border border-gray-100 overflow-hidden">
                                <div class="p-6">
                                    <h3 class="text-lg font-bold text-gray-900 mb-4 flex items-center">
                                        <svg class="w-5 h-5 mr-2 text-[#D88429]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v10a2 2 0 002 2h8a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 00-2 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path>
                                        </svg>
                                        Confirmación
                                    </h3>

                                    <div class="p-4 bg-yellow-50 rounded-lg border border-yellow-200 text-yellow-800 text-xs leading-relaxed mb-6">
                                        <div class="flex gap-2">
                                            <svg class="w-4 h-4 shrink-0 mt-0.5 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path>
                                            </svg>
                                            <p>
                                                <strong>Importante:</strong> Al crear la cotización, se notificará a tu asesor asignado para que elabore el presupuesto. Los precios oficiales se reflejarán una vez procesada.
                                            </p>
                                        </div>
                                    </div>

                                    <!-- Acciones del Wizard -->
                                    <form action="{{ route('cliente.cotizacion.confirmar_creacion') }}" method="POST">
                                        @csrf
                                        <button type="submit" class="w-full bg-green-600 hover:bg-green-700 text-white py-3 px-4 rounded-lg font-semibold transition-all flex items-center justify-center shadow-md hover:shadow-lg mb-3">
                                            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                            </svg>
                                            Crear Cotización
                                        </button>

                                        <a href="{{ route('cliente.cotizacion.productos') }}" class="w-full border border-gray-300 hover:bg-gray-50 text-gray-700 py-2.5 px-4 rounded-lg text-sm font-semibold transition-all flex items-center justify-center">
                                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                                            </svg>
                                            Volver a Modificar Productos
                                        </a>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

            </div>
        </main>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const mobileMenuBtn = document.getElementById('mobile-menu-button');
        const mobileSidebar = document.getElementById('mobile-sidebar');
        const sidebarOverlay = document.getElementById('sidebar-overlay');
        const closeMobileBtn = document.getElementById('close-mobile-menu');

        if (mobileMenuBtn && mobileSidebar) {
            mobileMenuBtn.addEventListener('click', function() {
                mobileSidebar.classList.remove('-translate-x-full');
                sidebarOverlay.classList.remove('hidden');
            });
        }

        function closeSidebar() {
            if (mobileSidebar) mobileSidebar.classList.add('-translate-x-full');
            if (sidebarOverlay) sidebarOverlay.classList.add('hidden');
        }

        if (sidebarOverlay) sidebarOverlay.addEventListener('click', closeSidebar);
        if (closeMobileBtn) closeMobileBtn.addEventListener('click', closeSidebar);
    });
</script>
@endsection
