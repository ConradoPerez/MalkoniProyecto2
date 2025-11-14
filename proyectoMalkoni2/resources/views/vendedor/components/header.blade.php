<!-- Header with Vendor Name and Avatar -->
<div class="flex items-center justify-between mb-8">
    <h1 class="text-3xl font-syncopate font-bold text-gray-900">
        DASHBOARD VENDEDOR
    </h1>
    <div class="flex items-center gap-3 bg-white border border-gray-200 rounded-xl px-4 py-3 shadow-sm">
                        <div class="w-10 h-10 rounded-full bg-gradient-to-r from-amber-500 to-orange-600 grid place-items-center">
                            <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                      d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                            </svg>
                        </div>
                        <div class="text-sm">
                            <div class="font-semibold text-gray-900">{{ isset($vendedor) ? $vendedor->nombre : 'Vendedor' }}</div>
                            <div class="text-gray-500">{{ isset($vendedor) ? $vendedor->rol->nombre ?? 'Vendedor' : 'Vendedor activo' }}</div>
                        </div>
                    </div>
</div>