@extends('layouts.app')

@section('title', 'Portal Interno - Malkoni Hnos')

@section('content')
<div class="min-h-screen flex items-center justify-center px-4 relative overflow-hidden" style="background-color:#121212;">
    <!-- Elementos decorativos de fondo para dar profundidad visual y asemejar la estética oscura -->
    <div class="absolute -top-40 -right-40 w-[500px] h-[500px] rounded-full bg-[#D88429]/8 blur-[120px] pointer-events-none"></div>
    <div class="absolute -bottom-40 -left-40 w-[500px] h-[500px] rounded-full bg-[#166379]/8 blur-[120px] pointer-events-none"></div>

    <div class="w-full max-w-md bg-[#1c1c1e] border border-gray-800 rounded-2xl shadow-2xl overflow-hidden z-10 transition-all duration-300">
        <!-- Línea superior de acento corporativo (Color Naranja Malkoni) -->
        <div class="h-1.5 w-full bg-[#D88429]"></div>

        <div class="p-8 sm:p-10">
            <!-- Logo e Identidad (Logo Blanco para fondo oscuro) -->
            <div class="text-center mb-8">
                <img src="{{ asset('logo/logo blanco.png') }}" alt="Malkoni Hnos." class="h-16 mx-auto mb-4 object-contain">
                <h1 class="text-lg font-syncopate font-bold text-white tracking-wider">
                    PORTAL COMERCIAL
                </h1>
                <p class="text-xs text-gray-400 mt-2 font-medium">
                    Uso exclusivo para asesores de venta y supervisores de Malkoni Hnos.
                </p>
            </div>

            <!-- Alertas de error -->
            @if($errors->any())
                <div class="mb-6 rounded-xl border border-red-900/30 bg-red-950/20 p-4 flex items-start gap-3 text-sm text-red-400">
                    <svg class="w-5 h-5 text-red-500 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                    </svg>
                    <span class="font-medium">{{ $errors->first() }}</span>
                </div>
            @endif

            <!-- Formulario de Acceso -->
            <form method="POST" action="{{ route('login.attempt') }}" class="space-y-5">
                @csrf
                
                <div>
                    <label class="block text-xs font-bold uppercase tracking-wider text-gray-400 mb-2">Correo Corporativo</label>
                    <div class="relative group">
                        <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-gray-500 group-focus-within:text-[#D88429] transition-colors">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M16 12a4 4 0 10-8 0 4 4 0 008 0zm0 0v1.5a2.5 2.5 0 005 0V12a9 9 0 10-9 9m4.5-1.206a8.959 8.959 0 01-4.5 1.206"/>
                            </svg>
                        </div>
                        <input type="email" name="email" value="{{ old('email') }}" required 
                               class="w-full h-12 pl-11 pr-4 rounded-xl border border-gray-800 bg-[#252528] text-white placeholder-gray-500 text-sm focus:outline-none focus:ring-2 focus:ring-[#D88429]/20 focus:border-[#D88429] focus:bg-[#2c2c30] transition-all" 
                               placeholder="asesor@malkonihnos.com">
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-bold uppercase tracking-wider text-gray-400 mb-2">Clave de Acceso</label>
                    <div class="relative group">
                        <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-gray-500 group-focus-within:text-[#D88429] transition-colors">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                            </svg>
                        </div>
                        <input type="password" name="password" required 
                               class="w-full h-12 pl-11 pr-4 rounded-xl border border-gray-800 bg-[#252528] text-white placeholder-gray-500 text-sm focus:outline-none focus:ring-2 focus:ring-[#D88429]/20 focus:border-[#D88429] focus:bg-[#2c2c30] transition-all" 
                               placeholder="••••••••">
                    </div>
                </div>

                <div class="pt-2">
                    <button type="submit" 
                            class="w-full h-12 rounded-xl text-white font-bold text-sm bg-[#D88429] hover:bg-[#c7731f] active:scale-[0.98] transition-all duration-200 shadow-md hover:shadow-lg flex items-center justify-center gap-2">
                        <span>Ingresar al Portal</span>
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"/>
                        </svg>
                    </button>
                </div>
            </form>

            <div class="mt-8 pt-6 border-t border-gray-800/80 flex items-start gap-3">
                <svg class="w-5 h-5 text-gray-500 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                <p class="text-xs text-gray-400 leading-relaxed">
                    <strong>¿Sos cliente?</strong> Los clientes deben acceder y autenticarse desde la plataforma 
                    <a href="https://online.malkoni.com.ar/public/login.php" class="text-[#D88429] font-semibold hover:underline">Malkoni Online</a> para gestionar sus cotizaciones.
                </p>
            </div>
        </div>
    </div>
</div>
@endsection
