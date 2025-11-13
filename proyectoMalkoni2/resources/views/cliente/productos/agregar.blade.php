@extends('layouts.app')

@section('title', 'Agregar Producto')

@section('content')
<div class="min-h-screen text-gray-900">
    <div class="flex">
        <!-- Sidebar -->
        <aside class="w-48 bg-gray-100 border-r border-gray-300">
            @include('cliente.components.sidebar') 
        </aside>

        <!-- Main content -->
        <main class="flex-1 overflow-y-auto">
            <div class="p-8">
                
                <!-- Header -->
                <div class="flex justify-between items-center mb-8 border-b pb-4">
                    <h1 class="text-3xl font-bold">Agregar Producto</h1>
                    <div class="flex items-center space-x-2">
                        <span class="text-lg font-medium">{{ auth()->user()->name ?? 'Usuario' }}</span>
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5.121 17.804A13.937 13.937 0 0112 16c2.5 0 4.847.655 6.879 1.804M15 10a3 3 0 11-6 0 3 3 0 016 0zm6 0a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </div>
                </div>

                <!-- Tabs de Categorías -->
                <div class="mb-8">
                    <div class="flex border-b border-gray-300 gap-0 overflow-x-auto">
                        @forelse($categorias as $categoria)
                            <button 
                                class="tab-btn px-6 py-3 border-b-2 font-medium transition-colors whitespace-nowrap"
                                data-categoria="{{ $categoria->id_categoria }}"
                                style="border-color: {{ $loop->first ? '#333' : '#ccc' }}; color: {{ $loop->first ? '#000' : '#666' }};"
                            >
                                {{ $categoria->nombre }}
                            </button>
                        @empty
                            <p class="text-gray-600">No hay categorías disponibles</p>
                        @endforelse
                    </div>
                </div>

                <!-- Barra de búsqueda -->
                <div class="mb-8 flex justify-center">
                    <div class="relative w-full max-w-xl">
                        <input 
                            type="text" 
                            id="searchInput"
                            placeholder="Buscar producto..." 
                            class="w-full px-4 py-2 border-2 border-gray-400 rounded focus:outline-none focus:border-[#D88429]"
                        >
                        <svg class="absolute right-3 top-3 w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                        </svg>
                    </div>
                </div>

                <!-- Debug Info -->
                <div class="bg-blue-50 border border-blue-200 rounded px-4 py-2 mb-4 text-sm text-blue-800">
                    📊 Categorías: {{ $categorias->count() }} | Productos: {{ $productos->count() }}
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

                <!-- Grid de Productos -->
                <form action="{{ route('cliente.cotizacion.guardar_productos', ['id' => $cotizacionId]) }}" method="POST" id="formProductos">
                    @csrf
                    
                    <div id="productosContainer" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 mb-8">
                        @forelse($productos as $producto)
                            <div class="producto-card border-2 border-gray-300 p-4 rounded hover:shadow-lg transition-shadow"
                                 data-categoria="{{ $producto->subcategoria->id_categoria ?? '' }}"
                                 data-nombre="{{ strtolower($producto->nombre) }}"
                                 data-descripcion="{{ strtolower($producto->descripcion ?? '') }}">
                                
                                <!-- Imagen del Producto -->
                                <div class="w-full h-40 bg-gray-200 rounded mb-3 border-2 border-gray-400 flex items-center justify-center overflow-hidden">
                                    @if($producto->foto)
                                        <img src="{{ asset($producto->foto) }}" alt="{{ $producto->nombre }}" class="w-full h-full object-cover">
                                    @else
                                        <svg class="w-16 h-16 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                        </svg>
                                    @endif
                                </div>

                                <!-- Información del Producto -->
                                <div class="mb-3">
                                    <h3 class="font-semibold text-gray-900 text-sm">{{ $producto->nombre }}</h3>
                                    <p class="text-xs text-gray-600 mt-1">{{ Str::limit($producto->descripcion, 60) }}</p>
                                </div>

                                <!-- Precio y Descuento -->
                                <div class="flex justify-between items-center mb-3">
                                    @if($producto->descuento > 0)
                                        <span class="text-xs text-red-500 line-through">${{ number_format($producto->precio_base / 100, 0) }}</span>
                                        <span class="text-sm font-bold text-green-600">${{ number_format($producto->precio_final / 100, 0) }}</span>
                                    @else
                                        <span class="text-sm font-bold text-blue-600">${{ number_format($producto->precio_final / 100, 0) }}</span>
                                    @endif
                                    @if($producto->descuento > 0)
                                        <span class="text-xs bg-red-100 text-red-700 px-2 py-1 rounded">-{{ $producto->descuento }}%</span>
                                    @endif
                                </div>

                                <!-- Cantidad e Input Oculto -->
                                <div class="flex items-center justify-between">
                                    <span class="text-xs text-gray-700">Cantidad:</span>
                                    <div class="flex items-center gap-2">
                                        <input type="hidden" 
                                            name="productos[{{ $loop->index }}][id_producto]" 
                                            value="{{ $producto->id_producto }}"
                                        >
                                        <input type="number" 
                                            name="productos[{{ $loop->index }}][cantidad]"
                                            min="0"
                                            max="999"
                                            value="0"
                                            class="cantidad-input w-20 px-2 py-1 border-2 border-gray-300 rounded text-center focus:border-[#D88429]"
                                            data-producto="{{ $producto->id_producto }}"
                                            data-precio="{{ $producto->precio_final }}"
                                        >
                                    </div>
                                </div>

                                <!-- Checkbox selección rápida -->
                                <div class="mt-3 flex items-center">
                                    <input type="checkbox" 
                                        class="producto-checkbox w-5 h-5 cursor-pointer"
                                        data-index="{{ $loop->index }}"
                                    >
                                    <span class="text-xs text-gray-600 ml-2">Seleccionar</span>
                                </div>
                            </div>
                        @empty
                            <div class="col-span-3 text-center py-12">
                                <p class="text-gray-600 text-lg">No hay productos disponibles</p>
                            </div>
                        @endforelse
                    </div>

                    <!-- Resumen -->
                    <div class="bg-gray-50 border-2 border-gray-300 rounded-lg p-6 mb-6 max-w-sm ml-auto">
                        <h3 class="font-semibold text-gray-900 mb-3">Resumen</h3>
                        <div class="space-y-2 mb-3">
                            <div class="flex justify-between text-sm">
                                <span>Productos seleccionados:</span>
                                <span id="contadorProductos" class="font-medium">0</span>
                            </div>
                            <div class="flex justify-between text-sm">
                                <span>Total items:</span>
                                <span id="totalItems" class="font-medium">0</span>
                            </div>
                        </div>
                        <div class="border-t pt-2 flex justify-between text-lg font-bold">
                            <span>Total:</span>
                            <span id="totalPrice" class="text-[#D88429]">$0</span>
                        </div>
                    </div>

                    <!-- Botones de Acción -->
                    <div class="flex justify-center gap-4">
                        <a href="{{ route('cliente.cotizacion.ver', ['id' => $cotizacionId]) }}" 
                           class="px-6 py-3 bg-gray-400 text-white font-semibold rounded shadow hover:bg-gray-500 transition-colors">
                            Cancelar
                        </a>
                        <button type="submit" 
                                class="px-6 py-3 bg-[#D88429] text-white font-semibold rounded shadow hover:bg-[#c7731f] transition-colors disabled:opacity-50"
                                id="btnAgregar"
                                disabled>
                            Agregar (0)
                        </button>
                    </div>
                </form>

            </div>
        </main>
    </div>
</div>

<!-- Scripts -->
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const tabBtns = document.querySelectorAll('.tab-btn');
        const productosCards = document.querySelectorAll('.producto-card');
        const cantidadInputs = document.querySelectorAll('.cantidad-input');
        const searchInput = document.getElementById('searchInput');
        const checkboxes = document.querySelectorAll('.producto-checkbox');
        const btnAgregar = document.getElementById('btnAgregar');
        const totalPriceEl = document.getElementById('totalPrice');
        const contadorProductosEl = document.getElementById('contadorProductos');
        const totalItemsEl = document.getElementById('totalItems');
        let categoriaActual = null;

        // Tabs - Filtrar por categoría
        tabBtns.forEach(btn => {
            btn.addEventListener('click', function() {
                categoriaActual = this.dataset.categoria;
                
                // Actualizar estilos del tab
                tabBtns.forEach(b => {
                    b.style.borderColor = '#ccc';
                    b.style.color = '#666';
                });
                this.style.borderColor = '#333';
                this.style.color = '#000';

                filtrarProductos();
            });
        });

        // Búsqueda
        searchInput.addEventListener('input', filtrarProductos);

        function filtrarProductos() {
            const searchTerm = searchInput.value.toLowerCase();
            let productosVisibles = 0;

            productosCards.forEach(card => {
                let mostrar = true;

                // Filtro por categoría
                if (categoriaActual && card.dataset.categoria !== categoriaActual) {
                    mostrar = false;
                }

                // Filtro por búsqueda
                if (mostrar && searchTerm) {
                    const nombre = card.dataset.nombre;
                    const descripcion = card.dataset.descripcion;
                    if (!nombre.includes(searchTerm) && !descripcion.includes(searchTerm)) {
                        mostrar = false;
                    }
                }

                card.style.display = mostrar ? 'block' : 'none';
                if (mostrar) productosVisibles++;
            });

            // Mensaje si no hay resultados
            if (productosVisibles === 0) {
                let container = document.getElementById('productosContainer');
                if (!container.querySelector('.no-results')) {
                    let msg = document.createElement('div');
                    msg.className = 'no-results col-span-3 text-center py-8 text-gray-600';
                    msg.textContent = 'No se encontraron productos';
                    container.appendChild(msg);
                }
            }
        }

        // Calcular totales
        function actualizarTotales() {
            let totalPrice = 0;
            let totalItems = 0;
            let productosSeleccionados = 0;

            cantidadInputs.forEach((input, index) => {
                const cantidad = parseInt(input.value) || 0;
                const precio = parseInt(input.dataset.precio) || 0;
                
                if (cantidad > 0) {
                    totalPrice += cantidad * precio;
                    totalItems += cantidad;
                    productosSeleccionados++;
                }
            });

            totalPriceEl.textContent = '$' + (totalPrice / 100).toLocaleString('es-AR', { minimumFractionDigits: 0 });
            contadorProductosEl.textContent = productosSeleccionados;
            totalItemsEl.textContent = totalItems;
            btnAgregar.textContent = `Agregar (${productosSeleccionados})`;
            btnAgregar.disabled = productosSeleccionados === 0;
        }

        cantidadInputs.forEach(input => {
            input.addEventListener('input', actualizarTotales);
            input.addEventListener('change', actualizarTotales);
        });

        // Checkboxes - Seleccionar cantidad predeterminada
        checkboxes.forEach(checkbox => {
            checkbox.addEventListener('change', function() {
                const index = this.dataset.index;
                const input = cantidadInputs[index];
                input.value = this.checked ? '1' : '0';
                actualizarTotales();
            });
        });

        // Validar antes de enviar
        document.getElementById('formProductos').addEventListener('submit', function(e) {
            let tieneProductos = false;
            cantidadInputs.forEach(input => {
                if (parseInt(input.value) > 0) {
                    tieneProductos = true;
                }
            });

            if (!tieneProductos) {
                e.preventDefault();
                alert('Por favor, selecciona al menos un producto con cantidad mayor a 0');
            }
        });

        // Inicializar - mostrar productos del primer tab
        if (tabBtns.length > 0) {
            const primerTab = tabBtns[0];
            categoriaActual = primerTab.dataset.categoria;
            primerTab.style.borderColor = '#333';
            primerTab.style.color = '#000';
            filtrarProductos();
        }
    });
</script>

<style>
    .tab-btn {
        cursor: pointer;
        transition: all 0.3s ease;
    }

    .tab-btn:hover {
        background-color: #f0f0f0;
    }

    input[type="number"]::-webkit-outer-spin-button,
    input[type="number"]::-webkit-inner-spin-button {
        -webkit-appearance: none;
        margin: 0;
    }

    input[type="number"] {
        -moz-appearance: textfield;
    }
</style>

@endsection
