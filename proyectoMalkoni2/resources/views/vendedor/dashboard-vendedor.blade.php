@extends('layouts.app')

@section('content')
<div class="min-h-screen bg-background text-gray-900">
    <div class="flex">
        <!-- Include Sidebar Component -->
        @include('vendedor.components.sidebar')

        <!-- Main content -->
        <main class="flex-1 lg:ml-48 overflow-y-auto">
            <div class="p-4 lg:p-8">
                <!-- Header with Vendor Name and Avatar -->
                <div class="flex items-center justify-between mb-8">
                    <h1 class="text-3xl font-syncopate font-bold text-gray-900">
                        DASHBOARD VENDEDOR
                    </h1>
                    <div class="flex items-center space-x-3">
                        <span class="text-sm font-medium">Vendedor</span>
                        <div class="w-10 h-10 rounded-full bg-primary text-white flex items-center justify-center font-bold">
                            JC
                        </div>
                    </div>
                </div>

                <!-- Top Metrics -->
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4 lg:gap-6 mb-8">
                    <!-- Clientes Digitalizados -->
                    <div class="bg-white rounded-lg p-6 border border-gray-200">
                        <div class="flex items-start space-x-4">
                            <div class="p-2 bg-purple-100 rounded-lg">
                                <svg class="w-6 h-6 text-primary" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-2a6 6 0 0112 0v2z"></path>
                                </svg>
                            </div>
                            <div>
                                <p class="text-xs lg:text-sm text-gray-600 font-medium">
                                    Clientes Digitalizados
                                </p>
                                <p class="text-2xl font-syncopate font-bold text-gray-900 mt-1">
                                    190
                                </p>
                            </div>
                        </div>
                    </div>

                    <!-- Cotizaciones en Proceso -->
                    <div class="bg-white rounded-lg p-6 border border-gray-200">
                        <div class="flex items-start space-x-4">
                            <div class="p-2 bg-blue-100 rounded-lg">
                                <svg class="w-6 h-6 text-primary" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                                </svg>
                            </div>
                            <div>
                                <p class="text-xs lg:text-sm text-gray-600 font-medium">
                                    Cotizaciones en proceso
                                </p>
                                <p class="text-2xl font-syncopate font-bold text-gray-900 mt-1">
                                    78
                                </p>
                            </div>
                        </div>
                    </div>

                    <!-- Comisiones Este Mes -->
                    <div class="bg-white rounded-lg p-6 border border-gray-200">
                        <div class="flex items-start space-x-4">
                            <div class="p-2 bg-green-100 rounded-lg">
                                <svg class="w-6 h-6 text-primary" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                </svg>
                            </div>
                            <div>
                                <p class="text-xs lg:text-sm text-gray-600 font-medium">
                                    Comisiones este mes
                                </p>
                                <p class="text-2xl font-syncopate font-bold text-gray-900 mt-1">
                                    $34.000.000
                                </p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Charts Section -->
                <div class="grid grid-cols-1 xl:grid-cols-2 gap-8 mb-8">
                    <!-- Sales Summary Bar Chart -->
                    <div class="bg-white rounded-lg p-6 border border-gray-200">
                        <div class="flex items-center justify-between mb-4">
                            <h2 class="text-lg font-syncopate font-bold text-gray-900">
                                RESUMEN DE VENTAS
                            </h2>
                            <div class="flex gap-2">
                                <button class="px-3 py-1 text-xs font-medium bg-primary text-white rounded hover:opacity-90">
                                    7 Días
                                </button>
                                <button class="px-3 py-1 text-xs font-medium border border-gray-300 rounded hover:bg-gray-50">
                                    3 Meses
                                </button>
                                <button class="px-3 py-1 text-xs font-medium border border-gray-300 rounded hover:bg-gray-50">
                                    6 Meses
                                </button>
                                <button class="px-3 py-1 text-xs font-medium border border-gray-300 rounded hover:bg-gray-50">
                                    1 Año
                                </button>
                            </div>
                        </div>
                        <canvas id="salesChart" height="80"></canvas>
                    </div>

                    <!-- Product Sales Donut Chart -->
                    <div class="bg-white rounded-lg p-6 border border-gray-200">
                        <h2 class="text-lg font-syncopate font-bold text-gray-900 mb-4">
                            GRÁFICO DE TORTAS CON VENTA POR PRODUCTO
                        </h2>
                        <div class="flex justify-center mb-4">
                            <canvas id="productChart" height="200"></canvas>
                        </div>
                        <div class="flex justify-center flex-wrap gap-4 text-sm">
                            <div class="flex items-center gap-2">
                                <div class="w-3 h-3 rounded-full" style="background-color: #D88429;"></div>
                                <span>Puerta</span>
                            </div>
                            <div class="flex items-center gap-2">
                                <div class="w-3 h-3 rounded-full" style="background-color: #166379;"></div>
                                <span>Ventana</span>
                            </div>
                            <div class="flex items-center gap-2">
                                <div class="w-3 h-3 rounded-full" style="background-color: #16a34a;"></div>
                                <span>Mueble</span>
                            </div>
                            <div class="flex items-center gap-2">
                                <div class="w-3 h-3 rounded-full" style="background-color: #d946ef;"></div>
                                <span>Decoración</span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Tables Section -->
                <div class="grid grid-cols-1 xl:grid-cols-2 gap-8">
                    <!-- Latest Quotations -->
                    <div class="bg-white rounded-lg p-6 border border-gray-200">
                        <h2 class="text-lg font-syncopate font-bold text-gray-900 mb-4">
                            ÚLTIMAS COTIZACIONES
                        </h2>
                        <div class="overflow-x-auto">
                            <table class="w-full text-sm">
                                <thead>
                                    <tr class="bg-gray-100 border-b border-gray-200">
                                        <th class="text-left py-3 px-2 font-semibold text-gray-700">
                                            Estado
                                        </th>
                                        <th class="text-left py-3 px-2 font-semibold text-gray-700">
                                            N° cotización
                                        </th>
                                        <th class="text-left py-3 px-2 font-semibold text-gray-700">
                                            Cliente
                                        </th>
                                        <th class="text-left py-3 px-2 font-semibold text-gray-700">
                                            Monto
                                        </th>
                                        <th class="text-left py-3 px-2 font-semibold text-gray-700">
                                            Acción
                                        </th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr class="border-b border-gray-200 hover:bg-gray-50">
                                        <td class="py-3 px-2">
                                            <span class="inline-block px-2 py-1 rounded text-xs font-medium bg-blue-100 text-blue-800">
                                                En Proceso
                                            </span>
                                        </td>
                                        <td class="py-3 px-2 text-gray-900">#001</td>
                                        <td class="py-3 px-2 text-gray-900">Grupo Innova</td>
                                        <td class="py-3 px-2 text-gray-900">$12.500</td>
                                        <td class="py-3 px-2">
                                            <a href="#" class="text-primary text-xs font-medium hover:underline">
                                                Ver detalle
                                            </a>
                                        </td>
                                    </tr>
                                    <tr class="border-b border-gray-200 hover:bg-gray-50">
                                        <td class="py-3 px-2">
                                            <span class="inline-block px-2 py-1 rounded text-xs font-medium bg-green-100 text-green-800">
                                                Aprobado
                                            </span>
                                        </td>
                                        <td class="py-3 px-2 text-gray-900">#002</td>
                                        <td class="py-3 px-2 text-gray-900">Soluciones Tech</td>
                                        <td class="py-3 px-2 text-gray-900">$8.000</td>
                                        <td class="py-3 px-2">
                                            <a href="#" class="text-primary text-xs font-medium hover:underline">
                                                Ver detalle
                                            </a>
                                        </td>
                                    </tr>
                                    <tr class="border-b border-gray-200 hover:bg-gray-50">
                                        <td class="py-3 px-2">
                                            <span class="inline-block px-2 py-1 rounded text-xs font-medium bg-gray-300 text-gray-700">
                                                Rechazado
                                            </span>
                                        </td>
                                        <td class="py-3 px-2 text-gray-900">#003</td>
                                        <td class="py-3 px-2 text-gray-900">Market Connect</td>
                                        <td class="py-3 px-2 text-gray-900">$3.200</td>
                                        <td class="py-3 px-2">
                                            <a href="#" class="text-primary text-xs font-medium hover:underline">
                                                Ver detalle
                                            </a>
                                        </td>
                                    </tr>
                                    <tr class="border-b border-gray-200 hover:bg-gray-50">
                                        <td class="py-3 px-2">
                                            <span class="inline-block px-2 py-1 rounded text-xs font-medium bg-yellow-100 text-yellow-800">
                                                Pendiente
                                            </span>
                                        </td>
                                        <td class="py-3 px-2 text-gray-900">#004</td>
                                        <td class="py-3 px-2 text-gray-900">Vision Global</td>
                                        <td class="py-3 px-2 text-gray-900">$15.000</td>
                                        <td class="py-3 px-2">
                                            <a href="#" class="text-primary text-xs font-medium hover:underline">
                                                Ver detalle
                                            </a>
                                        </td>
                                    </tr>
                                    <tr class="border-b border-gray-200 hover:bg-gray-50">
                                        <td class="py-3 px-2">
                                            <span class="inline-block px-2 py-1 rounded text-xs font-medium bg-blue-100 text-blue-800">
                                                En Proceso
                                            </span>
                                        </td>
                                        <td class="py-3 px-2 text-gray-900">#005</td>
                                        <td class="py-3 px-2 text-gray-900">Data Dynamics</td>
                                        <td class="py-3 px-2 text-gray-900">$7.000</td>
                                        <td class="py-3 px-2">
                                            <a href="#" class="text-primary text-xs font-medium hover:underline">
                                                Ver detalle
                                            </a>
                                        </td>
                                    </tr>
                                    <tr class="hover:bg-gray-50">
                                        <td class="py-3 px-2">
                                            <span class="inline-block px-2 py-1 rounded text-xs font-medium bg-green-100 text-green-800">
                                                Aprobado
                                            </span>
                                        </td>
                                        <td class="py-3 px-2 text-gray-900">#006</td>
                                        <td class="py-3 px-2 text-gray-900">Future Digital</td>
                                        <td class="py-3 px-2 text-gray-900">$22.000</td>
                                        <td class="py-3 px-2">
                                            <a href="#" class="text-primary text-xs font-medium hover:underline">
                                                Ver detalle
                                            </a>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <!-- Product Ranking -->
                    <div class="bg-white rounded-lg p-6 border border-gray-200">
                        <h2 class="text-lg font-syncopate font-bold text-gray-900 mb-4">
                            RANKING DE PRODUCTO
                        </h2>
                        <div class="space-y-2">
                            <div class="flex items-center justify-between py-3 px-2 border-b border-gray-200">
                                <div class="flex items-center space-x-3">
                                    <span class="w-6 h-6 rounded-full bg-gray-200 flex items-center justify-center text-xs font-bold">1</span>
                                    <span class="text-sm">Software CRM Pro</span>
                                </div>
                                <span class="text-sm font-medium">250</span>
                            </div>
                            <div class="flex items-center justify-between py-3 px-2 border-b border-gray-200">
                                <div class="flex items-center space-x-3">
                                    <span class="w-6 h-6 rounded-full bg-gray-200 flex items-center justify-center text-xs font-bold">2</span>
                                    <span class="text-sm">Servicio Cloud Premium</span>
                                </div>
                                <span class="text-sm font-medium">180</span>
                            </div>
                            <div class="flex items-center justify-between py-3 px-2 border-b border-gray-200">
                                <div class="flex items-center space-x-3">
                                    <span class="w-6 h-6 rounded-full bg-gray-200 flex items-center justify-center text-xs font-bold">3</span>
                                    <span class="text-sm">Consultoría Estratégica</span>
                                </div>
                                <span class="text-sm font-medium">120</span>
                            </div>
                            <div class="flex items-center justify-between py-3 px-2 border-b border-gray-200">
                                <div class="flex items-center space-x-3">
                                    <span class="w-6 h-6 rounded-full bg-gray-200 flex items-center justify-center text-xs font-bold">4</span>
                                    <span class="text-sm">Solución de Seguridad</span>
                                </div>
                                <span class="text-sm font-medium">90</span>
                            </div>
                            <div class="flex items-center justify-between py-3 px-2">
                                <div class="flex items-center space-x-3">
                                    <span class="w-6 h-6 rounded-full bg-gray-200 flex items-center justify-center text-xs font-bold">5</span>
                                    <span class="text-sm">Licencia de Productividad</span>
                                </div>
                                <span class="text-sm font-medium">75</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </main>
    </div>
</div>

<!-- Chart.js Script -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    // Sales Summary Bar Chart
    const salesCtx = document.getElementById('salesChart');
    if (salesCtx) {
        new Chart(salesCtx, {
            type: 'bar',
            data: {
                labels: ['Lun', 'Mar', 'Mié', 'Jue', 'Vie', 'Sáb', 'Dom'],
                datasets: [{
                    label: 'Ventas',
                    data: [120, 140, 160, 140, 170, 180, 130],
                    backgroundColor: '#D88429',
                    borderColor: '#D88429',
                    borderWidth: 0,
                    borderRadius: 4
                }]
            },
            options: {
                indexAxis: undefined,
                responsive: true,
                maintainAspectRatio: true,
                plugins: {
                    legend: {
                        display: false
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        max: 200
                    }
                }
            }
        });
    }

    // Product Sales Donut Chart
    const productCtx = document.getElementById('productChart');
    if (productCtx) {
        new Chart(productCtx, {
            type: 'doughnut',
            data: {
                labels: ['Puerta', 'Ventana', 'Mueble', 'Decoración'],
                datasets: [{
                    data: [35, 25, 25, 15],
                    backgroundColor: [
                        '#D88429',
                        '#166379',
                        '#16a34a',
                        '#d946ef'
                    ],
                    borderColor: '#ffffff',
                    borderWidth: 2
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: true,
                plugins: {
                    legend: {
                        display: false
                    }
                }
            }
        });
    }
</script>
@endsection