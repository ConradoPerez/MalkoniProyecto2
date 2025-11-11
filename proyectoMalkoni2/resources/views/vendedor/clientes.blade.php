@extends('layouts.app')

@section('content')
<div class="min-h-screen bg-gray-50 text-gray-900">
    <div class="flex">
        {{-- Sidebar --}}
        @include('vendedor.components.sidebar')

        {{-- Contenido principal --}}
        <main class="flex-1 overflow-y-auto ml-48">
            <div class="p-4 lg:p-8">

                {{-- Topbar --}}
                <div class="flex items-center justify-between mb-8">
                    <h1 class="text-2xl lg:text-3xl font-bold tracking-tight">Clientes</h1>

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

                {{-- Buscar Clientes --}}
                <section class="bg-white border border-gray-200 rounded-xl p-5 lg:p-6 mb-6">
                    <h2 class="text-base font-semibold mb-4">Buscar Clientes</h2>

                    {{-- 3 campos + botón en la misma fila (ocupan su box) --}}
                    <form method="GET" action="{{ route('vendedor.clientes.index') }}"
                          class="grid grid-cols-1 md:grid-cols-4 gap-4 md:[&>*]:w-full">

                        <div class="flex flex-col gap-1">
                            <label class="text-sm text-gray-600">Por N° de pedido</label>
                            <input type="text" name="pedido" value="{{ request('pedido') }}"
                                   class="h-10 rounded-lg border border-gray-300 px-3 outline-none focus:ring-2 focus:ring-[#D88429]/20 focus:border-[#D88429]" />
                        </div>

                        <div class="flex flex-col gap-1">
                            <label class="text-sm text-gray-600">Por Nombre</label>
                            <input type="text" name="nombre" value="{{ request('nombre') }}"
                                   class="h-10 rounded-lg border border-gray-300 px-3 outline-none focus:ring-2 focus:ring-[#D88429]/20 focus:border-[#D88429]" />
                        </div>

                        <div class="flex flex-col gap-1">
                            <label class="text-sm text-gray-600">Por DNI/CUIT</label>
                            <input type="text" name="doc" value="{{ request('doc') }}"
                                   class="h-10 rounded-lg border border-gray-300 px-3 outline-none focus:ring-2 focus:ring-[#D88429]/20 focus:border-[#D88429]" />
                        </div>

                        <div class="flex flex-col justify-end">
                            <button type="submit"
                                    class="h-10 w-full inline-flex items-center justify-center rounded-lg text-white font-semibold transition"
                                    style="background-color:#D88429;">
                                Buscar
                            </button>
                        </div>
                    </form>
                </section>

                {{-- Caja de tabla de clientes --}}
                <section class="bg-white border border-gray-200 rounded-xl">
                    <div class="px-5 pt-5">
                        <h3 class="text-base font-semibold">Clientes</h3>
                    </div>

                    <div class="overflow-x-auto p-5 pt-3">
                        <table class="w-full border-collapse">
                            <thead class="bg-gray-100">
                                <tr class="text-left text-sm font-semibold text-gray-700">
                                    <th class="px-4 py-3">Cliente</th>
                                    <th class="px-4 py-3">CUIT/DNI</th>
                                    <th class="px-4 py-3">Cantidad de cotizaciones</th>
                                    <th class="px-4 py-3 text-center">Acciones</th>
                                </tr>
                            </thead>
                            <tbody class="text-sm">
                                {{-- Ejemplos. Reemplazá por tu @foreach ($clientes as $c) --}}
                                @php
                                  $rows = [
                                    ['nombre' => 'Juan Pérez', 'doc' => '11222333', 'count' => 2],
                                    ['nombre' => 'Juan Pérez', 'doc' => '11222333', 'count' => 2],
                                    ['nombre' => 'Juan Pérez', 'doc' => '11222333', 'count' => 1],
                                    ['nombre' => 'Juan Pérez', 'doc' => '11222333', 'count' => 4],
                                    ['nombre' => 'Juan Pérez', 'doc' => '11222333', 'count' => 1],
                                  ];
                                @endphp

                                @foreach($rows as $r)
                                <tr class="border-t border-gray-200 hover:bg-gray-50">
                                    <td class="px-4 py-3">{{ $r['nombre'] }}</td>
                                    <td class="px-4 py-3">{{ $r['doc'] }}</td>
                                    <td class="px-4 py-3 ">{{ $r['count'] }}</td>

                                    <td class="px-4 py-3">
                                        <div class="flex items-center justify-center gap-2">
                                            {{-- Acción: Ver cotizaciones del cliente --}}
                                            <a href="#"
                                               class="inline-flex items-center justify-center w-10 h-10 rounded-lg border border-gray-300 bg-white hover:bg-gray-100"
                                               title="Ver cotizaciones">
                                                {{-- Icono clipboard --}}
                                                <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 text-gray-700" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8"
                                                          d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                                                </svg>
                                            </a>

                                            {{-- Acción: Ficha del cliente --}}
                                            <a href="#"
                                               class="inline-flex items-center justify-center w-10 h-10 rounded-lg border border-gray-300 bg-white hover:bg-gray-100"
                                               title="Ver ficha">
                                                {{-- Icono id-card (user + lines) --}}
                                                <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 text-gray-700" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8"
                                                          d="M3 7a2 2 0 012-2h14a2 2 0 012 2v10a2 2 0 01-2 2H5a2 2 0 01-2-2V7z" />
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8"
                                                          d="M9 12a2 2 0 100-4 2 2 0 000 4zM7 16a4 4 0 118 0" />
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8"
                                                          d="M15 9h4M15 12h4M15 15h4" />
                                                </svg>
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                                @endforeach
                                {{-- @endforelse si usás colección real --}}
                            </tbody>
                        </table>
                    </div>
                </section>

            </div>
        </main>
    </div>
</div>
@endsection
