@php
    $personaId       = (int) session('user_id', 0);
    $persona         = $personaId ? \App\Models\Persona::find($personaId) : null;
    $nombreCliente   = $persona ? trim($persona->nombre . ' ' . $persona->apellido) : 'Cliente';

    $totalCotizaciones = $personaId
        ? \App\Models\Cotizacion::where('id_personas', $personaId)->whereNotNull('id_empleados')->count()
        : 0;

    $mensajesSinLeer = $personaId
        ? \App\Models\MensajeCotizacion::whereHas('cotizacion', fn($q) => $q->where('id_personas', $personaId))
            ->where('sender_type', 'vendedor')
            ->where('leido', false)
            ->count()
        : 0;
@endphp

<!-- Sidebar -->
<aside class="hidden lg:flex w-56 bg-white border-r border-gray-200 p-6 fixed left-0 top-0 h-screen overflow-y-auto flex-col z-30">

    <!-- Logo -->
    <div class="mb-8 px-2">
        <div class="flex items-center justify-center">
            <img src="{{ asset('logo/logo negro.png') }}" alt="Malkoni Logo" class="max-h-12 max-w-full object-contain">
        </div>
    </div>

    <!-- Navegación -->
    <nav class="space-y-2 mb-8">
        <a href="{{ route('cliente.dashboard') }}"
           class="w-full flex items-center space-x-3 px-4 py-3 rounded-lg text-sm font-medium transition-colors {{ request()->routeIs('cliente.dashboard') ? 'text-white' : 'text-gray-900 hover:bg-gray-50' }}"
           style="{{ request()->routeIs('cliente.dashboard') ? 'background-color: #D88429;' : '' }}">
            <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24">
                <path d="M3 13h2v8H3zm4-8h2v16H7zm4-2h2v18h-2zm4 4h2v14h-2zm4-4h2v18h-2z"/>
            </svg>
            <span>Mis Cotizaciones</span>
        </a>

        <a href="{{ route('cliente.nueva_cotizacion') }}"
           class="w-full flex items-center space-x-3 px-4 py-3 rounded-lg text-sm font-medium transition-colors {{ request()->routeIs('cliente.nueva_cotizacion') ? 'text-white' : 'text-gray-900 hover:bg-gray-50' }}"
           style="{{ request()->routeIs('cliente.nueva_cotizacion') ? 'background-color: #D88429;' : '' }}">
            <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24">
                <path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm5 11h-4v4h-2v-4H7v-2h4V7h2v4h4v2z"/>
            </svg>
            <span>Nueva Cotización</span>
        </a>
    </nav>

    <!-- Resumen Rápido -->
    <h3 class="text-xs font-syncopate font-bold text-gray-900 mb-4 tracking-wider">RESUMEN RÁPIDO</h3>

    <div class="space-y-3">

        <!-- Cotizaciones -->
        <div class="border border-gray-200 rounded-xl p-4 bg-white">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-lg bg-blue-50 grid place-items-center flex-shrink-0">
                    <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8"
                              d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                    </svg>
                </div>
                <div>
                    <p class="text-lg font-bold text-gray-900 leading-none">{{ $totalCotizaciones }}</p>
                    <p class="text-xs text-gray-500 mt-0.5">Cotizaciones</p>
                </div>
            </div>
        </div>

        <!-- Mensajes sin leer -->
        <div class="border rounded-xl p-4 {{ $mensajesSinLeer > 0 ? 'border-orange-300 bg-orange-50' : 'border-gray-200 bg-white' }}">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-lg {{ $mensajesSinLeer > 0 ? 'bg-orange-100' : 'bg-gray-50' }} grid place-items-center flex-shrink-0 relative">
                    <svg class="w-5 h-5 {{ $mensajesSinLeer > 0 ? 'text-orange-500' : 'text-gray-400' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8"
                              d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z"/>
                    </svg>
                    @if($mensajesSinLeer > 0)
                        <span class="absolute -top-1 -right-1 w-4 h-4 bg-orange-500 rounded-full text-white text-[9px] font-bold grid place-items-center">
                            {{ $mensajesSinLeer > 9 ? '9+' : $mensajesSinLeer }}
                        </span>
                    @endif
                </div>
                <div>
                    <p class="text-lg font-bold {{ $mensajesSinLeer > 0 ? 'text-orange-700' : 'text-gray-900' }} leading-none">
                        {{ $mensajesSinLeer }}
                    </p>
                    <p class="text-xs {{ $mensajesSinLeer > 0 ? 'text-orange-600' : 'text-gray-500' }} mt-0.5">
                        {{ $mensajesSinLeer === 1 ? 'Mensaje nuevo' : 'Mensajes nuevos' }}
                    </p>
                </div>
            </div>
        </div>

        <!-- Cliente -->
        <div class="border border-gray-200 rounded-xl p-4 bg-gray-50">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-lg bg-gray-200 grid place-items-center flex-shrink-0">
                    <svg class="w-5 h-5 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8"
                              d="M5.121 17.804A13.937 13.937 0 0112 16c2.5 0 4.847.655 6.879 1.804M15 10a3 3 0 11-6 0 3 3 0 016 0z"/>
                    </svg>
                </div>
                <div class="min-w-0">
                    <p class="text-sm font-semibold text-gray-900 truncate leading-tight">{{ $nombreCliente }}</p>
                    @if($persona?->empresa)
                        <p class="text-xs text-gray-400 truncate mt-0.5">{{ $persona->empresa->nombre }}</p>
                    @endif
                </div>
            </div>
        </div>

    </div>

    <!-- Cerrar sesión -->
    <div class="mt-auto pt-6 border-t border-gray-200">
        <button type="button"
                onclick="document.getElementById('cliente-logout-form').submit();"
                class="w-full inline-flex items-center justify-center gap-2 px-4 py-3 rounded-lg text-sm font-semibold text-white transition-colors"
                style="background-color: #172A32;">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                      d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/>
            </svg>
            <span>Cerrar Sesión</span>
        </button>
        <form id="cliente-logout-form" action="{{ route('logout') }}" method="POST" class="hidden">
            @csrf
        </form>
    </div>

</aside>

<!-- Mobile Sidebar Overlay -->
<div id="sidebar-overlay" class="fixed inset-0 bg-black bg-opacity-50 z-45 lg:hidden hidden transition-opacity duration-300"></div>

<!-- Mobile Sidebar -->
<aside id="mobile-sidebar"
    class="fixed left-0 top-0 h-full w-72 bg-white shadow-xl p-6 transform -translate-x-full transition-transform duration-300 ease-in-out z-50 lg:hidden flex flex-col">
    <!-- Close Button -->
    <div class="flex items-center justify-between mb-8">
        <div class="flex items-center">
            <img src="{{ asset('logo/logo negro.png') }}" alt="Malkoni Logo" class="max-h-10 max-w-[120px] object-contain">
        </div>
        <button id="close-mobile-menu" class="p-2 rounded-md text-gray-600 hover:text-gray-900 hover:bg-gray-100 transition-colors">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
            </svg>
        </button>
    </div>

    <!-- Mobile Navigation -->
    <nav class="space-y-2 mb-8">
        <a href="{{ route('cliente.dashboard') }}"
           class="w-full flex items-center space-x-3 px-4 py-3 rounded-lg text-sm font-medium transition-colors {{ request()->routeIs('cliente.dashboard') ? 'text-white' : 'text-gray-900 hover:bg-gray-50' }}"
           style="{{ request()->routeIs('cliente.dashboard') ? 'background-color: #D88429;' : '' }}">
            <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24">
                <path d="M3 13h2v8H3zm4-8h2v16H7zm4-2h2v18h-2zm4 4h2v14h-2zm4-4h2v18h-2z"/>
            </svg>
            <span>Mis Cotizaciones</span>
        </a>

        <a href="{{ route('cliente.nueva_cotizacion') }}"
           class="w-full flex items-center space-x-3 px-4 py-3 rounded-lg text-sm font-medium transition-colors {{ request()->routeIs('cliente.nueva_cotizacion') ? 'text-white' : 'text-gray-900 hover:bg-gray-50' }}"
           style="{{ request()->routeIs('cliente.nueva_cotizacion') ? 'background-color: #D88429;' : '' }}">
            <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24">
                <path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm5 11h-4v4h-2v-4H7v-2h4V7h2v4h4v2z"/>
            </svg>
            <span>Nueva Cotización</span>
        </a>
    </nav>

    <!-- Resumen Rápido -->
    <h3 class="text-xs font-syncopate font-bold text-gray-900 mb-4 tracking-wider">RESUMEN RÁPIDO</h3>

    <div class="space-y-3">
        <!-- Cotizaciones -->
        <div class="border border-gray-200 rounded-xl p-4 bg-white">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-lg bg-blue-50 grid place-items-center flex-shrink-0">
                    <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8"
                              d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                    </svg>
                </div>
                <div>
                    <p class="text-lg font-bold text-gray-900 leading-none">{{ $totalCotizaciones }}</p>
                    <p class="text-xs text-gray-500 mt-0.5">Cotizaciones</p>
                </div>
            </div>
        </div>

        <!-- Mensajes sin leer -->
        <div class="border rounded-xl p-4 {{ $mensajesSinLeer > 0 ? 'border-orange-300 bg-orange-50' : 'border-gray-200 bg-white' }}">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-lg {{ $mensajesSinLeer > 0 ? 'bg-orange-100' : 'bg-gray-50' }} grid place-items-center flex-shrink-0 relative">
                    <svg class="w-5 h-5 {{ $mensajesSinLeer > 0 ? 'text-orange-500' : 'text-gray-400' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8"
                              d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z"/>
                    </svg>
                    @if($mensajesSinLeer > 0)
                        <span class="absolute -top-1 -right-1 w-4 h-4 bg-orange-500 rounded-full text-white text-[9px] font-bold grid place-items-center">
                            {{ $mensajesSinLeer > 9 ? '9+' : $mensajesSinLeer }}
                        </span>
                    @endif
                </div>
                <div>
                    <p class="text-lg font-bold {{ $mensajesSinLeer > 0 ? 'text-orange-700' : 'text-gray-900' }} leading-none">
                        {{ $mensajesSinLeer }}
                    </p>
                    <p class="text-xs {{ $mensajesSinLeer > 0 ? 'text-orange-600' : 'text-gray-500' }} mt-0.5">
                        {{ $mensajesSinLeer === 1 ? 'Mensaje nuevo' : 'Mensajes nuevos' }}
                    </p>
                </div>
            </div>
        </div>

        <!-- Cliente -->
        <div class="border border-gray-200 rounded-xl p-4 bg-gray-50">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-lg bg-gray-200 grid place-items-center flex-shrink-0">
                    <svg class="w-5 h-5 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8"
                              d="M5.121 17.804A13.937 13.937 0 0112 16c2.5 0 4.847.655 6.879 1.804M15 10a3 3 0 11-6 0 3 3 0 016 0z"/>
                    </svg>
                </div>
                <div class="min-w-0">
                    <p class="text-sm font-semibold text-gray-900 truncate leading-tight">{{ $nombreCliente }}</p>
                    @if($persona?->empresa)
                        <p class="text-xs text-gray-400 truncate mt-0.5">{{ $persona->empresa->nombre }}</p>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- Cerrar sesión -->
    <div class="mt-auto pt-6 border-t border-gray-200">
        <button type="button"
                onclick="document.getElementById('cliente-logout-form-mobile').submit();"
                class="w-full inline-flex items-center justify-center gap-2 px-4 py-3 rounded-lg text-sm font-semibold text-white transition-colors"
                style="background-color: #172A32;">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                      d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/>
            </svg>
            <span>Cerrar Sesión</span>
        </button>
        <form id="cliente-logout-form-mobile" action="{{ route('logout') }}" method="POST" class="hidden">
            @csrf
        </form>
    </div>
</aside>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const mobileMenuButton = document.getElementById('mobile-menu-button');
        const closeMobileMenu = document.getElementById('close-mobile-menu');
        const mobileSidebar = document.getElementById('mobile-sidebar');
        const overlay = document.getElementById('sidebar-overlay');
        
        function openMobileSidebar() {
            if (mobileSidebar && overlay) {
                mobileSidebar.classList.remove('-translate-x-full');
                overlay.classList.remove('hidden');
                document.body.style.overflow = 'hidden';
            }
        }
        
        function closeMobileSidebar() {
            if (mobileSidebar && overlay) {
                mobileSidebar.classList.add('-translate-x-full');
                overlay.classList.add('hidden');
                document.body.style.overflow = '';
            }
        }
        
        // Mobile menu button
        if (mobileMenuButton) {
            mobileMenuButton.addEventListener('click', function(e) {
                e.preventDefault();
                openMobileSidebar();
            });
        }
        
        // Close button in mobile sidebar
        if (closeMobileMenu) {
            closeMobileMenu.addEventListener('click', function(e) {
                e.preventDefault();
                closeMobileSidebar();
            });
        }
        
        // Click overlay to close
        if (overlay) {
            overlay.addEventListener('click', closeMobileSidebar);
        }
        
        // Close when clicking links in mobile sidebar
        if (mobileSidebar) {
            const mobileLinks = mobileSidebar.querySelectorAll('a');
            mobileLinks.forEach(link => {
                link.addEventListener('click', function() {
                    setTimeout(closeMobileSidebar, 100);
                });
            });
        }
        
        // Escape key to close
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') {
                closeMobileSidebar();
            }
        });
        
        // Close mobile sidebar on window resize to desktop
        window.addEventListener('resize', function() {
            if (window.innerWidth >= 1024) {
                closeMobileSidebar();
            }
        });
    });
</script>
