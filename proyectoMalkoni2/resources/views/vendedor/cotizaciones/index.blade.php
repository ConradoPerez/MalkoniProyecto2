@extends('layouts.app')

@section('title', 'Cotizaciones - Vendedor - Malkoni Hnos')

@section('content')
<div class="min-h-screen text-gray-900">
    <!-- Sidebar -->
    @include('vendedor.components.sidebar')

    <!-- Main content -->
    <main class="lg:ml-56">
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
                    <span class="text-xs font-medium text-gray-900">Cotizaciones</span>
                    <div class="w-8 h-8 rounded-full bg-gray-300 flex items-center justify-center">
                        <svg class="w-4 h-4 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                        </svg>
                    </div>
                </div>
            </div>
        </div>

        <div class="p-4 lg:p-8">

                {{-- Header mejorado --}}
                <div class="flex flex-col lg:flex-row lg:items-center justify-between mb-8 gap-4">
                    <div>
                        <h1 class="text-3xl font-syncopate font-bold text-gray-900 tracking-wide">
                            COTIZACIONES
                        </h1>
                        <p class="text-gray-600 mt-1">
                            Gestiona y busca cotizaciones de {{ isset($vendedor) ? $vendedor->nombre : 'Vendedor' }}
                        </p>
                    </div>

                    @include('vendedor.components.user_profile')
                </div>

                {{-- Total --}}
                <div class="flex items-center gap-2 mb-6">
                    <span class="text-2xl font-bold text-gray-900">{{ number_format($total) }}</span>
                    <span class="text-gray-600">cotizaciones en total</span>
                </div>

                {{-- Tabla mejorada --}}
                <section class="bg-white border border-gray-200 rounded-xl shadow-sm overflow-hidden">
                    @if($cotizaciones->count() > 0)
                        <div class="overflow-x-auto">
                            <table class="w-full">
                                <thead class="bg-gray-50 border-b border-gray-200">
                                    <tr class="text-left">
                                        <th class="px-6 py-4 text-sm font-semibold text-gray-700">
                                            <a href="{{ request()->fullUrlWithQuery(['orderby' => 'numero', 'direction' => request('orderby') == 'numero' && request('direction', 'desc') == 'desc' ? 'asc' : 'desc']) }}"
                                               class="flex items-center hover:text-[#D88429] transition-colors">
                                                N° Cotización
                                                <svg class="w-4 h-4 ml-1 {{ request('orderby') == 'numero' ? 'text-[#D88429]' : 'text-gray-400' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    @if(request('orderby') == 'numero' && request('direction', 'desc') == 'desc')
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                                                    @else
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 15l7-7 7 7"/>
                                                    @endif
                                                </svg>
                                            </a>
                                        </th>
                                        <th class="px-6 py-4 text-sm font-semibold text-gray-700">
                                            <a href="{{ request()->fullUrlWithQuery(['orderby' => 'fecha', 'direction' => request('orderby') == 'fecha' && request('direction', 'desc') == 'desc' ? 'asc' : 'desc']) }}"
                                               class="flex items-center hover:text-[#D88429] transition-colors">
                                                Fecha Creación
                                                <svg class="w-4 h-4 ml-1 {{ request('orderby', 'fecha') == 'fecha' ? 'text-[#D88429]' : 'text-gray-400' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    @if((request('orderby', 'fecha') == 'fecha') && request('direction', 'desc') == 'desc')
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                                                    @else
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 15l7-7 7 7"/>
                                                    @endif
                                                </svg>
                                            </a>
                                        </th>
                                        <th class="px-6 py-4 text-sm font-semibold text-gray-700">Cliente</th>
                                        <th class="px-6 py-4 text-sm font-semibold text-gray-700">
                                            <a href="{{ request()->fullUrlWithQuery(['orderby' => 'estado', 'direction' => request('orderby') == 'estado' && request('direction', 'desc') == 'desc' ? 'asc' : 'desc']) }}"
                                               class="flex items-center hover:text-[#D88429] transition-colors">
                                                Estado
                                                <svg class="w-4 h-4 ml-1 {{ request('orderby') == 'estado' ? 'text-[#D88429]' : 'text-gray-400' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    @if(request('orderby') == 'estado' && request('direction', 'desc') == 'desc')
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                                                    @else
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 15l7-7 7 7"/>
                                                    @endif
                                                </svg>
                                            </a>
                                        </th>
                                        <th class="px-6 py-4 text-sm font-semibold text-gray-700">
                                            <a href="{{ request()->fullUrlWithQuery(['orderby' => 'modificacion', 'direction' => request('orderby') == 'modificacion' && request('direction', 'desc') == 'desc' ? 'asc' : 'desc']) }}"
                                               class="flex items-center hover:text-[#D88429] transition-colors">
                                                Últ. Modificación
                                                <svg class="w-4 h-4 ml-1 {{ request('orderby') == 'modificacion' ? 'text-[#D88429]' : 'text-gray-400' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    @if(request('orderby') == 'modificacion' && request('direction', 'desc') == 'desc')
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                                                    @else
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 15l7-7 7 7"/>
                                                    @endif
                                                </svg>
                                            </a>
                                        </th>
                                        <th class="px-6 py-4 text-sm font-semibold text-gray-700 text-right">
                                            <a href="{{ request()->fullUrlWithQuery(['orderby' => 'monto', 'direction' => request('orderby') == 'monto' && request('direction', 'desc') == 'desc' ? 'asc' : 'desc']) }}"
                                               class="flex items-center justify-end hover:text-[#D88429] transition-colors">
                                                Monto
                                                <svg class="w-4 h-4 ml-1 {{ request('orderby') == 'monto' ? 'text-[#D88429]' : 'text-gray-400' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    @if(request('orderby') == 'monto' && request('direction', 'desc') == 'desc')
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                                                    @else
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 15l7-7 7 7"/>
                                                    @endif
                                                </svg>
                                            </a>
                                        </th>
                                        <th class="px-6 py-4 text-sm font-semibold text-gray-700 text-center">Acciones</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-200">
                                    @php
                                        $estadoColores = [
                                            'Nuevo' => 'bg-blue-100 text-blue-800',
                                            'Abierto' => 'bg-yellow-100 text-yellow-800',
                                            'Cotizado' => 'bg-green-100 text-green-800',
                                            'En entrega' => 'bg-purple-100 text-purple-800',
                                            'Cancelada' => 'bg-red-100 text-red-800',
                                        ];
                                    @endphp
                                    
                                    @foreach($cotizaciones as $cotizacion)
                                        <tr class="hover:bg-gray-50 transition-colors">
                                            <td class="px-6 py-4">
                                                <span class="text-sm font-bold text-gray-900">#{{ $cotizacion->numero }}</span>
                                            </td>
                                            <td class="px-6 py-4">
                                                <span class="text-sm text-gray-700">{{ $cotizacion->fyh ? $cotizacion->fyh->timezone('America/Argentina/Buenos_Aires')->format('d/m/Y') : '-' }}</span>
                                            </td>
                                            <td class="px-6 py-4">
                                                <span class="text-sm font-medium text-gray-900">{{ $cotizacion->cliente_nombre }}</span>
                                            </td>
                                            <td class="px-6 py-4">
                                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $estadoColores[$cotizacion->estado_actual->nombre ?? 'Nuevo'] ?? 'bg-gray-100 text-gray-800' }}">
                                                    {{ $cotizacion->estado_actual->nombre ?? 'Sin estado' }}
                                                </span>
                                            </td>
                                            <td class="px-6 py-4">
                                                @if($cotizacion->updated_at && $cotizacion->updated_at != $cotizacion->created_at)
                                                    <span class="text-sm text-gray-700">{{ $cotizacion->updated_at->timezone('America/Argentina/Buenos_Aires')->format('d/m/Y') }}</span>
                                                @else
                                                    <span class="text-sm text-gray-400">—</span>
                                                @endif
                                            </td>
                                            <td class="px-6 py-4 text-right">
                                                @php
                                                    $estadoNombre = $cotizacion->estado_actual->nombre ?? 'Sin estado';
                                                    $esCotizable = in_array($estadoNombre, ['Nuevo', 'Abierto']);
                                                    $tienePrecio = !$esCotizable && $cotizacion->precio_total && $cotizacion->precio_total > 0;
                                                @endphp
                                                
                                                @if($tienePrecio)
                                                    <span class="text-sm font-bold text-gray-900">${{ number_format($cotizacion->precio_total, 0, ',', '.') }}</span>
                                                @else
                                                    <span class="text-sm text-gray-500 italic">Sin cotizar</span>
                                                @endif
                                            </td>
                                            <td class="px-6 py-4">
                                                <div class="flex items-center justify-center gap-2">
                                                    @php
                                                        $esEnEntrega = $estadoNombre == 'En entrega';
                                                    @endphp
                                                    
                                                    @if(!$tienePrecio && $esCotizable)
                                                        {{-- Botón Cotizar (solo cuando no tiene precio y es cotizable - estados Nuevo/Abierto) --}}
                                                        <a href="{{ route('vendedor.app.cotizaciones.detalle', ['id' => $cotizacion->id]) }}" 
                                                           class="inline-flex items-center px-3 py-1.5 rounded-lg text-white text-sm font-semibold transition hover:opacity-90"
                                                           style="background-color:#D88429;">
                                                            <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 11h.01M12 11h.01M9 11h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z"/>
                                                            </svg>
                                                            Cotizar
                                                        </a>
                                                    @elseif($tienePrecio)
                                                        {{-- Botón Editar (solo cuando ya tiene precio - estados Cotizado/En entrega) --}}
                                                        @if($esEnEntrega)
                                                            <button disabled
                                                                    class="inline-flex items-center px-3 py-1.5 rounded-lg text-gray-400 text-sm font-semibold cursor-not-allowed bg-gray-200"
                                                                    title="No se puede editar una cotización en entrega">
                                                                <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                                                </svg>
                                                                Editar
                                                            </button>
                                                        @else
                                                            <a href="{{ route('vendedor.app.cotizaciones.detalle', ['id' => $cotizacion->id]) }}" 
                                                               class="inline-flex items-center px-3 py-1.5 rounded-lg text-white text-sm font-semibold transition hover:opacity-90"
                                                               style="background-color:#172A32;">
                                                                <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                                                </svg>
                                                                Editar
                                                            </a>
                                                        @endif
                                                    @endif
                                                    
                                                    {{-- Botón Ver detalle (siempre visible) --}}
                                                    <a href="{{ route('vendedor.app.cotizaciones.detalle', ['id' => $cotizacion->id]) }}" 
                                                       class="inline-flex items-center px-3 py-1.5 border border-gray-300 rounded-lg text-gray-700 text-sm font-medium bg-white hover:bg-gray-50 transition-colors">
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
                                </tbody>
                            </table>
                        </div>

                        {{-- Paginación --}}
                        @if($cotizaciones->hasPages())
                            <div class="px-6 py-4 border-t border-gray-200 bg-gray-50">
                                <div class="flex items-center justify-between">
                                    <div class="text-sm text-gray-700">
                                        Mostrando {{ $cotizaciones->firstItem() }} a {{ $cotizaciones->lastItem() }} de {{ $cotizaciones->total() }} resultados
                                    </div>
                                    <div>
                                        {{ $cotizaciones->appends(request()->query())->links('custom.pagination') }}
                                    </div>
                                </div>
                            </div>
                        @endif
                    @else
                        {{-- Estado vacío --}}
                        <div class="text-center py-16">
                            <div class="w-16 h-16 mx-auto mb-4 rounded-full bg-gray-100 flex items-center justify-center">
                                <svg class="w-8 h-8 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                </svg>
                            </div>
                            <h3 class="text-lg font-medium text-gray-900 mb-2">No se encontraron cotizaciones</h3>
                            <p class="text-gray-500 mb-4">
                                @if(request()->hasAny(['nropedido', 'cliente', 'doc', 'estado']))
                                    No hay cotizaciones que coincidan con los criterios de búsqueda y filtros aplicados.
                                @else
                                    Aún no hay cotizaciones registradas para este vendedor.
                                @endif
                            </p>
                            @if(request()->hasAny(['nropedido', 'cliente', 'doc', 'estado']))
                                <a href="{{ route('vendedor.app.cotizaciones.index') }}"
                                   class="inline-flex items-center px-4 py-2 bg-linear-to-r from-amber-500 to-orange-600 text-white font-semibold rounded-lg hover:from-amber-600 hover:to-orange-700 transition-all">
                                    Ver todas las cotizaciones
                                </a>
                            @endif
                        </div>
                    @endif
                </section>


            </div>
        </main>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
// Mostrar SweetAlert si la cotización fue guardada
@if(session('cotizacion_guardada'))
    Swal.fire({
        icon: 'success',
        title: '{{ session('cotizacion_guardada')['modificada'] ?? false ? '¡Cotización actualizada!' : '¡Cotización guardada!' }}',
        html: `La cotización N° <strong>{{ session('cotizacion_guardada')['numero'] }}</strong> ha sido {{ session('cotizacion_guardada')['modificada'] ?? false ? 'actualizada' : 'cotizada' }} con éxito.{{ session('cotizacion_guardada')['modificada'] ?? false ? '' : " Se le notificará al cliente." }}`,
        confirmButtonText: 'Entendido',
        confirmButtonColor: '#D88429',
        timer: 5000,
        timerProgressBar: true
    });
@endif

</script>

@endsection
