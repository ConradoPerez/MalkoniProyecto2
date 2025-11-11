@extends('layouts.app')

@section('content')
<div class="min-h-screen bg-gray-50 text-gray-900">
    <div class="flex">
<<<<<<< HEAD
        {{-- Sidebar fijo --}}
        @include('vendedor.components.sidebar')

        {{-- Contenido principal --}}
        <main class="flex-1 overflow-y-auto ml-48">
            <div class="p-4 lg:p-8">

                {{-- Topbar --}}
                <div class="flex items-center justify-between mb-8">
                    <h1 class="text-2xl lg:text-3xl font-bold tracking-tight">Cotizaciones</h1>

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

                {{-- Buscar --}}
                <section class="bg-white border border-gray-200 rounded-xl p-5 lg:p-6 mb-6">
                    <h2 class="text-base font-semibold mb-4">Buscar Cotizaciones</h2>

                    {{-- 3 campos + botón en la misma fila en md+ --}}
                    <form method="GET" action="{{ route('vendedor.cotizaciones.index') }}" class="grid grid-cols-1 md:grid-cols-4 gap-4 md:[&>*]:w-full">
                        <div class="flex flex-col gap-1">
                            <label class="text-sm text-gray-600">Por Número de pedido</label>
                            <input type="text" name="nropedido" value="{{ request('nropedido') }}"
                                   class="h-10 rounded-lg border border-gray-300 px-3 outline-none focus:ring-2 focus:ring-[#D88429]/20 focus:border-[#D88429]" />
                        </div>

                        <div class="flex flex-col gap-1">
                            <label class="text-sm text-gray-600">Por nombre del Cliente</label>
                            <input type="text" name="cliente" value="{{ request('cliente') }}"
                                   class="h-10 rounded-lg border border-gray-300 px-3 outline-none focus:ring-2 focus:ring-[#D88429]/20 focus:border-[#D88429]" />
                        </div>

                        <div class="flex flex-col gap-1">
                            <label class="text-sm text-gray-600">Por DNI/CUIT</label>
                            <input type="text" name="doc" value="{{ request('doc') }}"
                                   class="h-10 rounded-lg border border-gray-300 px-3 outline-none focus:ring-2 focus:ring-[#D88429]/20 focus:border-[#D88429]" />
                        </div>

                        {{-- Botón Buscar - tamaño ajustado, no estirado --}}
                        <div class="flex flex-col justify-end">
                            <button type="submit"
                                    class="h-10 w-full inline-flex items-center justify-center rounded-lg text-white font-semibold transition"
                                    style="background-color:#D88429;">
                                Buscar
                            </button>
                        </div>

                    </form>
                </section>

                {{-- Contador --}}
                <p class="text-sm text-gray-600 mb-3">
                    {{ $total ?? 181 }} Cotizaciones en total
                </p>

                {{-- Tabla --}}
                <section class="bg-white border border-gray-200 rounded-xl">
                    <div class="overflow-x-auto">
                        <table class="w-full border-collapse">
                            <thead class="bg-gray-100">
                                <tr class="text-left text-sm font-semibold text-gray-700">
                                    <th class="px-4 py-3 w-24">Estado</th>
                                    <th class="px-4 py-3">N° cotización</th>
                                    <th class="px-4 py-3">Cliente</th>
                                    <th class="px-4 py-3">Monto</th>
                                </tr>
                            </thead>
                            <tbody class="text-sm">
                                <tr class="border-t border-gray-200 hover:bg-gray-50">
                                    <td class="px-4 py-3">
                                        <span class="inline-block w-5 h-5 rounded-full" title="Cotizado"
                                              style="background-color:#54B66B;"></span>
                                    </td>
                                    <td class="px-4 py-3 font-semibold">1001</td>
                                    <td class="px-4 py-3">juan</td>
                                    <td class="px-4 py-3 text-gray-800">$100.000,00</td>
                                </tr>
                                <tr class="border-t border-gray-200 hover:bg-gray-50">
                                    <td class="px-4 py-3">
                                        <span class="inline-block w-5 h-5 rounded-full" title="En entrega"
                                              style="background-color:#3F5FFF;"></span>
                                    </td>
                                    <td class="px-4 py-3 font-semibold">1002</td>
                                    <td class="px-4 py-3">esteban</td>
                                    <td class="px-4 py-3 text-gray-800">$200.000,00</td>
                                </tr>
                                <tr class="border-t border-gray-200 hover:bg-gray-50">
                                    <td class="px-4 py-3">
                                        <span class="inline-block w-5 h-5 rounded-full" title="Cotizado"
                                              style="background-color:#54B66B;"></span>
                                    </td>
                                    <td class="px-4 py-3 font-semibold">1003</td>
                                    <td class="px-4 py-3">lucas</td>
                                    <td class="px-4 py-3 text-gray-800">$150.000,00</td>
                                </tr>
                                <tr class="border-t border-gray-200 hover:bg-gray-50">
                                    <td class="px-4 py-3">
                                        <span class="inline-block w-5 h-5 rounded-full" title="Abierto"
                                              style="background-color:#F5EA5A;"></span>
                                    </td>
                                    <td class="px-4 py-3 font-semibold">1004</td>
                                    <td class="px-4 py-3">martín</td>
                                    <td class="px-4 py-3 text-gray-800">$50.000,00</td>
                                </tr>
                                <tr class="border-t border-gray-200 hover:bg-gray-50">
                                    <td class="px-4 py-3">
                                        <span class="inline-block w-5 h-5 rounded-full" title="Nuevo"
                                              style="background-color:#C56C39;"></span>
                                    </td>
                                    <td class="px-4 py-3 font-semibold">1005</td>
                                    <td class="px-4 py-3">pedro</td>
                                    <td class="px-4 py-3 text-gray-800">$80.000,00</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </section>

                {{-- Leyenda de estados centrada --}}
                <div class="flex flex-wrap justify-center items-center gap-6 mt-6">
                    <div class="flex items-center gap-2">
                        <span class="inline-block w-4 h-4 rounded-full" style="background-color:#C56C39;"></span>
                        <span class="text-sm">Nuevo</span>
                    </div>
                    <div class="flex items-center gap-2">
                        <span class="inline-block w-4 h-4 rounded-full" style="background-color:#F5EA5A;"></span>
                        <span class="text-sm">Abierto</span>
                    </div>
                    <div class="flex items-center gap-2">
                        <span class="inline-block w-4 h-4 rounded-full" style="background-color:#54B66B;"></span>
                        <span class="text-sm">Cotizado</span>
                    </div>
                    <div class="flex items-center gap-2">
                        <span class="inline-block w-4 h-4 rounded-full" style="background-color:#3F5FFF;"></span>
                        <span class="text-sm">En entrega</span>
                    </div>
                </div>

            </div>
        </main>
    </div>
</div>
@endsection
