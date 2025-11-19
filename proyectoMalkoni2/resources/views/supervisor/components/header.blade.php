<!-- Desktop Header -->
<div class="hidden lg:flex items-center justify-between mb-8">
    <div>
        <h1 class="text-3xl font-syncopate font-bold text-gray-900">
            @yield('page-title', 'DASHBOARD')
        </h1>
    </div>
    <div class="flex items-center space-x-3">
        <div class="w-10 h-10 rounded-full bg-gray-300 flex items-center justify-center">
            @if(isset($supervisor) && $supervisor->foto)
                <img class="w-10 h-10 rounded-full object-cover" 
                     src="{{ asset('storage/' . $supervisor->foto) }}" 
                     alt="{{ $supervisor->nombre }}">
            @else
                <svg class="w-5 h-5 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                          d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                </svg>
            @endif
        </div>
        <span class="text-sm font-medium text-gray-900">
            {{ isset($supervisor) && $supervisor ? $supervisor->nombre : 'Supervisor' }}
        </span>
    </div>
</div>

<!-- Mobile Header Title -->
<div class="lg:hidden mb-6">
    <h1 class="text-2xl font-syncopate font-bold text-gray-900">
        @yield('page-title', 'DASHBOARD')
    </h1>
</div>