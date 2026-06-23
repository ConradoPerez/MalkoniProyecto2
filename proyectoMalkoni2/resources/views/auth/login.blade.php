@extends('layouts.app')

@section('title', 'Portal Interno')

@section('content')
<div class="min-h-screen flex items-center justify-center px-4" style="background-color:#e1dfd9;">
    <div class="w-full max-w-md bg-white border border-gray-200 rounded-2xl shadow-xl p-8">
        <div class="text-center mb-6">
            <img src="{{ asset('logo/logo negro.png') }}" alt="Malkoni" class="h-14 mx-auto mb-3">
            <h1 class="text-2xl font-syncopate font-bold text-gray-900">Portal de Acceso Interno</h1>
            <p class="text-sm text-gray-500 mt-1">Uso exclusivo para asesores comerciales y supervisores de Malkoni Hnos.</p>
        </div>

        @if($errors->any())
            <div class="mb-4 rounded-lg border border-red-200 bg-red-50 p-3 text-sm text-red-700">
                {{ $errors->first() }}
            </div>
        @endif

        <form method="POST" action="{{ route('login.attempt') }}" class="space-y-4">
            @csrf
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Correo corporativo</label>
                <input type="email" name="email" value="{{ old('email') }}" required class="w-full h-11 rounded-lg border border-gray-300 px-3 focus:outline-none focus:ring-2 focus:ring-[#D88429]/20 focus:border-[#D88429]" placeholder="asesor@malkonihnos.com">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Clave interna</label>
                <input type="password" name="password" required class="w-full h-11 rounded-lg border border-gray-300 px-3 focus:outline-none focus:ring-2 focus:ring-[#D88429]/20 focus:border-[#D88429]" placeholder="Ingresa tu clave interna">
            </div>
            <button type="submit" class="w-full h-11 rounded-lg text-white font-semibold" style="background-color:#D88429;">Ingresar</button>
        </form>

        <div class="mt-6 pt-4 border-t border-gray-100 text-center text-sm text-gray-600">
            Los clientes deben ingresar desde Malkoni Online para acceder a sus cotizaciones.
        </div>
    </div>
</div>
@endsection
