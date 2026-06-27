<!-- Desktop Header -->
<div class="hidden lg:flex items-center justify-between mb-8">
    <div>
        <h1 class="text-3xl font-syncopate font-bold text-gray-900">
            @yield('page-title', 'DASHBOARD')
        </h1>
    </div>
    @include('supervisor.components.user_profile')
</div>

<!-- Mobile Header Title -->
<div class="lg:hidden mb-6">
    <h1 class="text-2xl font-syncopate font-bold text-gray-900">
        @yield('page-title', 'DASHBOARD')
    </h1>
</div>