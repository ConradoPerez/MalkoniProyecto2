@extends('layouts.app')

@section('title', 'Dashboard Supervisor - Malkoni Hnos')

@section('content')
<div class="min-h-screen text-gray-900">
    <div class="flex">
        <!-- Sidebar -->
        @include('supervisor.components.sidebar')

        <!-- Main content -->
        <main class="flex-1 overflow-y-auto ml-48">
            <div class="p-4 lg:p-8">
                <!-- Header -->
                @include('supervisor.components.header')

                <!-- Metrics -->
                @include('supervisor.components.metrics')

                <!-- Charts and Tables -->
                <div class="grid grid-cols-1 xl:grid-cols-2 gap-8 mt-8">
                    @include('supervisor.components.charts')
                    @include('supervisor.components.tables')
                </div>
            </div>
        </main>
    </div>
</div>
@endsection