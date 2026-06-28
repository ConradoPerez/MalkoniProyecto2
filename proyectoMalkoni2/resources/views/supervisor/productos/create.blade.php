@extends('layouts.app')

@section('title', 'Crear Producto - Malkoni Hnos')
@section('page-title', 'CREAR PRODUCTO')

@section('content')
<div class="min-h-screen text-gray-900" style="background-color: #F3F4F6;">
    <!-- Sidebar -->
    @include('supervisor.components.sidebar')

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
                <div class="w-8"></div>
            </div>
        </div>

        <div class="p-4 lg:p-8 max-w-4xl">
            <!-- Header -->
            <div class="mb-6 flex items-center justify-between">
                <div>
                    <a href="{{ route('productos.index') }}" class="text-sm font-semibold text-gray-500 hover:text-gray-800 transition-colors flex items-center gap-1">
                        ← Volver a Productos
                    </a>
                    <h1 class="text-2xl font-syncopate font-bold text-gray-900 mt-2">
                        NUEVO PRODUCTO
                    </h1>
                </div>
            </div>

            <!-- Form Card -->
            <div class="bg-white rounded-xl border border-gray-200 shadow-sm overflow-hidden">
                <form action="{{ route('productos.store') }}" method="POST" enctype="multipart/form-data" class="p-6 sm:p-8 space-y-6">
                    @csrf

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <!-- Nombre -->
                        <div class="md:col-span-2">
                            <label class="block text-sm font-bold text-gray-700 mb-2 uppercase tracking-wide">Nombre del Producto *</label>
                            <input type="text" name="nombre" value="{{ old('nombre') }}" required
                                   class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-[#D88429]/20 focus:border-[#D88429] transition-all"
                                   placeholder="Ej: Tabla de Pino Premium">
                        </div>

                        <!-- Precio Base -->
                        <div>
                            <label class="block text-sm font-bold text-gray-700 mb-2 uppercase tracking-wide">Precio Base ($) *</label>
                            <input type="number" name="precio_base" value="{{ old('precio_base', 0) }}" min="0" required
                                   class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-[#D88429]/20 focus:border-[#D88429] transition-all"
                                   placeholder="Ej: 5000">
                        </div>

                        <!-- Descuento -->
                        <div>
                            <label class="block text-sm font-bold text-gray-700 mb-2 uppercase tracking-wide">Descuento (%) *</label>
                            <input type="number" name="descuento" value="{{ old('descuento', 0) }}" min="0" max="100" required
                                   class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-[#D88429]/20 focus:border-[#D88429] transition-all"
                                   placeholder="Ej: 10">
                        </div>

                        <!-- Subtipo -->
                        <div>
                            <label class="block text-sm font-bold text-gray-700 mb-2 uppercase tracking-wide">Subtipo</label>
                            <select name="id_subtipo" class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-[#D88429]/20 focus:border-[#D88429] transition-all bg-white">
                                <option value="">-- Seleccionar Subtipo --</option>
                                @foreach($subtipos as $subtipo)
                                    <option value="{{ $subtipo->id_subtipo }}" {{ old('id_subtipo') == $subtipo->id_subtipo ? 'selected' : '' }}>
                                        {{ $subtipo->nombre }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <!-- Subcategoría -->
                        <div>
                            <label class="block text-sm font-bold text-gray-700 mb-2 uppercase tracking-wide">Subcategoría</label>
                            <select name="id_subcategoria" class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-[#D88429]/20 focus:border-[#D88429] transition-all bg-white">
                                <option value="">-- Seleccionar Subcategoría --</option>
                                @foreach($subcategorias as $subcat)
                                    <option value="{{ $subcat->id_subcategoria }}" {{ old('id_subcategoria') == $subcat->id_subcategoria ? 'selected' : '' }}>
                                        {{ $subcat->nombre }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <!-- Foto del Producto -->
                        <div class="md:col-span-2">
                            <label class="block text-sm font-bold text-gray-700 mb-2 uppercase tracking-wide">Foto del Producto</label>
                            <input type="file" name="foto" accept="image/*"
                                   class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-[#D88429]/20 focus:border-[#D88429] transition-all bg-gray-50 file:mr-4 file:py-2 file:px-4 file:rounded-md file:border-0 file:text-xs file:font-semibold file:bg-[#166379] file:text-white hover:file:opacity-90">
                            <p class="text-xs text-gray-500 mt-1">Formatos permitidos: JPG, PNG, WEBP. Máximo 2MB.</p>
                        </div>

                        <!-- Descripción -->
                        <div class="md:col-span-2">
                            <label class="block text-sm font-bold text-gray-700 mb-2 uppercase tracking-wide">Descripción</label>
                            <textarea name="descripcion" rows="4"
                                      class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-[#D88429]/20 focus:border-[#D88429] transition-all"
                                      placeholder="Detalles adicionales del producto...">{{ old('descripcion') }}</textarea>
                        </div>

                        <!-- Promoción -->
                        <div class="md:col-span-2 flex items-center gap-3">
                            <input type="checkbox" name="promocion" id="promocion" value="1" {{ old('promocion') ? 'checked' : '' }}
                                   class="w-5 h-5 rounded text-[#D88429] focus:ring-[#D88429] border-gray-300">
                            <label for="promocion" class="text-sm font-bold text-gray-700 uppercase tracking-wide select-none">Marcar como producto en promoción</label>
                        </div>
                    </div>

                    <!-- Botones de Acción -->
                    <div class="pt-4 border-t border-gray-200 flex justify-end gap-3">
                        <a href="{{ route('productos.index') }}" class="px-6 py-2.5 border border-gray-300 rounded-lg text-sm font-semibold hover:bg-gray-50 transition-colors">
                            Cancelar
                        </a>
                        <button type="submit" class="px-6 py-2.5 text-white rounded-lg text-sm font-semibold hover:opacity-90 transition-opacity shadow-md" style="background-color: #D88429;">
                            Crear Producto
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </main>
</div>
@endsection
