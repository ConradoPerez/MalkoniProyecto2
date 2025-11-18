@extends('layouts.app')

@section('title', isset($esNuevaCotizacion) && $esNuevaCotizacion ? 'Nueva Cotización - Seleccionar Productos' : 'Agregar Productos - Cotización #' . $cotizacion->numero_cotizacion)

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
                            @if(isset($esNuevaCotizacion) && $esNuevaCotizacion)
                                <a href="{{ route('cliente.nueva_cotizacion', ['persona_id' => $personaId]) }}" class="inline-flex items-center text-sm font-medium text-gray-500 hover:text-[#D88429]">
                                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path></svg>
                                    Volver al formulario
                                </a>
                            @else
                                <a href="{{ route('cliente.cotizacion.ver', ['id' => $cotizacion->id, 'persona_id' => $personaId]) }}" class="inline-flex items-center text-sm font-medium text-gray-500 hover:text-[#D88429]">
                                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path></svg>
                                    Volver al Detalle
                                </a>
                            @endif
                        </li>
                        <li>
                            <div class="flex items-center">
                                <svg class="w-6 h-6 text-gray-300" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd"></path></svg>
                                <span class="ml-1 text-sm font-medium text-gray-400">{{ isset($esNuevaCotizacion) && $esNuevaCotizacion ? 'Seleccionar Productos' : 'Catálogo de Productos' }}</span>
                            </div>
                        </li>
                    </ol>
                </nav>

                <div class="flex flex-col md:flex-row justify-between items-end mb-8 gap-4">
                    <div>
                        <h2 class="text-2xl font-syncopate font-bold text-gray-800">{{ isset($esNuevaCotizacion) && $esNuevaCotizacion ? 'SELECCIONAR PRODUCTOS' : 'AGREGAR PRODUCTOS' }}</h2>
                        <p class="text-sm text-gray-500 mt-1">{{ isset($esNuevaCotizacion) && $esNuevaCotizacion ? 'Elige los productos para tu nueva cotización' : 'Agrega items a la cotización' }} @if(!isset($esNuevaCotizacion) || !$esNuevaCotizacion)<span class="font-semibold text-[#D88429]">#{{ $cotizacion->numero_cotizacion }}</span>@endif</p>
                    </div>
                    
                    <div class="w-full md:w-1/3 relative">
                        <input type="text" placeholder="Buscar productos..." class="w-full pl-10 pr-4 py-2.5 rounded-lg border border-gray-200 focus:border-[#D88429] focus:ring-2 focus:ring-[#D88429]/20 outline-none transition-all shadow-sm text-sm" id="buscar-productos">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <svg class="h-5 w-5 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                            </svg>
                        </div>
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

                <form action="{{ isset($esNuevaCotizacion) && $esNuevaCotizacion ? route('cliente.cotizacion.crear_con_productos', ['persona_id' => $personaId]) : route('cliente.cotizacion.guardar_productos', ['id' => $cotizacion->id ?? 0, 'persona_id' => $personaId]) }}" method="POST" id="formProductos">
                    @csrf
                    
                    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8 items-start">
                        
                        <div class="lg:col-span-2">
                            
                            <!-- Filtros por categoría -->
                            <div class="flex gap-2 overflow-x-auto pb-4 mb-4 no-scrollbar">
                                <button type="button" class="filtro-categoria px-4 py-2 bg-gray-800 text-white rounded-full text-sm font-medium whitespace-nowrap shadow-md" data-categoria="todos">Todos</button>
                                @foreach($categorias as $cat)
                                    @if($cat->subcategorias->sum(function($sub) { return $sub->productos->count(); }) > 0)
                                        <button type="button" class="filtro-categoria px-4 py-2 bg-white text-gray-600 border border-gray-200 rounded-full text-sm font-medium whitespace-nowrap hover:bg-gray-50 hover:border-gray-300 transition-colors" data-categoria="{{ $cat->id_categoria }}">{{ $cat->nombre }}</button>
                                    @endif
                                @endforeach
                            </div>

                            <!-- Grid de productos -->
                            <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-3 gap-6" id="productos-grid">
                                @php $productoIndex = 0; @endphp
                                @foreach($categorias as $categoria)
                                    @foreach($categoria->subcategorias as $subcategoria)
                                        @foreach($subcategoria->productos as $producto)
                                            @php 
                                                $tipoNombre = '';
                                                $subtipoNombre = '';
                                                
                                                // Verificación más robusta para evitar errores
                                                if (isset($producto->subtipo) && $producto->subtipo !== null) {
                                                    $subtipoNombre = $producto->subtipo->nombre ?? '';
                                                    if (isset($producto->subtipo->tipo) && $producto->subtipo->tipo !== null) {
                                                        $tipoNombre = $producto->subtipo->tipo->nombre ?? '';
                                                    }
                                                }
                                            @endphp
                                            <div class="producto-card bg-white rounded-xl border border-gray-100 shadow-sm hover:shadow-md transition-all duration-300 overflow-hidden group flex flex-col h-full" 
                                                 data-categoria="{{ $categoria->id_categoria }}"
                                                 data-nombre="{{ strtolower($producto->nombre) }}"
                                                 data-descripcion="{{ strtolower($producto->descripcion ?? '') }}"
                                                 data-tipo="{{ strtolower($tipoNombre . ' ' . $subtipoNombre) }}">
                                                
                                                <div class="relative h-48 bg-gray-100 overflow-hidden">
                                                    @if($producto->foto)
                                                        <img src="{{ asset($producto->foto) }}" class="w-full h-full object-cover transform group-hover:scale-105 transition-transform duration-500" alt="{{ $producto->nombre }}">
                                                    @else
                                                        <div class="flex items-center justify-center h-full text-gray-300">
                                                            <svg class="w-12 h-12" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                                                        </div>
                                                    @endif
                                                </div>

                                                <div class="p-5 flex-1 flex flex-col">
                                                    <div class="mb-auto">
                                                        <h3 class="font-bold text-gray-900 text-base mb-1 leading-tight">{{ $producto->nombre }}</h3>
                                                        
                                                        @if($tipoNombre || $subtipoNombre)
                                                            <p class="text-xs text-[#D88429] mb-2 font-medium">
                                                                @if($tipoNombre)
                                                                    {{ $tipoNombre }}
                                                                    @if($subtipoNombre) - @endif
                                                                @endif
                                                                {{ $subtipoNombre }}
                                                            </p>
                                                        @endif
                                                        
                                                        @if($producto->descripcion)
                                                            <p class="text-xs text-gray-500 line-clamp-2 mb-3">{{ $producto->descripcion }}</p>
                                                        @endif
                                                    </div>

                                                    <div class="mt-4 pt-4 border-t border-gray-50">
                                                        <div class="flex items-center bg-gray-50 rounded-lg border border-gray-200 p-1">
                                                            <button type="button" onclick="ajustarCantidad('{{ $producto->id_producto }}', -1)" class="w-8 h-8 flex items-center justify-center text-gray-500 hover:text-red-500 hover:bg-white rounded-md transition-colors">
                                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 12H4"></path></svg>
                                                            </button>
                                                            
                                                            <input type="hidden" name="productos[{{ $productoIndex }}][id_producto]" value="{{ $producto->id_producto }}">
                                                            <input type="number" 
                                                                   id="input-{{ $producto->id_producto }}"
                                                                   name="productos[{{ $productoIndex }}][cantidad]" 
                                                                   value="0" 
                                                                   min="0"
                                                                   class="flex-1 w-full text-center bg-transparent border-none focus:ring-0 text-gray-900 font-semibold p-0 cantidad-input"
                                                                   data-producto="{{ $producto->id_producto }}"
                                                                   data-nombre="{{ $producto->nombre }}"
                                                                   readonly>
                                                                   
                                                            <button type="button" onclick="ajustarCantidad('{{ $producto->id_producto }}', 1)" class="w-8 h-8 flex items-center justify-center text-gray-500 hover:text-green-600 hover:bg-white rounded-md transition-colors">
                                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                                                            </button>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                            @php $productoIndex++; @endphp
                                        @endforeach
                                    @endforeach
                                @endforeach
                            </div>
                            
                            <div id="no-productos" class="hidden text-center py-12">
                                <svg class="w-16 h-16 text-gray-300 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.172 16.172a4 4 0 015.656 0M9 12h6m-6-4h6m2 5.291A7.962 7.962 0 0112 15c-2.34 0-4.5-1.006-6-2.709V19a1 1 0 001 1h10a1 1 0 001-1v-6.709z"/>
                                </svg>
                                <p class="text-gray-500">No se encontraron productos que coincidan con tu búsqueda.</p>
                            </div>
                        </div>

                        <!-- Sidebar: Resumen de Cotización -->
                        <div class="lg:col-span-1">
                            <div class="bg-white rounded-xl shadow-sm border border-gray-100 sticky top-4">
                                <div class="p-6">
                                    <h3 class="text-lg font-bold text-gray-900 mb-4 flex items-center">
                                        <svg class="w-5 h-5 mr-2 text-[#D88429]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v10a2 2 0 002 2h8a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path>
                                        </svg>
                                        Resumen de Cotización
                                    </h3>

                                    <div class="space-y-4 mb-6">
                                        <div class="flex items-start justify-between text-sm">
                                            <span class="text-gray-600">Cliente:</span>
                                            <span class="font-medium text-gray-900 text-right max-w-[140px]">{{ $cotizacion->cliente_nombre }}</span>
                                        </div>
                                        <div class="flex items-center justify-between text-sm">
                                            <span class="text-gray-600">Código:</span>
                                            <span class="font-mono text-gray-900">{{ $cotizacion->numero_cotizacion }}</span>
                                        </div>
                                        <div class="flex items-center justify-between text-sm">
                                            <span class="text-gray-600">Estado:</span>
                                            <span class="px-2 py-1 rounded-full text-xs font-medium
                                                {{ $cotizacion->estado_actual === 'Nuevo' ? 'bg-blue-100 text-blue-800' : 
                                                   ($cotizacion->estado_actual === 'Abierto' ? 'bg-yellow-100 text-yellow-800' : 
                                                   ($cotizacion->estado_actual === 'Cerrado' ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-800')) }}">
                                                {{ $cotizacion->estado_actual }}
                                            </span>
                                        </div>
                                    </div>

                                    <div class="border-t border-gray-100 pt-4 mb-4">
                                        <div class="flex items-center justify-between text-sm mb-3">
                                            <span class="font-medium text-gray-900">Productos Seleccionados</span>
                                            <span id="total-productos" class="px-2 py-1 bg-[#D88429] text-white text-xs font-bold rounded-full min-w-[20px] text-center">0</span>
                                        </div>
                                        
                                        <div id="productos-seleccionados" class="space-y-2 max-h-64 overflow-y-auto">
                                            <div id="mensaje-vacio" class="text-sm text-gray-500 text-center py-4 border border-dashed border-gray-200 rounded-lg">
                                                <svg class="w-8 h-8 text-gray-300 mx-auto mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"></path>
                                                </svg>
                                                Aún no has seleccionado productos
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="px-6 py-4 bg-gray-50 border-t border-gray-100 rounded-b-xl">
                                    <button type="submit" id="btnGuardar" class="w-full bg-[#D88429] hover:bg-[#c17623] text-white py-3 px-4 rounded-lg font-semibold transition-colors disabled:opacity-50 disabled:cursor-not-allowed flex items-center justify-center">
                                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                        </svg>
                                        {{ isset($esNuevaCotizacion) && $esNuevaCotizacion ? 'Crear Cotización' : 'Agregar Productos' }}
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </form>

                <!-- SECCIÓN: PRODUCTOS YA AGREGADOS -->
                @if(isset($itemsAgregados) && $itemsAgregados->count() > 0)
                    <div class="mt-8">
                        <div class="bg-blue-50 border border-blue-200 rounded-xl p-6">
                            <h3 class="text-lg font-semibold mb-4 text-blue-900 flex items-center">
                                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                </svg>
                                Productos Ya Agregados
                            </h3>
                            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                                @foreach($itemsAgregados as $item)
                                    <div class="flex justify-between items-center bg-white p-4 rounded-lg border border-blue-100 shadow-sm">
                                        <div class="flex-1">
                                            <span class="font-medium text-gray-900 block">{{ $item->producto->nombre ?? 'Producto eliminado' }}</span>
                                            <span class="text-sm text-gray-600">Cantidad: <span class="font-semibold">{{ $item->cantidad }}</span></span>
                                        </div>
                                        @if(!isset($esNuevaCotizacion) || !$esNuevaCotizacion)
                                        <form action="{{ route('cliente.cotizacion.eliminar_item', ['cotizacionId' => $cotizacion->id, 'itemId' => $item->id_item, 'persona_id' => $personaId]) }}" method="POST" class="inline ml-3" onsubmit="return confirm('¿Desea eliminar este producto?');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="p-2 bg-red-500 text-white rounded-lg text-sm hover:bg-red-600 transition-colors">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                                </svg>
                                            </button>
                                        </form>
                                        @endif
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    </div>
                @endif

            </div>
        </main>
    </div>
</div>

<!-- Script para validación y funcionalidades -->
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const cantidadInputs = document.querySelectorAll('.cantidad-input');
        const searchInput = document.getElementById('search-input');
        const filtrosCategorias = document.querySelectorAll('.filtro-categoria');
        const productosGrid = document.getElementById('productos-grid');
        const noProductos = document.getElementById('no-productos');
        const totalProductos = document.getElementById('total-productos');
        const productosSeleccionados = document.getElementById('productos-seleccionados');
        const mensajeVacio = document.getElementById('mensaje-vacio');
        const btnGuardar = document.getElementById('btnGuardar');

        // Variables para el estado
        let categoriaActiva = 'todos';
        let terminoBusqueda = '';
        let productosEnResumen = {};

        // Función para filtrar productos
        function filtrarProductos() {
            const productos = document.querySelectorAll('.producto-card');
            let productosVisibles = 0;

            productos.forEach(producto => {
                const categoriaProducto = producto.getAttribute('data-categoria');
                const nombreProducto = producto.getAttribute('data-nombre') || '';
                const descripcionProducto = producto.getAttribute('data-descripcion') || '';
                const tipoProducto = producto.getAttribute('data-tipo') || '';
                
                const textoCompleto = nombreProducto + ' ' + descripcionProducto + ' ' + tipoProducto;
                
                // Verificar categoría
                const coincideCategoria = categoriaActiva === 'todos' || categoriaProducto === categoriaActiva;
                
                // Verificar búsqueda
                const coincideBusqueda = terminoBusqueda === '' || textoCompleto.includes(terminoBusqueda);
                
                if (coincideCategoria && coincideBusqueda) {
                    producto.style.display = 'flex';
                    productosVisibles++;
                } else {
                    producto.style.display = 'none';
                }
            });

            // Mostrar/ocultar mensaje de no productos
            if (productosVisibles === 0) {
                noProductos.classList.remove('hidden');
                productosGrid.style.display = 'none';
            } else {
                noProductos.classList.add('hidden');
                productosGrid.style.display = 'grid';
            }
        }

        // Función para ajustar cantidad
        function ajustarCantidad(productoId, cambio) {
            const input = document.getElementById(`input-${productoId}`);
            const nuevaCantidad = Math.max(0, parseInt(input.value) + cambio);
            input.value = nuevaCantidad;
            
            // Actualizar resumen
            actualizarResumen();
        }

        // Función para actualizar el resumen
        function actualizarResumen() {
            productosEnResumen = {};
            let totalCount = 0;

            // Recorrer todos los inputs de cantidad
            cantidadInputs.forEach(input => {
                const cantidad = parseInt(input.value) || 0;
                if (cantidad > 0) {
                    const productoId = input.getAttribute('data-producto');
                    const productoNombre = input.getAttribute('data-nombre');
                    productosEnResumen[productoId] = {
                        nombre: productoNombre,
                        cantidad: cantidad
                    };
                    totalCount += cantidad;
                }
            });

            // Actualizar contador
            totalProductos.textContent = totalCount;

            // Actualizar lista de productos seleccionados
            actualizarListaResumen();

            // Habilitar/deshabilitar botón
            btnGuardar.disabled = totalCount === 0;
        }

        // Función para actualizar la lista del resumen
        function actualizarListaResumen() {
            productosSeleccionados.innerHTML = '';

            if (Object.keys(productosEnResumen).length === 0) {
                productosSeleccionados.appendChild(mensajeVacio);
            } else {
                Object.entries(productosEnResumen).forEach(([id, producto]) => {
                    const item = document.createElement('div');
                    item.className = 'flex items-center justify-between p-3 bg-gray-50 rounded-lg border border-gray-200';
                    item.innerHTML = `
                        <div class="flex-1 min-w-0">
                            <p class="font-medium text-gray-900 text-sm truncate">${producto.nombre}</p>
                            <p class="text-xs text-gray-600">Cantidad: ${producto.cantidad}</p>
                        </div>
                        <button type="button" onclick="ajustarCantidad('${id}', -${producto.cantidad})" class="ml-2 p-1 text-red-500 hover:bg-red-50 rounded">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                            </svg>
                        </button>
                    `;
                    productosSeleccionados.appendChild(item);
                });
            }
        }

        // Event listeners para búsqueda
        if (searchInput) {
            searchInput.addEventListener('input', function() {
                terminoBusqueda = this.value.toLowerCase();
                filtrarProductos();
            });
        }

        // Event listeners para filtros de categoría
        filtrosCategorias.forEach(filtro => {
            filtro.addEventListener('click', function() {
                // Remover clase activa de todos los filtros
                filtrosCategorias.forEach(f => {
                    f.classList.remove('bg-gray-800', 'text-white');
                    f.classList.add('bg-white', 'text-gray-600', 'border-gray-200');
                });
                
                // Agregar clase activa al filtro clickeado
                this.classList.remove('bg-white', 'text-gray-600', 'border-gray-200');
                this.classList.add('bg-gray-800', 'text-white');
                
                categoriaActiva = this.getAttribute('data-categoria');
                filtrarProductos();
            });
        });

        // Event listeners para inputs de cantidad
        cantidadInputs.forEach(input => {
            input.addEventListener('change', actualizarResumen);
        });

        // Hacer función global para los botones
        window.ajustarCantidad = ajustarCantidad;

        // Validar envío del formulario
        document.getElementById('formProductos').addEventListener('submit', function(e) {
            let tieneProductos = false;
            
            // Primero, verificar si hay productos seleccionados
            cantidadInputs.forEach(input => {
                if (parseInt(input.value) > 0) {
                    tieneProductos = true;
                }
            });

            if (!tieneProductos) {
                e.preventDefault();
                alert('Por favor, seleccione al menos un producto antes de continuar.');
                return false;
            }
            
            // Eliminar inputs de productos con cantidad 0 para evitar envío innecesario
            cantidadInputs.forEach(input => {
                const cantidad = parseInt(input.value) || 0;
                if (cantidad === 0) {
                    // Encontrar el input hidden correspondiente y removerlo también
                    const productoId = input.getAttribute('data-producto');
                    const hiddenInput = document.querySelector(`input[type="hidden"][value="${productoId}"]`);
                    if (hiddenInput) {
                        hiddenInput.remove();
                    }
                    input.remove();
                }
            });
        });

        // Inicializar
        actualizarResumen();
        filtrarProductos();
    });
</script>

@endsection

