<!-- Desktop Sidebar -->
<aside class="w-48 bg-white border-r border-gray-200 p-6 fixed left-0 top-0 h-screen overflow-y-auto z-30 desktop-sidebar">
    <!-- Logo -->
    <div class="mb-8">
        <div class="flex items-center justify-center">
            <img src="{{ asset('logo/logo negro.png') }}" alt="Malkoni Logo" class="h-12 w-auto">
        </div>
    </div>

    <!-- Navigation -->
    <nav class="space-y-2">
        <a href="{{ route('cliente.dashboard') }}" class="w-full flex items-center space-x-3 px-4 py-3 rounded-lg text-sm font-medium {{ request()->routeIs('cliente.dashboard') ? 'text-white' : 'text-gray-900 hover:bg-gray-50' }} transition-colors" 
           style="{{ request()->routeIs('cliente.dashboard') ? 'background-color: #D88429;' : '' }}">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
            </svg>
            <span>Dashboard</span>
        </a>

        <a href="{{ route('cliente.cotizaciones') }}" class="w-full flex items-center space-x-3 px-4 py-3 rounded-lg text-sm font-medium {{ request()->routeIs('cliente.cotizaciones') ? 'text-white' : 'text-gray-900 hover:bg-gray-50' }} transition-colors"
           style="{{ request()->routeIs('cliente.cotizaciones') ? 'background-color: #D88429;' : '' }}">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
            <span>Cotizaciones</span>
        </a>
        
        <a href="{{ route('cliente.nueva_cotizacion') }}" class="w-full flex items-center space-x-3 px-4 py-3 rounded-lg text-sm font-medium {{ request()->routeIs('cliente.nueva_cotizacion') ? 'text-white' : 'text-gray-900 hover:bg-gray-50' }} transition-colors"
           style="{{ request()->routeIs('cliente.nueva_cotizacion') ? 'background-color: #D88429;' : '' }}">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
            <span>Nueva Cotización</span>
        </a>
    </nav>
    
    <div class="mt-8 pt-4 border-t border-gray-200 space-y-3">
        <a href="{{ route('cliente.mensajes') }}" class="w-full flex items-center justify-between px-2 py-2 rounded-lg text-sm font-medium text-gray-900 hover:bg-gray-50 transition-colors">
            <div class="flex items-center space-x-3">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path></svg>
                <span>3 Mensajes</span>
            </div>
        </a>
        
        <a href="{{ route('cliente.pedidos_realizados') }}" class="w-full flex items-center justify-between px-2 py-2 rounded-lg text-sm font-medium text-gray-900 hover:bg-gray-50 transition-colors">
            <div class="flex items-center space-x-3">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path></svg>
                <span>6 Pedidos Realizados</span>
            </div>
        </a>
        
        <a href="{{ route('cliente.pedidos_sin_cotizar') }}" class="w-full flex items-center justify-between px-2 py-2 rounded-lg text-sm font-medium text-gray-900 hover:bg-gray-50 transition-colors">
            <div class="flex items-center space-x-3">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c1.657 0 3 1.343 3 3v2a3 3 0 003 3h1a2 2 0 002-2v-3.333m-1.333-2.333h-1.334a1 1 0 010-2h1.334a1 1 0 010 2zm0 0l-1.334-1.333m0 0l-1.333 1.333"></path></svg>
                <span>2 Pedidos sin Cotizar</span>
            </div>
        </a>
        
        <a href="{{ route('cliente.pedidos_en_entrega') }}" class="w-full flex items-center justify-between px-2 py-2 rounded-lg text-sm font-medium text-gray-900 hover:bg-gray-50 transition-colors">
            <div class="flex items-center space-x-3">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 13l2-2m0 0l2 2m-2-2v4"></path></svg>
                <span>3 Pedidos en Entrega</span>
            </div>
        </a>
    </div>
</aside>

<!-- Mobile Sidebar Overlay -->
<div id="sidebar-overlay" class="fixed inset-0 bg-black bg-opacity-50 z-40 lg:hidden hidden transition-opacity duration-300"></div>

<!-- Mobile Sidebar -->
<aside id="mobile-sidebar"
       class="fixed left-0 top-0 h-full w-72 bg-white shadow-xl p-6 transform -translate-x-full transition-transform duration-300 ease-in-out z-50 lg:hidden">
    <!-- Close Button -->
    <div class="flex items-center justify-between mb-8">
        <div class="flex items-center">
            <img src="{{ asset('logo/logo negro.png') }}" alt="Malkoni Logo" class="h-10 w-auto">
        </div>
        <button id="close-mobile-menu" class="p-2 rounded-md text-gray-600 hover:text-gray-900 hover:bg-gray-100 transition-colors">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
            </svg>
        </button>
    </div>

    <!-- Mobile Navigation -->
    <nav class="space-y-2">
        <a href="{{ route('cliente.dashboard') }}" class="w-full flex items-center space-x-3 px-4 py-3 rounded-lg text-sm font-medium {{ request()->routeIs('cliente.dashboard') ? 'text-white' : 'text-gray-900 hover:bg-gray-50' }} transition-colors" 
           style="{{ request()->routeIs('cliente.dashboard') ? 'background-color: #D88429;' : '' }}">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
            </svg>
            <span>Dashboard</span>
        </a>

        <a href="{{ route('cliente.cotizaciones') }}" class="w-full flex items-center space-x-3 px-4 py-3 rounded-lg text-sm font-medium {{ request()->routeIs('cliente.cotizaciones') ? 'text-white' : 'text-gray-900 hover:bg-gray-50' }} transition-colors"
           style="{{ request()->routeIs('cliente.cotizaciones') ? 'background-color: #D88429;' : '' }}">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
            <span>Cotizaciones</span>
        </a>
        
        <a href="{{ route('cliente.nueva_cotizacion') }}" class="w-full flex items-center space-x-3 px-4 py-3 rounded-lg text-sm font-medium {{ request()->routeIs('cliente.nueva_cotizacion') ? 'text-white' : 'text-gray-900 hover:bg-gray-50' }} transition-colors"
           style="{{ request()->routeIs('cliente.nueva_cotizacion') ? 'background-color: #D88429;' : '' }}">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
            <span>Nueva Cotización</span>
        </a>
    </nav>
    
    <div class="mt-8 pt-4 border-t border-gray-200 space-y-3">
        <a href="{{ route('cliente.mensajes') }}" class="w-full flex items-center justify-between px-2 py-2 rounded-lg text-sm font-medium text-gray-900 hover:bg-gray-50 transition-colors">
            <div class="flex items-center space-x-3">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path></svg>
                <span>3 Mensajes</span>
            </div>
        </a>
        
        <a href="{{ route('cliente.pedidos_realizados') }}" class="w-full flex items-center justify-between px-2 py-2 rounded-lg text-sm font-medium text-gray-900 hover:bg-gray-50 transition-colors">
            <div class="flex items-center space-x-3">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path></svg>
                <span>6 Pedidos Realizados</span>
            </div>
        </a>
        
        <a href="{{ route('cliente.pedidos_sin_cotizar') }}" class="w-full flex items-center justify-between px-2 py-2 rounded-lg text-sm font-medium text-gray-900 hover:bg-gray-50 transition-colors">
            <div class="flex items-center space-x-3">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c1.657 0 3 1.343 3 3v2a3 3 0 003 3h1a2 2 0 002-2v-3.333m-1.333-2.333h-1.334a1 1 0 010-2h1.334a1 1 0 010 2zm0 0l-1.334-1.333m0 0l-1.333 1.333"></path></svg>
                <span>2 Pedidos sin Cotizar</span>
            </div>
        </a>
        
        <a href="{{ route('cliente.pedidos_en_entrega') }}" class="w-full flex items-center justify-between px-2 py-2 rounded-lg text-sm font-medium text-gray-900 hover:bg-gray-50 transition-colors">
            <div class="flex items-center space-x-3">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 13l2-2m0 0l2 2m-2-2v4"></path></svg>
                <span>3 Pedidos en Entrega</span>
            </div>
        </a>
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