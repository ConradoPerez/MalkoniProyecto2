@extends('layouts.app')

@section('content')
<div class="min-h-screen bg-background text-gray-900">
    <div class="flex">
        {{-- Sidebar --}}
        @include('vendedor.components.sidebar')

        {{-- Contenido principal --}}
        <main class="flex-1 overflow-y-auto ml-48">
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

                    <div class="flex items-center gap-3 bg-white border border-gray-200 rounded-xl px-4 py-3 shadow-sm">
                        <div class="w-10 h-10 rounded-full bg-gradient-to-r from-amber-500 to-orange-600 grid place-items-center">
                            <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                      d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/>
                            </svg>
                        </div>
                        <div class="text-sm">
                            <div class="font-semibold text-gray-900">{{ isset($vendedor) ? $vendedor->nombre : 'Vendedor' }}</div>
                            <div class="text-gray-500">{{ isset($vendedor) ? $vendedor->rol->nombre ?? 'Vendedor' : 'Vendedor activo' }}</div>
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
                                            class="w-8 h-8 flex items-center justify-center rounded-lg border border-gray-300 bg-white hover:bg-gray-100 text-gray-700 transition-colors">
                                        <span class="text-xl font-bold">+</span>
                                    </button>
                                    <button onclick="deleteGroup({{ $grupo->id_grupo }})" 
                                            class="w-8 h-8 flex items-center justify-center rounded-lg border border-red-300 bg-white hover:bg-red-50 text-red-600 transition-colors">
                                        <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M9 2a1 1 0 00-.894.553L7.382 4H4a1 1 0 000 2v10a2 2 0 002 2h8a2 2 0 002-2V6a1 1 0 100-2h-3.382l-.724-1.447A1 1 0 0011 2H9zM7 8a1 1 0 012 0v6a1 1 0 11-2 0V8zm5-1a1 1 0 00-1 1v6a1 1 0 102 0V8a1 1 0 00-1-1z" clip-rule="evenodd"/>
                                        </svg>
                                    </button>
                                </div>
                            </div>

                            @if($grupo->empresas->count() > 0)
                                <div class="overflow-x-auto">
                                    <table class="w-full border-collapse">
                                        <thead class="bg-gray-50 border-b border-gray-200">
                                            <tr class="text-left text-sm font-semibold text-gray-700">
                                                <th class="px-4 py-3">CUIT</th>
                                                <th class="px-4 py-3">Razón Social</th>
                                                <th class="px-4 py-3">Cotizaciones</th>
                                                <th class="px-4 py-3 text-center">Acciones</th>
                                            </tr>
                                        </thead>
                                        <tbody class="text-sm divide-y divide-gray-200">
                                            @foreach($grupo->empresas as $empresa)
                                                <tr class="hover:bg-gray-50 transition-colors">
                                                    <td class="px-4 py-3 font-mono text-gray-600">{{ $empresa->cuit_formateado }}</td>
                                                    <td class="px-4 py-3 font-medium text-gray-900">{{ $empresa->nombre }}</td>
                                                    <td class="px-4 py-3">
                                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                                            {{ $empresa->cotizaciones_count ?? 0 }} cotizaciones
                                                        </span>
                                                    </td>
                                                    <td class="px-4 py-3 text-center">
                                                        <button onclick="removeEmpresa({{ $grupo->id_grupo }}, {{ $empresa->id_empresa }})" 
                                                                class="text-red-600 hover:text-red-800 transition-colors">
                                                            <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                                                                <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"/>
                                                            </svg>
                                                        </button>
                                                    </td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            @else
                                <div class="text-center py-8 text-gray-500 bg-gray-50 rounded-lg">
                                    <svg class="w-12 h-12 mx-auto mb-4 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/>
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
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/>
                                </svg>
                            </div>
                            <h3 class="text-lg font-medium text-gray-900 mb-2">No tienes grupos creados</h3>
                            <p class="text-gray-500 mb-4">Crea tu primer grupo de clientes para organizar mejor tus cotizaciones</p>
                        </div>
                    </section>
                @endforelse

                {{-- Botón Crear Nuevo Grupo --}}
                <div class="flex justify-center">
                    <button onclick="showCreateGroupModal()" 
                            class="px-6 py-3 rounded-lg text-white font-semibold shadow-sm transition-colors hover:opacity-90"
                            style="background-color:#D88429;">
                        <svg class="w-5 h-5 inline mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
                        </svg>
                        Crear Nuevo Grupo
                    </button>
                </div>

            </div>
        </main>
    </div>
</div>

{{-- Modal para crear grupo --}}
<div id="createGroupModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 hidden z-50">
    <div class="flex items-center justify-center min-h-screen p-4">
        <div class="bg-white rounded-lg shadow-xl max-w-md w-full">
            <form action="{{ route('vendedor.app.grupos.store') }}" method="POST">
                @csrf
                <input type="hidden" name="empleado_id" value="{{ request('empleado_id', 1) }}">
                <div class="p-6">
                    <h3 class="text-lg font-medium text-gray-900 mb-4">Crear Nuevo Grupo</h3>
                    
                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700 mb-2">Nombre del Grupo</label>
                        <input type="text" name="nombre_grupo" required maxlength="100"
                               class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-orange-500 focus:border-orange-500"
                               placeholder="Ej: Clientes Capital">
                    </div>

                    <div class="mb-6">
                        <label class="block text-sm font-medium text-gray-700 mb-2">Descripción (Opcional)</label>
                        <textarea name="descripcion" rows="3" maxlength="500"
                                  class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-orange-500 focus:border-orange-500"
                                  placeholder="Descripción del grupo..."></textarea>
                    </div>

                    <div class="flex justify-end space-x-3">
                        <button type="button" onclick="hideCreateGroupModal()" 
                                class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50">
                            Cancelar
                        </button>
                        <button type="submit" 
                                class="px-4 py-2 text-sm font-medium text-white rounded-lg" style="background-color:#D88429;">
                            Crear Grupo
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- Modal para agregar empresa --}}
<div id="addEmpresaModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 hidden z-50">
    <div class="flex items-center justify-center min-h-screen p-4">
        <div class="bg-white rounded-lg shadow-xl max-w-md w-full">
            <div class="p-6">
                <h3 class="text-lg font-medium text-gray-900 mb-4">Agregar Empresa al Grupo</h3>
                
                <div class="mb-6">
                    <label class="block text-sm font-medium text-gray-700 mb-2">Seleccionar Empresa</label>
                    <select id="empresaSelect" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-orange-500 focus:border-orange-500">
                        <option value="">Seleccione una empresa...</option>
                        @foreach($empresasDisponibles as $empresa)
                            <option value="{{ $empresa->id_empresa }}">{{ $empresa->nombre }} ({{ $empresa->cuit_formateado }})</option>
                        @endforeach
                    </select>
                </div>

                <div class="flex justify-end space-x-3">
                    <button type="button" onclick="hideAddEmpresaModal()" 
                            class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50">
                        Cancelar
                    </button>
                    <button type="button" onclick="addEmpresaToGroup()" 
                            class="px-4 py-2 text-sm font-medium text-white rounded-lg" style="background-color:#D88429;">
                        Agregar
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
}

function hideCreateGroupModal() {
    document.getElementById('createGroupModal').classList.add('hidden');
}

function showAddEmpresaModal(groupId) {
    currentGroupId = groupId;
    document.getElementById('addEmpresaModal').classList.remove('hidden');
}

function hideAddEmpresaModal() {
    document.getElementById('addEmpresaModal').classList.add('hidden');
    currentGroupId = null;
    document.getElementById('empresaSelect').value = '';
}

function addEmpresaToGroup() {
    const empresaId = document.getElementById('empresaSelect').value;
    if (!empresaId || !currentGroupId) {
        alert('Por favor seleccione una empresa');
        return;
    }

    fetch(`/vendedor/grupos/${currentGroupId}/empresas?empleado_id=${empleadoId}`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        },
        body: JSON.stringify({
            id_empresa: empresaId,
            empleado_id: empleadoId
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            location.reload();
        } else {
            alert(data.error || 'Error al agregar empresa');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Error al agregar empresa');
    });
}

function removeEmpresa(groupId, empresaId) {
    if (!confirm('¿Está seguro de que desea remover esta empresa del grupo?')) {
        return;
    }

    fetch(`/vendedor/grupos/${groupId}/empresas/${empresaId}?empleado_id=${empleadoId}`, {
        method: 'DELETE',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            location.reload();
        } else {
            alert(data.error || 'Error al remover empresa');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Error al remover empresa');
    });
}

function deleteGroup(groupId) {
    if (!confirm('¿Está seguro de que desea eliminar este grupo? Esta acción no se puede deshacer.')) {
        return;
    }

    fetch(`/vendedor/grupos/${groupId}?empleado_id=${empleadoId}`, {
        method: 'DELETE',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        }
    })
    .then(response => {
        if (response.ok) {
            location.reload();
        } else {
            alert('Error al eliminar grupo');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Error al eliminar grupo');
    });
}
</script>
@endsection
