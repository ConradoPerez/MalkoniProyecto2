<!-- Sidebar -->
<aside class="hidden lg:block w-48 bg-white border-r border-gray-200 p-6 fixed left-0 top-0 h-screen overflow-y-auto">
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

<!-- Mobile sidebar toggle button (for future mobile implementation) -->
<div class="lg:hidden flex-1 ml-48"></div>