@extends('layouts.app')

@section('title', 'Agregar Productos - Cotización #' . $cotizacion->numero_formateado)

@section('content')
<div class="min-h-screen text-gray-900">
    <!-- Sidebar -->
    @include('cliente.components.sidebar')

    <!-- Main content -->
    <main>
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

        <!-- Desktop Header with offset -->
        <div class="hidden lg:block sticky top-0 z-20 bg-white border-b border-gray-200 p-8">
            <h1 class="text-2xl font-bold text-gray-900">Agregar Productos a Cotización</h1>
        </div>

        <div class="p-4 lg:p-8">
                
                <!-- Mobile Header -->
                <div class="lg:hidden flex justify-between items-start border-b pb-4 mb-6">
                    <div>
                        <h1 class="text-3xl font-bold text-gray-900 mb-2">Agregar Productos a Cotización</h1>
                        <p class="text-sm text-gray-600">Cotización: {{ $cotizacion->numero_formateado }}</p>
                        <p class="text-sm text-gray-600">Vendedor: {{ $cotizacion->empleado->nombre ?? 'N/A' }}</p>
                    </div>
                    
                    <!-- Sección de Usuario -->
                    <div class="flex items-center space-x-2">
                        <span class="text-lg font-medium">{{ auth()->user()->name }}</span>
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5.121 17.804A13.937 13.937 0 0112 16c2.5 0 4.847.655 6.879 1.804M15 10a3 3 0 11-6 0 3 3 0 016 0zm6 0a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
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

                @if (session('success'))
                    <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4">
                        {{ session('success') }}
                    </div>
                @endif

                <form action="{{ route('cliente.cotizacion.guardar_productos', ['id' => $cotizacion->id]) }}" method="POST" id="formProductos">
                    @csrf

                    <!-- SECCIÓN: PRODUCTOS DISPONIBLES -->
                    <div class="mb-8">
                        <h2 class="text-xl font-semibold mb-6">Seleccione Productos:</h2>
                        
                        <div id="productosContainer" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                            @forelse($productos as $producto)
                                <div class="border-2 border-gray-300 rounded-lg p-4 hover:shadow-lg transition-shadow">
                                    <!-- Imagen del Producto -->
                                    <div class="w-full h-40 bg-gray-200 rounded mb-3 overflow-hidden">
                                        @if($producto->foto)
                                            <img src="{{ asset($producto->foto) }}" alt="{{ $producto->nombre }}" class="w-full h-full object-cover">
                                        @else
                                            <svg class="w-full h-full text-gray-400 p-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" /></svg>
                                        @endif
                                    </div>

                                    <!-- Información del Producto -->
                                    <h3 class="font-semibold text-gray-900 mb-2">{{ $producto->nombre }}</h3>
                                    <p class="text-sm text-gray-600 mb-2 line-clamp-2">{{ $producto->descripcion }}</p>

                                    <!-- Precios -->
                                    <div class="flex justify-between items-center mb-3">
                                        @if($producto->descuento > 0)
                                            <span class="text-sm text-red-500 line-through">${{ number_format($producto->precio_base / 100, 2, ',', '.') }}</span>
                                            <span class="text-lg font-bold text-green-600">${{ number_format($producto->precio_final / 100, 2, ',', '.') }}</span>
                                        @else
                                            <span class="text-lg font-bold text-blue-600">${{ number_format($producto->precio_final / 100, 2, ',', '.') }}</span>
                                        @endif
                                    </div>

                                    <!-- Cantidad -->
                                    <div class="flex items-center gap-2">
                                        <label class="text-sm font-medium text-gray-700">Cantidad:</label>
                                        <input type="number" 
                                            name="productos[{{ $loop->index }}][id_producto]" 
                                            value="{{ $producto->id_producto }}"
                                            class="hidden"
                                        >
                                        <input type="number" 
                                            name="productos[{{ $loop->index }}][cantidad]"
                                            min="0"
                                            value="0"
                                            class="w-20 px-2 py-1 border border-gray-300 rounded text-center cantidad-input"
                                            data-producto="{{ $producto->id_producto }}"
                                            data-precio="{{ $producto->precio_final }}"
                                        >
                                    </div>
                                </div>
                            @empty
                                <div class="col-span-3 text-center py-8">
                                    <p class="text-gray-600 text-lg">No hay productos disponibles en este momento.</p>
                                </div>
                            @endforelse
                        </div>
                    </div>

                    <!-- SECCIÓN: RESUMEN Y PRODUCTOS AGREGADOS -->
                    @if($itemsAgregados->count() > 0)
                        <div class="mb-8 bg-blue-50 border border-blue-200 rounded-lg p-6">
                            <h3 class="text-lg font-semibold mb-4 text-blue-900">Productos Ya Agregados:</h3>
                            <div class="space-y-3">
                                @foreach($itemsAgregados as $item)
                                    <div class="flex justify-between items-center bg-white p-3 rounded border border-blue-100">
                                        <div>
                                            <span class="font-medium">{{ $item->producto->nombre ?? 'Producto eliminado' }}</span>
                                            <span class="text-gray-600 ml-2">(x{{ $item->cantidad }})</span>
                                        </div>
                                        <div class="flex items-center gap-4">
                                            <span class="font-semibold">${{ number_format(($item->producto->precio_final * $item->cantidad) / 100, 2, ',', '.') }}</span>
                                            <form action="{{ route('cliente.cotizacion.eliminar_item', ['cotizacionId' => $cotizacion->id, 'itemId' => $item->id_item]) }}" method="POST" class="inline" onsubmit="return confirm('¿Desea eliminar este producto?');">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="px-3 py-1 bg-red-500 text-white rounded text-sm hover:bg-red-600">
                                                    Eliminar
                                                </button>
                                            </form>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @endif

                    <!-- SECCIÓN: RESUMEN TOTAL -->
                    <div class="max-w-md mx-auto mb-8 bg-gray-50 border-2 border-gray-300 rounded-lg p-6">
                        <h3 class="text-lg font-semibold mb-4">Resumen:</h3>
                        <div class="space-y-2 mb-4">
                            <div class="flex justify-between">
                                <span>Subtotal:</span>
                                <span id="subtotal" class="font-medium">$0,00</span>
                            </div>
                            <div class="flex justify-between">
                                <span>Productos Anteriores:</span>
                                <span class="font-medium">${{ number_format($cotizacion->precio_total / 100, 2, ',', '.') }}</span>
                            </div>
                            <div class="border-t pt-2 flex justify-between text-lg font-bold">
                                <span>Total:</span>
                                <span id="total">${{ number_format($cotizacion->precio_total / 100, 2, ',', '.') }}</span>
                            </div>
                        </div>
                    </div>

                    <!-- SECCIÓN: BOTONES DE ACCIÓN -->
                    <div class="flex justify-center gap-4">
                        <a href="{{ route('cliente.cotizacion.ver', ['id' => $cotizacion->id]) }}" class="px-6 py-3 bg-gray-400 text-white font-semibold rounded shadow hover:bg-gray-500 transition-colors">
                            Cancelar
                        </a>
                        <a href="{{ route('cliente.agregar_productos_catalogo', ['cotizacionId' => $cotizacion->id]) }}" class="px-6 py-3 bg-blue-500 text-white font-semibold rounded shadow hover:bg-blue-600 transition-colors">
                            Agregar desde Catálogo
                        </a>
                        <button type="submit" class="px-6 py-3 bg-[#D88429] text-white font-semibold rounded shadow hover:bg-[#c7731f] transition-colors" id="btnGuardar">
                            Guardar Productos
                        </button>
                    </div>
                </form>
                
            </div>
        </main>
    </div>
</div>

<!-- Script para calcular totales dinámicamente -->
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const cantidadInputs = document.querySelectorAll('.cantidad-input');
        const subtotalEl = document.getElementById('subtotal');
        const totalEl = document.getElementById('total');
        const precioAnterior = {{ $cotizacion->precio_total }};

        function actualizarTotal() {
            let nuevoSubtotal = 0;

            cantidadInputs.forEach(input => {
                const cantidad = parseInt(input.value) || 0;
                const precio = parseInt(input.dataset.precio) || 0;
                nuevoSubtotal += cantidad * precio;
            });

            const total = nuevoSubtotal + precioAnterior;

            subtotalEl.textContent = '$' + (nuevoSubtotal / 100).toLocaleString('es-AR', {minimumFractionDigits: 2, maximumFractionDigits: 2});
            totalEl.textContent = '$' + (total / 100).toLocaleString('es-AR', {minimumFractionDigits: 2, maximumFractionDigits: 2});
        }

        cantidadInputs.forEach(input => {
            input.addEventListener('change', actualizarTotal);
            input.addEventListener('input', actualizarTotal);
        });

        // Validar que al menos haya un producto con cantidad > 0
        document.getElementById('formProductos').addEventListener('submit', function(e) {
            let tieneProductos = false;

            cantidadInputs.forEach(input => {
                if (parseInt(input.value) > 0) {
                    tieneProductos = true;
                }
            });

            if (!tieneProductos) {
                e.preventDefault();
                alert('Por favor, seleccione al menos un producto con cantidad mayor a 0');
            }
        });
    });
</script>

@endsection
