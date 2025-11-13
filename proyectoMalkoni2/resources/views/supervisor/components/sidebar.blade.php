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
        <!-- Dashboard -->
        <a href="{{ route('dashboard') }}" class="w-full flex items-center space-x-3 px-4 py-3 rounded-lg text-sm font-medium {{ request()->routeIs('dashboard') ? 'text-white' : 'text-gray-900 hover:bg-gray-50' }} transition-colors" 
           style="{{ request()->routeIs('dashboard') ? 'background-color: #D88429;' : '' }}">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
            </svg>
            <span>Dashboard</span>
        </a>

        <!-- Vendedores -->
        <a href="{{ route('vendedor.index') }}" class="w-full flex items-center space-x-3 px-4 py-3 rounded-lg text-sm font-medium {{ request()->routeIs('vendedores.*') ? 'text-white' : 'text-gray-900 hover:bg-gray-50' }} transition-colors"
           style="{{ request()->routeIs('vendedores.*') ? 'background-color: #D88429;' : '' }}">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-2a6 6 0 0112 0v2z"></path>
            </svg>
            <span>Vendedores</span>
        </a>

        <!-- Productos -->
        <a href="{{ route('productos.index') }}" class="w-full flex items-center space-x-3 px-4 py-3 rounded-lg text-sm font-medium {{ request()->routeIs('productos.*') ? 'text-white' : 'text-gray-900 hover:bg-gray-50' }} transition-colors"
           style="{{ request()->routeIs('productos.*') ? 'background-color: #D88429;' : '' }}">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m0 0v10l8 4"></path>
            </svg>
            <span>Productos</span>
        </a>
    </nav>
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
        <!-- Dashboard -->
        <a href="{{ route('dashboard') }}" class="w-full flex items-center space-x-3 px-4 py-3 rounded-lg text-sm font-medium {{ request()->routeIs('dashboard') ? 'text-white' : 'text-gray-900 hover:bg-gray-50' }} transition-colors" 
           style="{{ request()->routeIs('dashboard') ? 'background-color: #D88429;' : '' }}">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
            </svg>
            <span>Dashboard</span>
        </a>

        <!-- Vendedores -->
        <a href="{{ route('vendedor.index') }}" class="w-full flex items-center space-x-3 px-4 py-3 rounded-lg text-sm font-medium {{ request()->routeIs('vendedores.*') ? 'text-white' : 'text-gray-900 hover:bg-gray-50' }} transition-colors"
           style="{{ request()->routeIs('vendedores.*') ? 'background-color: #D88429;' : '' }}">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-2a6 6 0 0112 0v2z"></path>
            </svg>
            <span>Vendedores</span>
        </a>

        <!-- Productos -->
        <a href="{{ route('productos.index') }}" class="w-full flex items-center space-x-3 px-4 py-3 rounded-lg text-sm font-medium {{ request()->routeIs('productos.*') ? 'text-white' : 'text-gray-900 hover:bg-gray-50' }} transition-colors"
           style="{{ request()->routeIs('productos.*') ? 'background-color: #D88429;' : '' }}">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m0 0v10l8 4"></path>
            </svg>
            <span>Productos</span>
        </a>
    </nav>
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