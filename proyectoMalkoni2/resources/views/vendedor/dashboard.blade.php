@extends('layouts.app')

@section('title', 'Dashboard Vendedor - Malkoni Hnos')

@section('content')
<div class="min-h-screen text-gray-900">
    <!-- Sidebar -->
    @include('vendedor.components.sidebar')

    <!-- Main content -->
    <main class="lg:ml-56">
        <!-- Mobile Header -->
        <div class="lg:hidden bg-white border-b border-gray-200 p-4 sticky top-0 z-10">
            <div class="flex items-center justify-between">
                <button id="mobile-menu-button" class="p-2 rounded-md text-gray-600 hover:text-gray-900 hover:bg-gray-100">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"></path>
                    </svg>
                </button>
                <div class="flex items-center">
                    <img src="{{ asset('logo/logo negro.png') }}" alt="Malkoni Logo" class="h-8 w-auto">
                </div>
                <div class="flex items-center space-x-2">
                    <span class="text-xs font-medium text-gray-900">Vendedor</span>
                    <div class="w-8 h-8 rounded-full bg-gray-300 flex items-center justify-center">
                        <svg class="w-4 h-4 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                                  d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                        </svg>
                    </div>
                </div>
            </div>
        </div>

        <div class="p-4 lg:p-8">
                <!-- Header -->
                @include('vendedor.components.header')

                <!-- Metrics -->
                @include('vendedor.components.metrics')

                <!-- Charts Section -->
                <div class="grid grid-cols-1 xl:grid-cols-2 gap-8 mt-8">
                    <!-- Pie Chart (Left) -->
                    @include('vendedor.components.pie-chart')
                    
                    <!-- Bar Chart (Right) -->
                    @include('vendedor.components.bar-chart')
                </div>

                <!-- Tables Section -->
                <div class="mt-8">
                    @include('vendedor.components.tables')
                </div>
            </div>
        </main>
    </div>
</div>

<!-- Chart.js Script -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
@endsection