@extends('layouts.app')

@section('title', 'Mis Cotizaciones')

@section('content')
<div class="min-h-screen text-gray-900">
    <!-- Sidebar -->
    @include('cliente.components.sidebar')

    <!-- Main content -->
    <main class="lg:ml-48">
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
                    <div class="w-8 h-8 rounded-full bg-gray-300"></div>
                </div>
            </div>
        </div>

        <div class="p-4 lg:p-8">
                
                <!-- Header -->
                <div class="flex justify-between items-start border-b pb-4 mb-6">
                    <div>
                        <h1 class="text-3xl font-bold text-gray-900 mb-2">Mis Cotizaciones</h1>
                        <p class="text-sm text-gray-600">Gestiona tus solicitudes de presupuesto</p>
                    </div>
                    
                    <a href="{{ route('cliente.nueva_cotizacion') }}" class="px-4 py-2 bg-[#D88429] text-white font-semibold rounded shadow hover:bg-[#c7731f] transition-colors">
                        + Nueva Cotización
                    </a>
                </div>

                <!-- Mensajes de Éxito/Error -->
                @if (session('success'))
                    <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4">
                        {{ session('success') }}
                    </div>
                @endif

                @if ($errors->any())
                    <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
                        <ul class="list-disc list-inside">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <!-- Tabla de Cotizaciones -->
                <div class="bg-white border border-gray-300 rounded-lg overflow-hidden">
                    @if($cotizaciones->count() > 0)
                        <div class="overflow-x-auto">
                            <table class="w-full">
                                <thead>
                                    <tr class="bg-gray-100 border-b border-gray-300">
                                        <th class="px-6 py-3 text-left text-sm font-semibold text-gray-900">Número</th>
                                        <th class="px-6 py-3 text-left text-sm font-semibold text-gray-900">Título</th>
                                        <th class="px-6 py-3 text-left text-sm font-semibold text-gray-900">Vendedor</th>
                                        <th class="px-6 py-3 text-center text-sm font-semibold text-gray-900">Fecha</th>
                                        <th class="px-6 py-3 text-right text-sm font-semibold text-gray-900">Total</th>
                                        <th class="px-6 py-3 text-center text-sm font-semibold text-gray-900">Estado</th>
                                        <th class="px-6 py-3 text-center text-sm font-semibold text-gray-900">Acciones</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($cotizaciones as $cotizacion)
                                        <tr class="border-b border-gray-200 hover:bg-gray-50">
                                            <td class="px-6 py-3">
                                                <span class="font-medium text-[#D88429]">{{ $cotizacion->numero_formateado }}</span>
                                            </td>
                                            <td class="px-6 py-3">
                                                <p class="font-medium text-gray-900">{{ $cotizacion->titulo }}</p>
                                            </td>
                                            <td class="px-6 py-3">
                                                <p class="font-medium">{{ $cotizacion->empleado->nombre ?? 'N/A' }}</p>
                                            </td>
                                            <td class="px-6 py-3 text-center text-sm">
                                                {{ $cotizacion->fyh->format('d/m/Y') }}
                                            </td>
                                            <td class="px-6 py-3 text-right font-medium">
                                                ${{ number_format($cotizacion->precio_total / 100, 2, ',', '.') }}
                                            </td>
                                            <td class="px-6 py-3 text-center">
                                                <span class="inline-block px-3 py-1 rounded text-white text-sm font-semibold" style="{{ $cotizacion->estado_estilo }}">
                                                    {{ $cotizacion->estado }}
                                                </span>
                                            </td>
                                            <td class="px-6 py-3 text-center">
                                                <div class="flex justify-center gap-2">
                                                    <a href="{{ route('cliente.cotizacion.ver', ['id' => $cotizacion->id]) }}" class="px-3 py-1 bg-blue-500 text-white rounded text-sm hover:bg-blue-600">
                                                        Ver
                                                    </a>
                                                    <a href="{{ route('cliente.cotizacion.productos', ['id' => $cotizacion->id]) }}" class="px-3 py-1 bg-green-500 text-white rounded text-sm hover:bg-green-600">
                                                        Productos
                                                    </a>
                                                </div>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="7" class="px-6 py-8 text-center text-gray-600">
                                                <p class="mb-4">No hay cotizaciones aún.</p>
                                                <a href="{{ route('cliente.nueva_cotizacion') }}" class="text-[#D88429] font-semibold hover:underline">
                                                    Crear nueva cotización
                                                </a>
                                            </td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>

                        <!-- Paginación -->
                        @if($cotizaciones->hasPages())
                            <div class="px-6 py-4 border-t border-gray-300">
                                {{ $cotizaciones->links() }}
                            </div>
                        @endif
                    @else
                        <div class="px-6 py-16 text-center">
                            <svg class="mx-auto h-12 w-12 text-gray-400 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                            </svg>
                            <h3 class="text-lg font-medium text-gray-900 mb-2">No hay cotizaciones</h3>
                            <p class="text-gray-600 mb-4">Comienza creando tu primera cotización</p>
                            <a href="{{ route('cliente.nueva_cotizacion') }}" class="inline-block px-6 py-2 bg-[#D88429] text-white font-semibold rounded shadow hover:bg-[#c7731f] transition-colors">
                                Crear Nueva Cotización
                            </a>
                        </div>
                    @endif
                </div>
                
            </div>
        </main>
    </div>
</div>

@endsection
