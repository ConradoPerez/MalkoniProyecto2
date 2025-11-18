<!-- Header with Client Name and Avatar -->
<div class="flex items-center justify-between mb-8">
    <h1 class="text-3xl font-syncopate font-bold text-gray-900">
        DASHBOARD CLIENTE
    </h1>
    <div class="flex items-center gap-3 bg-white border border-gray-200 rounded-xl px-4 py-3 shadow-sm">
        <div class="w-10 h-10 rounded-full bg-gradient-to-r from-blue-500 to-indigo-600 grid place-items-center">
            <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                      d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
            </svg>
        </div>
        <div class="text-sm">
            <div class="font-bold text-gray-900">Nombre y Apellido</div>
            <div class="text-gray-500 text-xs">{{ $nombreEmpresa ?? 'Sin empresa' }}</div>
        </div>
    </div>
</div>
