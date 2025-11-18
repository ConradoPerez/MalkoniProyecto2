@extends('layouts.app')

@push('styles')
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
@endpush

@section('content')
<div class="min-h-screen bg-background text-gray-900">
    <div class="flex">
        {{-- Sidebar --}}
        @include('vendedor.components.sidebar')

        {{-- Contenido principal --}}
        <main class="flex-1 overflow-y-auto ml-56">
            <div class="p-4 lg:p-8">

                {{-- Título principal --}}
                <div class="flex flex-col lg:flex-row lg:items-center justify-between mb-8 gap-4">
                    <div>
                        <h1 class="text-3xl font-syncopate font-bold text-gray-900 tracking-wide">
                            GRUPOS DE CLIENTES
                        </h1>
                        <p class="text-gray-600 mt-1">
                            Organiza y gestiona grupos de clientes de {{ isset($vendedor) ? $vendedor->nombre : 'Vendedor' }}
                        </p>
                    </div>

                    <div class="flex items-center gap-4">
                        {{-- Botón Crear Nuevo Grupo --}}
                        <button onclick="showCreateGroupModal()" 
                                class="px-6 py-3 rounded-lg text-white font-semibold shadow-sm transition-colors hover:opacity-90 flex items-center"
                                style="background-color:#D88429;">
                            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
                            </svg>
                            Crear Nuevo Grupo
                        </button>

                        {{-- Info del vendedor --}}
                        <div class="flex items-center gap-3 bg-white border border-gray-200 rounded-xl px-4 py-3 shadow-sm">
                            <div class="w-10 h-10 rounded-full bg-gradient-to-r from-amber-500 to-orange-600 grid place-items-center">
                                <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                          d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                                </svg>
                            </div>
                            <div class="text-sm">
                                <div class="font-semibold text-gray-900">{{ isset($vendedor) ? $vendedor->nombre : 'Vendedor' }}</div>
                                <div class="text-gray-500">Vendedor activo</div>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Mensajes de éxito/error --}}
                @if(session('success'))
                    <div class="mb-6 p-4 bg-green-100 border border-green-300 text-green-700 rounded-lg">
                        {{ session('success') }}
                    </div>
                @endif

                @if(session('error'))
                    <div class="mb-6 p-4 bg-red-100 border border-red-300 text-red-700 rounded-lg">
                        {{ session('error') }}
                    </div>
                @endif

                {{-- Lista de Grupos Existentes --}}
                @forelse($grupos as $grupo)
                    <section class="bg-white border border-gray-200 rounded-xl mb-6 shadow-sm">
                        <div class="p-6">
                            <div class="flex items-center justify-between mb-4">
                                <div>
                                    <h2 class="text-lg font-semibold text-gray-900">{{ $grupo->nombre_grupo }}</h2>
                                    @if($grupo->descripcion)
                                        <p class="text-sm text-gray-500 mt-1">{{ $grupo->descripcion }}</p>
                                    @endif
                                    <p class="text-xs text-gray-400 mt-1">{{ $grupo->empresas->count() }} empresas</p>
                                </div>
                                <div class="flex gap-2">
                                    <button onclick="showAddEmpresaModal({{ $grupo->id_grupo }})" 
                                            title="Agregar empresa al grupo"
                                            class="w-8 h-8 flex items-center justify-center rounded-lg border border-gray-300 bg-white hover:bg-gray-100 text-gray-700 transition-colors">
                                        <span class="text-xl font-bold">+</span>
                                    </button>
                                    <button onclick="deleteGroup({{ $grupo->id_grupo }})" 
                                            title="Eliminar grupo completo"
                                            class="w-8 h-8 flex items-center justify-center rounded-lg border border-red-300 bg-white hover:bg-red-50 text-red-600 transition-colors">
                                        <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M9 2a1 1 0 00-.894.553L7.382 4H4a1 1 0 000 2v10a2 2 0 002 2h8a2 2 0 002-2V6a1 1 0 100-2h-3.382l-.724-1.447A1 1 0 0011 2H9zM7 8a1 1 0 012 0v6a1 1 0 11-2 0V8zm5-1a1 1 0 00-1 1v6a1 1 0 102 0V8a1 1 0 00-1-1z" clip-rule="evenodd"/>
                                        </svg>
                                    </button>
                                </div>
                            </div>

                            @if($grupo->empresas->count() > 0)
                                {{-- Resumen de Estados del Grupo --}}
                                @php
                                    $estadoColores = [
                                        'Nuevo' => 'bg-blue-100 text-blue-700',
                                        'Abierto' => 'bg-yellow-100 text-yellow-700',
                                        'Cotizado' => 'bg-green-100 text-green-700',
                                        'En entrega' => 'bg-purple-100 text-purple-700',
                                    ];
                                    
                                    // Calcular estadísticas totales del grupo
                                    $estadisticasGrupo = [
                                        'Nuevo' => 0,
                                        'Abierto' => 0,
                                        'Cotizado' => 0,
                                        'En entrega' => 0
                                    ];
                                    
                                    foreach ($grupo->empresas as $empresa) {
                                        $cotizacionesEmpresa = $empresa->cotizaciones()
                                            ->where('id_empleados', $vendedor->id_empleado)
                                            ->get();
                                        
                                        foreach ($cotizacionesEmpresa as $cotizacion) {
                                            $estadoActual = $cotizacion->getEstadoActualDirecto();
                                            $nombreEstado = $estadoActual ? $estadoActual->nombre : 'Nuevo';
                                            
                                            if (isset($estadisticasGrupo[$nombreEstado])) {
                                                $estadisticasGrupo[$nombreEstado]++;
                                            }
                                        }
                                    }
                                @endphp

                                <div class="mb-4 p-4 bg-gray-50 rounded-lg border border-gray-200">
                                    <div class="flex flex-wrap gap-2">
                                        @foreach($estadisticasGrupo as $estado => $cantidad)
                                            @if($cantidad > 0)
                                                <span class="inline-flex items-center px-3 py-1 rounded text-sm font-medium {{ $estadoColores[$estado] ?? 'bg-gray-100 text-gray-700' }}">
                                                    {{ $cantidad }} {{ $estado }}
                                                </span>
                                            @endif
                                        @endforeach
                                        @if(array_sum($estadisticasGrupo) == 0)
                                            <span class="text-sm text-gray-500">Sin cotizaciones</span>
                                        @endif
                                    </div>
                                </div>

                                {{-- Lista de Empresas --}}
                                <div class="overflow-x-auto">
                                    <div class="min-w-full">
                                        <!-- Encabezados -->
                                        <div class="grid grid-cols-[150px_1fr_150px_80px] gap-2 bg-gray-50 border-b border-gray-200 px-2 py-2">
                                            <div class="text-left text-sm font-semibold text-gray-700">CUIT</div>
                                            <div class="text-left text-sm font-semibold text-gray-700">Razón Social</div>
                                            <div class="text-left text-sm font-semibold text-gray-700">Cotizaciones</div>
                                            <div class="text-center text-sm font-semibold text-gray-700">Acciones</div>
                                        </div>
                                        <!-- Filas -->
                                        <div class="bg-white divide-y divide-gray-200">
                                            @foreach($grupo->empresas as $empresa)
                                                <div class="grid grid-cols-[150px_1fr_150px_80px] gap-2 px-2 py-2 hover:bg-gray-50 transition-colors">
                                                    <div class="text-sm font-mono text-gray-600 truncate">{{ $empresa->cuit_formateado }}</div>
                                                    <div class="text-sm font-medium text-gray-900 truncate">{{ $empresa->nombre }}</div>
                                                    <div>
                                                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                                            {{ $empresa->cotizaciones_count ?? 0 }} cotizaciones
                                                        </span>
                                                    </div>
                                                    <div class="text-center">
                                                        <button onclick="removeEmpresa({{ $grupo->id_grupo }}, {{ $empresa->id_empresa }})" 
                                                                title="Eliminar empresa del grupo"
                                                                class="inline-flex items-center justify-center w-7 h-7 rounded-lg border border-red-300 bg-white hover:bg-red-50 text-red-600 transition-colors">
                                                            <svg class="w-3.5 h-3.5" fill="currentColor" viewBox="0 0 20 20">
                                                                <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"/>
                                                            </svg>
                                                        </button>
                                                    </div>
                                                </div>
                                            @endforeach
                                        </div>
                                    </div>
                                </div>
                            @else
                                <div class="text-center py-8 text-gray-500 bg-gray-50 rounded-lg">
                                    <svg class="w-12 h-12 mx-auto mb-4 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                                    </svg>
                                    <p>No hay empresas en este grupo</p>
                                    <button onclick="showAddEmpresaModal({{ $grupo->id_grupo }})" class="mt-2 text-sm text-orange-600 hover:text-orange-700">
                                        Agregar empresas
                                    </button>
                                </div>
                            @endif
                        </div>
                    </section>
                @empty
                    {{-- Estado vacío --}}
                    <section class="bg-white border border-gray-200 rounded-xl mb-6 shadow-sm">
                        <div class="text-center py-16">
                            <div class="w-16 h-16 mx-auto mb-4 rounded-full bg-gray-100 flex items-center justify-center">
                                <svg class="w-8 h-8 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                                </svg>
                            </div>
                            <h3 class="text-lg font-medium text-gray-900 mb-2">No tienes grupos creados</h3>
                            <p class="text-gray-500 mb-4">Crea tu primer grupo de clientes para organizar mejor tus cotizaciones</p>
                        </div>
                    </section>
                @endforelse

            </div>
        </main>
    </div>
</div>

{{-- Modal para crear grupo MEJORADO --}}
<div id="createGroupModal" class="fixed inset-0 bg-gradient-to-br from-white/10 via-gray-100/20 to-orange-100/30 backdrop-blur-md hidden z-50">
    <div class="flex items-center justify-center min-h-screen p-4">
        <div class="bg-white/95 backdrop-blur-sm rounded-2xl shadow-2xl max-w-2xl w-full max-h-[90vh] overflow-y-auto transform transition-all border border-gray-200/50 ring-1 ring-gray-300/30">
            <form id="createGroupForm" action="{{ route('vendedor.app.grupos.store') }}" method="POST">
                @csrf
                <input type="hidden" name="empleado_id" value="{{ request('empleado_id', 1) }}">
                
                <!-- Encabezado del modal -->
                <div class="p-6 border-b border-gray-200/50 bg-gradient-to-r from-orange-50/80 to-orange-100/60 backdrop-blur-sm rounded-t-2xl">
                    <div class="flex items-center justify-between">
                        <h3 class="text-xl font-semibold text-gray-900">Crear Nuevo Grupo</h3>
                        <button type="button" onclick="hideCreateGroupModal()" 
                                class="text-gray-400 hover:text-gray-600 transition-colors">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                            </svg>
                        </button>
                    </div>
                </div>

                <!-- Paso 1: Información del grupo -->
                <div id="step1" class="p-6">
                    <h4 class="text-md font-medium text-gray-700 mb-4">Información del Grupo</h4>
                    
                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700 mb-2">Nombre del Grupo</label>
                        <input type="text" name="nombre_grupo" id="nombreGrupo" required maxlength="100"
                               class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-orange-500 focus:border-orange-500"
                               placeholder="Ej: Clientes Capital">
                    </div>

                    <div class="mb-6">
                        <label class="block text-sm font-medium text-gray-700 mb-2">Descripción (Opcional)</label>
                        <textarea name="descripcion" id="descripcionGrupo" rows="3" maxlength="500"
                                  class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-orange-500 focus:border-orange-500"
                                  placeholder="Descripción del grupo..."></textarea>
                    </div>

                    <div class="flex justify-end space-x-3">
                        <button type="button" onclick="hideCreateGroupModal()" 
                                class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50">
                            Cancelar
                        </button>
                        <button type="button" onclick="showStep2()" 
                                class="px-4 py-2 text-sm font-medium text-white rounded-lg" style="background-color:#D88429;">
                            Continuar
                        </button>
                    </div>
                </div>

                <!-- Paso 2: Seleccionar empresas -->
                <div id="step2" class="p-6 hidden">
                    <h4 class="text-md font-medium text-gray-700 mb-4">Seleccionar Empresas para el Grupo</h4>
                    
                    <div class="mb-4">
                        <p class="text-sm text-gray-600 mb-3">Selecciona las empresas que deseas agregar a "<span id="grupoNombrePreview"></span>":</p>
                        
                        <div class="max-h-60 overflow-y-auto border border-gray-300 rounded-lg">
                            @foreach($empresasDisponibles as $empresa)
                                <label class="flex items-center p-3 hover:bg-gray-50 cursor-pointer border-b border-gray-100 last:border-b-0">
                                    <input type="checkbox" name="empresas[]" value="{{ $empresa->id_empresa }}" 
                                           class="mr-3 h-4 w-4 text-orange-600 focus:ring-orange-500 border-gray-300 rounded">
                                    <div class="flex-1">
                                        <div class="font-medium text-gray-900">{{ $empresa->nombre }}</div>
                                        <div class="text-sm text-gray-500">CUIT: {{ $empresa->cuit_formateado }}</div>
                                    </div>
                                </label>
                            @endforeach
                            
                            @if($empresasDisponibles->count() === 0)
                                <div class="p-4 text-center text-gray-500">
                                    <p>No hay empresas disponibles para agregar.</p>
                                    <p class="text-xs mt-1">Todas las empresas con cotizaciones ya están en otros grupos.</p>
                                </div>
                            @endif
                        </div>
                    </div>

                    <div class="flex justify-between space-x-3">
                        <button type="button" onclick="showStep1()" 
                                class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50">
                            Volver
                        </button>
                        <div class="flex space-x-3">
                            <button type="button" onclick="hideCreateGroupModal()" 
                                    class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50">
                                Cancelar
                            </button>
                            <button type="button" onclick="createGroupWithAjax()" 
                                    class="px-4 py-2 text-sm font-medium text-white rounded-lg" style="background-color:#D88429;">
                                Crear Grupo
                            </button>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- Modal para agregar empresa --}}
<div id="addEmpresaModal" class="fixed inset-0 bg-gradient-to-br from-white/10 via-gray-100/20 to-orange-100/30 backdrop-blur-md hidden z-50">
    <div class="flex items-center justify-center min-h-screen p-4">
        <div class="bg-white/95 backdrop-blur-sm rounded-2xl shadow-2xl max-w-lg w-full transform transition-all border border-gray-200/50 ring-1 ring-gray-300/30">
            <div class="p-6 border-b border-gray-200/50 bg-gradient-to-r from-orange-50/80 to-orange-100/60 backdrop-blur-sm rounded-t-2xl">
                <div class="flex items-center justify-between">
                    <h3 class="text-xl font-semibold text-gray-900">Agregar Empresas al Grupo</h3>
                    <button type="button" onclick="hideAddEmpresaModal()" 
                            class="text-gray-400 hover:text-gray-600 transition-colors">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                    </button>
                </div>
            </div>
            
            <div class="p-6">
                <div class="mb-6">
                    <label class="block text-sm font-medium text-gray-700 mb-3">Seleccionar Empresas (múltiple selección)</label>
                    <div class="space-y-2 max-h-64 overflow-y-auto border border-gray-200 rounded-lg p-3 bg-gray-50">
                        @foreach($empresasDisponibles as $empresa)
                        <label class="flex items-center space-x-3 cursor-pointer hover:bg-white p-2 rounded transition-colors">
                            <input type="checkbox" 
                                   name="empresas_add[]" 
                                   value="{{ $empresa->id_empresa }}"
                                   class="rounded border-gray-300 text-orange-600 focus:ring-orange-500 h-4 w-4">
                            <div class="flex-1">
                                <div class="text-sm font-medium text-gray-900">{{ $empresa->nombre }}</div>
                                <div class="text-xs text-gray-500">{{ $empresa->cuit_formateado }}</div>
                            </div>
                        </label>
                        @endforeach
                    </div>
                    <p class="text-xs text-gray-500 mt-2">Selecciona todas las empresas que desees agregar al grupo</p>
                </div>

                <div class="flex justify-end space-x-3">
                    <button type="button" onclick="hideAddEmpresaModal()" 
                            class="px-6 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-orange-500 transition-colors">
                        Cancelar
                    </button>
                    <button type="button" onclick="addEmpresaToGroup()" 
                            class="px-6 py-2 text-sm font-medium text-white rounded-lg hover:bg-orange-700 focus:outline-none focus:ring-2 focus:ring-orange-500 transition-colors" style="background-color:#D88429;">
                        Agregar Seleccionadas
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
let currentGroupId = null;
const empleadoId = {{ request('empleado_id', 1) }};

function showCreateGroupModal() {
    document.getElementById('createGroupModal').classList.remove('hidden');
    showStep1();
}

function hideCreateGroupModal() {
    document.getElementById('createGroupModal').classList.add('hidden');
    // Limpiar formulario
    document.getElementById('createGroupForm').reset();
    showStep1();
}

function showStep1() {
    document.getElementById('step1').classList.remove('hidden');
    document.getElementById('step2').classList.add('hidden');
}

function showStep2() {
    const nombreGrupo = document.getElementById('nombreGrupo').value;
    if (!nombreGrupo.trim()) {
        alert('Por favor ingresa un nombre para el grupo');
        return;
    }
    
    document.getElementById('step1').classList.add('hidden');
    document.getElementById('step2').classList.remove('hidden');
    document.getElementById('grupoNombrePreview').textContent = nombreGrupo;
}

function createGroupWithAjax() {
    const formData = new FormData();
    formData.append('nombre_grupo', document.getElementById('nombreGrupo').value);
    formData.append('descripcion', document.getElementById('descripcionGrupo').value);
    formData.append('empleado_id', empleadoId);
    
    // Agregar empresas seleccionadas
    const empresasSeleccionadas = document.querySelectorAll('input[name="empresas[]"]:checked');
    empresasSeleccionadas.forEach(checkbox => {
        formData.append('empresas[]', checkbox.value);
    });

    fetch(`/vendedor/grupos?empleado_id=${empleadoId}`, {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
            'Accept': 'application/json'
        },
        body: formData
    })
    .then(response => {
        if (!response.ok) {
            if (response.headers.get('content-type')?.includes('text/html')) {
                throw new Error('El servidor devolvió HTML en lugar de JSON. Revisa los logs del servidor.');
            }
            throw new Error(`HTTP error! status: ${response.status}`);
        }
        return response.json();
    })
    .then(data => {
        if (data.success) {
            hideCreateGroupModal();
            Swal.fire({
                title: '¡Creado!',
                text: 'El grupo ha sido creado exitosamente.',
                icon: 'success',
                confirmButtonColor: '#D88429',
                timer: 2000,
                showConfirmButton: false
            }).then(() => {
                window.location.reload();
            });
        } else {
            Swal.fire('Error', data.error || 'Error al crear el grupo', 'error');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        Swal.fire('Error', error.message, 'error');
    });
}

function showAddEmpresaModal(groupId) {
    currentGroupId = groupId;
    document.getElementById('addEmpresaModal').classList.remove('hidden');
}

function hideAddEmpresaModal() {
    document.getElementById('addEmpresaModal').classList.add('hidden');
    currentGroupId = null;
    // Desmarcar todos los checkboxes
    document.querySelectorAll('input[name="empresas_add[]"]').forEach(checkbox => {
        checkbox.checked = false;
    });
}

function addEmpresaToGroup() {
    const empresasSeleccionadas = document.querySelectorAll('input[name="empresas_add[]"]:checked');
    
    if (empresasSeleccionadas.length === 0) {
        Swal.fire('Error', 'Por favor seleccione al menos una empresa', 'warning');
        return;
    }
    
    if (!currentGroupId) {
        Swal.fire('Error', 'No se ha seleccionado un grupo válido', 'error');
        return;
    }

    const empresas = Array.from(empresasSeleccionadas).map(checkbox => checkbox.value);

    console.log('Enviando petición:', {
        url: `/vendedor/grupos/${currentGroupId}/empresas?empleado_id=${empleadoId}`,
        empresas: empresas,
        currentGroupId: currentGroupId
    });

    // Mostrar loading
    Swal.fire({
        title: 'Agregando empresas...',
        allowOutsideClick: false,
        didOpen: () => {
            Swal.showLoading();
        }
    });

    fetch(`/vendedor/grupos/${currentGroupId}/empresas?empleado_id=${empleadoId}`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'Accept': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        },
        body: JSON.stringify({
            empresas: empresas,
            empleado_id: empleadoId
        })
    })
    .then(response => {
        console.log('Response status:', response.status);
        if (!response.ok) {
            if (response.headers.get('content-type')?.includes('text/html')) {
                throw new Error('El servidor devolvió HTML en lugar de JSON. Revisa los logs del servidor.');
            }
            throw new Error(`HTTP error! status: ${response.status}`);
        }
        return response.json();
    })
    .then(data => {
        console.log('Response data:', data);
        if (data.success) {
            hideAddEmpresaModal();
            Swal.fire({
                title: '¡Agregadas!',
                text: data.success,
                icon: 'success',
                confirmButtonColor: '#D88429',
                timer: 3000,
                showConfirmButton: false
            }).then(() => {
                window.location.reload();
            });
        } else {
            Swal.fire('Error', data.error || 'Error al agregar empresas', 'error');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        Swal.fire('Error', error.message, 'error');
    });
}

function removeEmpresa(groupId, empresaId) {
    Swal.fire({
        title: '¿Remover empresa?',
        text: '¿Está seguro de que desea remover esta empresa del grupo?',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#d33',
        cancelButtonColor: '#3085d6',
        confirmButtonText: 'Sí, remover',
        cancelButtonText: 'Cancelar',
        reverseButtons: true
    }).then((result) => {
        if (result.isConfirmed) {
            // Mostrar loading
            Swal.fire({
                title: 'Removiendo...',
                allowOutsideClick: false,
                didOpen: () => {
                    Swal.showLoading();
                }
            });

            fetch(`/vendedor/grupos/${groupId}/empresas/${empresaId}?empleado_id=${empleadoId}`, {
                method: 'DELETE',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                    'Accept': 'application/json'
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    Swal.fire({
                        title: '¡Removida!',
                        text: 'La empresa ha sido removida del grupo exitosamente.',
                        icon: 'success',
                        confirmButtonColor: '#D88429',
                        timer: 2000,
                        showConfirmButton: false
                    }).then(() => {
                        window.location.reload();
                    });
                } else {
                    Swal.fire('Error', data.error || 'Error al remover empresa', 'error');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                Swal.fire('Error', 'Error de conexión al remover empresa', 'error');
            });
        }
    });
}

function deleteGroup(groupId) {
    Swal.fire({
        title: '¿Eliminar grupo?',
        text: 'Esta acción no se puede deshacer. Se eliminará el grupo y todas sus empresas asociadas.',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#d33',
        cancelButtonColor: '#3085d6',
        confirmButtonText: 'Sí, eliminar',
        cancelButtonText: 'Cancelar',
        reverseButtons: true
    }).then((result) => {
        if (result.isConfirmed) {
            // Mostrar loading
            Swal.fire({
                title: 'Eliminando...',
                allowOutsideClick: false,
                didOpen: () => {
                    Swal.showLoading();
                }
            });

            fetch(`/vendedor/grupos/${groupId}?empleado_id=${empleadoId}`, {
                method: 'DELETE',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                    'Accept': 'application/json'
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    Swal.fire({
                        title: '¡Eliminado!',
                        text: 'El grupo ha sido eliminado exitosamente.',
                        icon: 'success',
                        confirmButtonColor: '#D88429',
                        timer: 2000,
                        showConfirmButton: false
                    }).then(() => {
                        window.location.reload();
                    });
                } else {
                    Swal.fire('Error', data.error || 'Error al eliminar grupo', 'error');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                Swal.fire('Error', 'Error de conexión al eliminar grupo', 'error');
            });
        }
    });
}
</script>

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
@endpush

@endsection
