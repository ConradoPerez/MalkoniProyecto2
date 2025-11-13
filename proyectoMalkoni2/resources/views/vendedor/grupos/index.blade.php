@extends('layouts.app')

@section('content')
<div class="min-h-screen bg-gray-50 text-gray-900">
    <div class="flex">
        {{-- Sidebar --}}
        @include('vendedor.components.sidebar')

        {{-- Contenido principal --}}
        <main class="flex-1 overflow-y-auto ml-48">
            <div class="p-4 lg:p-8">

                {{-- Título principal --}}
                <div class="flex items-center justify-between mb-8">
                    <h1 class="text-2xl lg:text-3xl font-bold tracking-tight">Grupos de Clientes</h1>

                    <div class="hidden md:flex items-center gap-3 bg-white border border-gray-200 rounded-xl px-3 py-2 shadow-sm">
                        <div class="w-8 h-8 rounded-full bg-gray-200 grid place-items-center">
                            <svg class="w-5 h-5 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                      d="M5.121 17.804A13.937 13.937 0 0112 15c2.5 0 4.847.655 6.879 1.804M15 11a3 3 0 11-6 0 3 3 0 016 0z"/>
                            </svg>
                        </div>
                        <div class="text-sm">
                            <div class="font-semibold">Vendedor</div>
                            <div class="text-gray-500">Cuenta activa</div>
                        </div>
                    </div>
                </div>

                {{-- Grupo: Interior --}}
                <section class="bg-white border border-gray-200 rounded-xl mb-8 p-5">
                    <div class="flex items-center justify-between mb-4">
                        <h2 class="text-lg font-semibold">Grupo: Interior</h2>
                        <button class="w-8 h-8 flex items-center justify-center rounded-lg border border-gray-300 bg-white hover:bg-gray-100 text-gray-700">
                            <span class="text-xl font-bold">+</span>
                        </button>
                    </div>

                    <div class="overflow-x-auto">
                        <table class="w-full border-collapse">
                            <thead class="bg-gray-100">
                                <tr class="text-left text-sm font-semibold text-gray-700">
                                    <th class="px-4 py-3 w-1/3">CUIT</th>
                                    <th class="px-4 py-3 w-1/3">Razón Social</th>
                                    <th class="px-4 py-3 w-1/3">Usuario</th>
                                </tr>
                            </thead>
                            <tbody class="text-sm">
                                <tr class="border-t border-gray-200 hover:bg-gray-50">
                                    <td class="px-4 py-3">20-44896243-4</td>
                                    <td class="px-4 py-3">Inditex S.A.</td>
                                    <td class="px-4 py-3">santiago1234</td>
                                </tr>
                                <tr class="border-t border-gray-200 hover:bg-gray-50">
                                    <td class="px-4 py-3">20-43896248-4</td>
                                    <td class="px-4 py-3">Ripoldi S.R.L.</td>
                                    <td class="px-4 py-3">pedro4485</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </section>

                {{-- Grupo: Capital --}}
                <section class="bg-white border border-gray-200 rounded-xl mb-8 p-5">
                    <div class="flex items-center justify-between mb-4">
                        <h2 class="text-lg font-semibold">Grupo: Capital</h2>
                        <button class="w-8 h-8 flex items-center justify-center rounded-lg border border-gray-300 bg-white hover:bg-gray-100 text-gray-700">
                            <span class="text-xl font-bold">+</span>
                        </button>
                    </div>

                    <div class="overflow-x-auto">
                        <table class="w-full border-collapse">
                            <thead class="bg-gray-100">
                                <tr class="text-left text-sm font-semibold text-gray-700">
                                    <th class="px-4 py-3 w-1/3">CUIT</th>
                                    <th class="px-4 py-3 w-1/3">Razón Social</th>
                                    <th class="px-4 py-3 w-1/3">Usuario</th>
                                </tr>
                            </thead>
                            <tbody class="text-sm">
                                <tr class="border-t border-gray-200 hover:bg-gray-50">
                                    <td class="px-4 py-3">20-45675234-6</td>
                                    <td class="px-4 py-3">Productos Ramos S.A.S.</td>
                                    <td class="px-4 py-3">gustavo3984</td>
                                </tr>
                                <tr class="border-t border-gray-200 hover:bg-gray-50">
                                    <td class="px-4 py-3">20-38954231-3</td>
                                    <td class="px-4 py-3">Arcos Dorados C.A.</td>
                                    <td class="px-4 py-3">nicolai230</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </section>

                {{-- Botón Crear Nuevo Grupo --}}
                <div class="flex justify-end">
                    <button class="px-6 py-2 rounded-lg text-white font-semibold shadow-sm"
                            style="background-color:#D88429;">
                        Crear Nuevo Grupo
                    </button>
                </div>

            </div>
        </main>
    </div>
</div>
@endsection
