@extends('layouts.app')

@section('title', 'Editar Cotización #' . $cotizacion->numero_formateado)

@section('content')
<div class="min-h-screen text-gray-900">
    <div class="flex">
        <!-- Sidebar del Cliente -->
        <aside class="w-48 bg-gray-100 border-r border-gray-300">
            @include('cliente.components.sidebar') 
        </aside>

        <!-- Main content -->
        <main class="flex-1 overflow-y-auto">
            <div class="p-4 lg:p-8">
                
                <!-- Header -->
                <div class="flex justify-between items-start border-b pb-4 mb-6">
                    <div>
                        <h1 class="text-3xl font-bold text-gray-900 mb-2">Editar Cotización</h1>
                        <p class="text-sm text-gray-600">Cotización: {{ $cotizacion->numero_formateado }}</p>
                    </div>
                </div>

                <!-- Mensajes de Éxito/Error -->
                @if ($errors->any())
                    <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
                        <ul class="list-disc list-inside">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <div class="max-w-2xl mx-auto bg-white border border-gray-300 rounded-lg p-6">
                    <form action="" method="POST">
                        @csrf
                        @method('PUT')

                        <!-- Título -->
                        <div class="mb-6">
                            <label class="block text-sm font-medium text-gray-900 mb-2">Título de la Cotización</label>
                            <input type="text" 
                                name="titulo" 
                                value="{{ old('titulo', $cotizacion->titulo) }}"
                                class="w-full px-4 py-2 border border-gray-300 rounded focus:ring-2 focus:ring-[#D88429] focus:border-transparent"
                                required
                            >
                            @error('titulo')
                                <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Información Actual -->
                        <div class="mb-6">
                            <h3 class="text-lg font-semibold mb-4">Información Actual:</h3>
                            <div class="bg-gray-50 p-4 rounded border border-gray-200 space-y-3">
                                <div>
                                    <p class="text-sm text-gray-600">Número de Cotización:</p>
                                    <p class="font-medium">{{ $cotizacion->numero_formateado }}</p>
                                </div>
                                <div>
                                    <p class="text-sm text-gray-600">Vendedor:</p>
                                    <p class="font-medium">{{ $cotizacion->empleado->nombre }}</p>
                                </div>
                                <div>
                                    <p class="text-sm text-gray-600">Fecha de Creación:</p>
                                    <p class="font-medium">{{ $cotizacion->fyh->format('d/m/Y H:i') }}</p>
                                </div>
                                <div>
                                    <p class="text-sm text-gray-600">Precio Total Actual:</p>
                                    <p class="font-medium text-[#D88429]">${{ number_format($cotizacion->precio_total / 100, 2, ',', '.') }}</p>
                                </div>
                                <div>
                                    <p class="text-sm text-gray-600">Estado:</p>
                                    <span class="inline-block px-3 py-1 rounded text-white text-sm font-semibold" style="{{ $cotizacion->estado_estilo }}">
                                        {{ $cotizacion->estado }}
                                    </span>
                                </div>
                            </div>
                        </div>

                        <!-- Productos Agregados -->
                        <div class="mb-6">
                            <h3 class="text-lg font-semibold mb-4">Productos Agregados:</h3>
                            @if($cotizacion->items->count() > 0)
                                <div class="bg-gray-50 border border-gray-200 rounded overflow-hidden">
                                    @foreach($cotizacion->items as $item)
                                        <div class="flex justify-between items-center p-4 border-b border-gray-200 last:border-0">
                                            <div>
                                                <p class="font-medium">{{ $item->producto->nombre ?? 'Producto eliminado' }}</p>
                                                <p class="text-sm text-gray-600">Cantidad: x{{ $item->cantidad }}</p>
                                            </div>
                                            <div class="text-right">
                                                <p class="font-medium">${{ number_format(($item->producto->precio_final ?? 0) * $item->cantidad / 100, 2, ',', '.') }}</p>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            @else
                                <p class="text-gray-600">No hay productos en esta cotización.</p>
                            @endif
                        </div>

                        <!-- Nota -->
                        <div class="mb-6">
                            <label class="block text-sm font-medium text-gray-900 mb-2">Notas Adicionales</label>
                            <textarea name="notas" 
                                rows="4"
                                class="w-full px-4 py-2 border border-gray-300 rounded focus:ring-2 focus:ring-[#D88429] focus:border-transparent"
                                placeholder="Añade notas o comentarios sobre esta cotización"
                            ></textarea>
                        </div>

                        <!-- Botones -->
                        <div class="flex justify-between gap-4">
                            <a href="{{ route('cliente.cotizacion.ver', ['id' => $cotizacion->id]) }}" class="px-6 py-2 bg-gray-400 text-white font-semibold rounded hover:bg-gray-500 transition-colors">
                                Cancelar
                            </a>
                            <button type="submit" class="px-6 py-2 bg-[#D88429] text-white font-semibold rounded hover:bg-[#c7731f] transition-colors" disabled>
                                Actualizar
                            </button>
                        </div>

                        <!-- Nota informativa -->
                        <p class="text-sm text-gray-600 mt-4 text-center">
                            ℹ️ Para modificar productos, ve a la sección "Agregar Productos"
                        </p>
                    </form>
                </div>
                
            </div>
        </main>
    </div>
</div>

@endsection
