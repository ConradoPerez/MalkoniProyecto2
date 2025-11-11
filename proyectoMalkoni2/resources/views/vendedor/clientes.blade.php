@extends('layouts.app')

@section('content')
<div class="min-h-screen bg-background text-gray-900">
    <div class="flex">
        <!-- Include Sidebar Component -->
        @include('vendedor.components.sidebar')

        <!-- Main content -->
        <main class="flex-1 lg:ml-48 overflow-y-auto">
            <div class="p-4 lg:p-8">
                <div class="flex items-center justify-between mb-8">
                    <h1 class="text-3xl font-syncopate font-bold text-gray-900">
                        CLIENTES
                    </h1>
                </div>

                <div class="bg-white rounded-lg p-6 border border-gray-200">
                    <p class="text-gray-600">Vista de clientes en desarrollo...</p>
                </div>
            </div>
        </main>
    </div>
</div>
@endsection