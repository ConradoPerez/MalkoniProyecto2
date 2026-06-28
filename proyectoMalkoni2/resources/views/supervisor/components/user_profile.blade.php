@php
    $supervisorId = (int) session('user_id', 0);
    $supervisor = $supervisorId ? \App\Models\Empleado::find($supervisorId) : null;
@endphp

<div class="relative inline-block text-left" id="userDropdownContainer">
    <!-- Trigger Button -->
    <button id="userDropdownTrigger" class="flex items-center gap-3 bg-white border border-gray-200 rounded-xl px-4 py-2 hover:bg-gray-50 transition-colors focus:outline-none select-none text-left shadow-sm">
        <div class="w-10 h-10 rounded-full bg-gray-300 flex items-center justify-center overflow-hidden border border-gray-200 shrink-0">
            @if(isset($supervisor) && $supervisor->foto)
                <img class="w-10 h-10 rounded-full object-cover" 
                     src="{{ asset($supervisor->foto) }}" 
                     alt="{{ $supervisor->nombre }}">
            @else
                <svg class="w-5 h-5 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                          d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                </svg>
            @endif
        </div>
        <div class="text-sm">
            <div class="font-semibold text-gray-900 flex items-center gap-1">
                {{ isset($supervisor) && $supervisor ? $supervisor->nombre : 'Supervisor' }}
                <svg class="w-4 h-4 text-gray-500 transform transition-transform" id="userDropdownArrow" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                </svg>
            </div>
            <div class="text-gray-500 text-xs">Supervisor activo</div>
        </div>
    </button>

    <!-- Dropdown Menu -->
    <div id="userDropdownMenu" class="absolute right-0 mt-2 w-48 bg-white border border-gray-200 rounded-xl shadow-lg py-2 hidden z-30 transition-all duration-200 transform scale-95 origin-top-right">
        <a href="{{ route('supervisor.perfil.edit') }}" class="w-full text-left px-4 py-2.5 text-sm text-gray-700 hover:bg-gray-50 hover:text-gray-900 font-semibold transition-colors flex items-center gap-2">
            <svg class="w-4 h-4 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
            </svg>
            Editar Perfil
        </a>

        <div class="h-px bg-gray-100 my-1"></div>

        <form action="{{ route('logout') }}" method="POST" class="m-0">
            @csrf
            <button type="submit" class="w-full text-left px-4 py-2.5 text-sm text-red-600 hover:bg-red-50 hover:text-red-700 font-semibold transition-colors flex items-center gap-2">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"></path>
                </svg>
                Cerrar Sesión
            </button>
        </form>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const trigger = document.getElementById('userDropdownTrigger');
        const menu = document.getElementById('userDropdownMenu');
        const arrow = document.getElementById('userDropdownArrow');

        if (trigger && menu) {
            trigger.addEventListener('click', function(e) {
                e.stopPropagation();
                const isHidden = menu.classList.contains('hidden');
                if (isHidden) {
                    menu.classList.remove('hidden');
                    // Micro-animation trigger
                    setTimeout(() => {
                        menu.classList.remove('scale-95');
                        menu.classList.add('scale-100');
                    }, 10);
                    arrow.classList.add('rotate-180');
                } else {
                    closeDropdown();
                }
            });

            document.addEventListener('click', function(e) {
                if (!menu.contains(e.target) && !trigger.contains(e.target)) {
                    closeDropdown();
                }
            });

            function closeDropdown() {
                menu.classList.add('scale-95');
                menu.classList.remove('scale-100');
                arrow.classList.remove('rotate-180');
                setTimeout(() => {
                    menu.classList.add('hidden');
                }, 150);
            }
        }
    });
</script>
