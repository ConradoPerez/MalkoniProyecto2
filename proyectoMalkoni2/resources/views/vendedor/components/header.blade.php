<!-- Header with Vendor Name and Avatar -->
<div class="flex items-center justify-between mb-8">
    <h1 class="text-3xl font-syncopate font-bold text-gray-900">
        DASHBOARD VENDEDOR
    </h1>
    <div class="flex items-center space-x-3">
        <span class="text-sm font-medium">
            {{ isset($vendedor) ? $vendedor->nombre : 'Vendedor' }}
        </span>
        <div class="w-10 h-10 rounded-full bg-primary text-white flex items-center justify-center font-bold">
            @if(isset($vendedor) && $vendedor->nombre)
                {{ strtoupper(substr($vendedor->nombre, 0, 1)) . strtoupper(substr(explode(' ', $vendedor->nombre)[1] ?? $vendedor->nombre, 0, 1)) }}
            @else
                VN
            @endif
        </div>
    </div>
</div>