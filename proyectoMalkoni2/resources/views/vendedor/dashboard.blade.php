@extends('layouts.app')

@section('content')
<div class="min-h-screen bg-background text-gray-900">
    <div class="flex">
        <!-- Include Sidebar Component -->
        @include('vendedor.components.sidebar')

        <!-- Main content -->
        <main class="flex-1 overflow-y-auto ml-56">
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