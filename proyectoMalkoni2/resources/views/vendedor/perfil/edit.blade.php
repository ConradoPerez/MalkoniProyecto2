@extends('layouts.app')

@section('title', 'Editar Perfil - Malkoni Hnos')

@section('content')
<div class="min-h-screen text-gray-900" style="background-color: #F3F4F6;">
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
                <div class="w-8"></div>
            </div>
        </div>

        <div class="p-4 lg:p-8 max-w-3xl">
            <!-- Header -->
            <div class="mb-6">
                <a href="{{ route('vendedor.dashboard') }}" class="text-sm font-semibold text-gray-500 hover:text-gray-800 transition-colors flex items-center gap-1">
                    ← Volver al Dashboard
                </a>
                <h1 class="text-2xl font-syncopate font-bold text-gray-900 mt-2">
                    MI PERFIL
                </h1>
                <p class="text-gray-600 text-sm mt-1">Actualiza tu información personal, foto de perfil y contraseña corporativa.</p>
            </div>

            <!-- Alertas de Validación -->
            @if($errors->any())
                <div class="mb-6 rounded-xl border border-red-200 bg-red-50 p-4 flex items-start gap-3 text-sm text-red-700 shadow-sm">
                    <svg class="w-5 h-5 text-red-500 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                    </svg>
                    <div class="font-semibold">
                        <p class="mb-1">Por favor corrige los siguientes errores:</p>
                        <ul class="list-disc list-inside text-xs font-normal">
                            @foreach($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                </div>
            @endif

            <!-- Form Card -->
            <div class="bg-white rounded-xl border border-gray-200 shadow-sm overflow-hidden">
                <form action="{{ route('vendedor.perfil.update') }}" method="POST" enctype="multipart/form-data" class="p-6 sm:p-8 space-y-6">
                    @csrf
                    @method('PUT')

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
                        <!-- Nombre Completo -->
                        <div class="sm:col-span-2">
                            <label class="block text-sm font-bold text-gray-700 mb-2 uppercase tracking-wide">Nombre Completo *</label>
                            <input type="text" name="nombre" value="{{ old('nombre', $vendedor->nombre) }}" required
                                   class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-[#D88429]/20 focus:border-[#D88429] transition-all"
                                   placeholder="Ej: Juan Carlos Pérez">
                        </div>

                        <!-- Email (Informativo, Deshabilitado) -->
                        <div>
                            <label class="block text-sm font-bold text-gray-400 mb-2 uppercase tracking-wide">Correo Corporativo</label>
                            <input type="email" value="{{ $vendedor->email }}" disabled
                                   class="w-full px-4 py-2.5 border border-gray-200 bg-gray-50 text-gray-500 rounded-lg cursor-not-allowed">
                        </div>

                        <!-- DNI -->
                        <div>
                            <label class="block text-sm font-bold text-gray-700 mb-2 uppercase tracking-wide">DNI</label>
                            <input type="text" name="dni" value="{{ old('dni', $vendedor->dni) }}"
                                   class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-[#D88429]/20 focus:border-[#D88429] transition-all"
                                   placeholder="Documento Nacional de Identidad">
                        </div>

                        <!-- Teléfono -->
                        <div>
                            <label class="block text-sm font-bold text-gray-700 mb-2 uppercase tracking-wide">Teléfono de contacto</label>
                            <input type="text" name="telefono" value="{{ old('telefono', $vendedor->telefono) }}"
                                   class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-[#D88429]/20 focus:border-[#D88429] transition-all"
                                   placeholder="Ej: +54 9 11 2345 6789">
                        </div>

                        <!-- Rol (Informativo, Deshabilitado) -->
                        <div>
                            <label class="block text-sm font-bold text-gray-400 mb-2 uppercase tracking-wide">Rol asignado</label>
                            <input type="text" value="{{ $vendedor->rol ? ucfirst($vendedor->rol->nombre) : 'Vendedor' }}" disabled
                                   class="w-full px-4 py-2.5 border border-gray-200 bg-gray-50 text-gray-500 rounded-lg cursor-not-allowed">
                        </div>

                        <!-- Foto de Perfil -->
                        <div class="sm:col-span-2">
                            <label class="block text-sm font-bold text-gray-700 mb-2 uppercase tracking-wide">Foto de Perfil</label>
                            
                            <!-- Foto actual con botón de eliminar (basurita) -->
                            @if($vendedor->foto)
                                <div id="current-image-container" class="mb-3 flex items-center gap-4 p-3 bg-gray-50 rounded-lg border border-gray-100 max-w-sm transition-all duration-300">
                                    <img src="{{ asset($vendedor->foto) }}" class="w-16 h-16 object-cover rounded-full border border-gray-200 shrink-0" alt="Foto actual">
                                    <div class="flex flex-col gap-1.5">
                                        <span class="text-xs text-gray-500 font-medium">Foto actual cargada</span>
                                        <button type="button" onclick="marcarEliminarFoto()" class="inline-flex items-center gap-1 text-xs text-red-600 font-bold hover:text-red-800 transition-colors">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                            </svg>
                                            Quitar foto
                                        </button>
                                    </div>
                                </div>

                                <div id="deleted-image-alert" class="hidden mb-3 items-center justify-between p-3 bg-red-50 rounded-lg border border-red-100 max-w-sm transition-all duration-300">
                                    <div class="flex items-center gap-2 text-red-700 text-xs font-semibold">
                                        <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                                        </svg>
                                        <span>Foto marcada para eliminar</span>
                                    </div>
                                    <button type="button" onclick="deshacerEliminarFoto()" class="text-xs text-gray-600 font-bold hover:text-gray-900 underline select-none">
                                        Deshacer
                                    </button>
                                </div>

                                <input type="hidden" name="eliminar_foto" id="eliminar_foto" value="0">
                            @endif

                            <!-- Vista previa dinámica para nuevo archivo cargado -->
                            <div id="new-image-preview-container" class="hidden mb-3 items-center justify-between p-3 bg-blue-50 rounded-lg border border-blue-100 max-w-sm transition-all duration-300">
                                <div class="flex items-center gap-3">
                                    <img id="new-image-preview" src="#" class="w-16 h-16 object-cover rounded-full border border-blue-200 shrink-0" alt="Nueva foto">
                                    <div>
                                        <span class="text-xs text-blue-700 font-bold block">Nueva foto seleccionada</span>
                                        <span id="new-image-name" class="text-[10px] text-blue-500 truncate block max-w-[150px]">Nombre de archivo</span>
                                    </div>
                                </div>
                                <button type="button" onclick="clearNewImage()" class="p-2 text-red-600 hover:text-red-800 hover:bg-red-100/50 rounded-full transition-colors" title="Quitar foto">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                    </svg>
                                </button>
                            </div>

                            <input type="file" name="foto" id="foto-input" accept="image/*" onchange="previewNewImage(event)"
                                   class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-[#D88429]/20 focus:border-[#D88429] transition-all bg-gray-50 file:mr-4 file:py-2 file:px-4 file:rounded-md file:border-0 file:text-xs file:font-semibold file:bg-[#166379] file:text-white hover:file:opacity-90">
                            <p class="text-xs text-gray-500 mt-1">Formatos permitidos: JPG, PNG, WEBP. Máximo 2MB.</p>
                        </div>

                        <!-- Separador visual -->
                        <div class="sm:col-span-2 border-t border-gray-150 pt-4 mt-2">
                            <h3 class="text-sm font-syncopate font-bold text-gray-900 uppercase tracking-wide">Cambiar Contraseña</h3>
                            <p class="text-xs text-gray-500 mt-1">Completa estos campos únicamente si deseas actualizar tu contraseña de acceso.</p>
                        </div>

                        <!-- Nueva Contraseña -->
                        <div>
                            <label class="block text-sm font-bold text-gray-700 mb-2 uppercase tracking-wide">Nueva Contraseña</label>
                            <input type="password" name="password"
                                   class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-[#D88429]/20 focus:border-[#D88429] transition-all"
                                   placeholder="Mínimo 6 caracteres">
                        </div>

                        <!-- Confirmar Nueva Contraseña -->
                        <div>
                            <label class="block text-sm font-bold text-gray-700 mb-2 uppercase tracking-wide">Confirmar Contraseña</label>
                            <input type="password" name="password_confirmation"
                                   class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-[#D88429]/20 focus:border-[#D88429] transition-all"
                                   placeholder="Repite la nueva contraseña">
                        </div>
                    </div>

                    <!-- Botones de Acción -->
                    <div class="pt-4 border-t border-gray-200 flex justify-end gap-3">
                        <a href="{{ route('vendedor.dashboard') }}" class="px-6 py-2.5 border border-gray-300 rounded-lg text-sm font-semibold hover:bg-gray-50 transition-colors">
                            Cancelar
                        </a>
                        <button type="submit" class="px-6 py-2.5 text-white rounded-lg text-sm font-semibold hover:opacity-90 transition-opacity shadow-md" style="background-color: #D88429;">
                            Guardar Cambios
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </main>
</div>

<script>
function marcarEliminarFoto() {
    const hiddenInput = document.getElementById('eliminar_foto');
    if (hiddenInput) hiddenInput.value = '1';
    
    const container = document.getElementById('current-image-container');
    if (container) container.classList.add('hidden');
    
    const alertBox = document.getElementById('deleted-image-alert');
    if (alertBox) {
        alertBox.classList.remove('hidden');
        alertBox.classList.add('flex');
    }
}

function deshacerEliminarFoto() {
    const hiddenInput = document.getElementById('eliminar_foto');
    if (hiddenInput) hiddenInput.value = '0';
    
    const container = document.getElementById('current-image-container');
    if (container) container.classList.remove('hidden');
    
    const alertBox = document.getElementById('deleted-image-alert');
    if (alertBox) {
        alertBox.classList.add('hidden');
        alertBox.classList.remove('flex');
    }
}

function previewNewImage(event) {
    const input = event.target;
    if (input.files && input.files[0]) {
        const file = input.files[0];
        const reader = new FileReader();
        
        reader.onload = function(e) {
            document.getElementById('new-image-preview').src = e.target.result;
            document.getElementById('new-image-name').textContent = file.name;
            
            const container = document.getElementById('new-image-preview-container');
            container.classList.remove('hidden');
            container.classList.add('flex');

            const currentImg = document.getElementById('current-image-container');
            if (currentImg) {
                currentImg.classList.add('opacity-40');
                const delInput = document.getElementById('eliminar_foto');
                if (delInput) delInput.value = '1';
            }
        }
        
        reader.readAsDataURL(file);
    }
}

function clearNewImage() {
    const input = document.getElementById('foto-input');
    if (input) input.value = '';
    
    const container = document.getElementById('new-image-preview-container');
    if (container) {
        container.classList.add('hidden');
        container.classList.remove('flex');
    }

    const currentImg = document.getElementById('current-image-container');
    if (currentImg) {
        currentImg.classList.remove('opacity-40');
        const delInput = document.getElementById('eliminar_foto');
        if (delInput) delInput.value = '0';
    }
}
</script>
@endsection
