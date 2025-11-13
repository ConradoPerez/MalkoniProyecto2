<!-- Bar Chart for Quotations Over Time -->
<div class="bg-white rounded-lg p-6 border border-gray-200 shadow-sm">
    <div class="flex items-center justify-between mb-4">
        <h2 class="text-lg font-syncopate font-bold text-gray-900">
            RESUMEN DE COTIZACIONES
        </h2>
        <div class="flex gap-2">
            <button class="interval-btn px-3 py-1 text-xs font-medium bg-primary text-white rounded hover:opacity-90 transition-colors" data-interval="7dias">
                7 Días
            </button>
            <button class="interval-btn px-3 py-1 text-xs font-medium border border-gray-300 rounded hover:bg-gray-50 transition-colors" data-interval="3meses">
                3 Meses
            </button>
            <button class="interval-btn px-3 py-1 text-xs font-medium border border-gray-300 rounded hover:bg-gray-50 transition-colors" data-interval="6meses">
                6 Meses
            </button>
            <button class="interval-btn px-3 py-1 text-xs font-medium border border-gray-300 rounded hover:bg-gray-50 transition-colors" data-interval="1ano">
                1 Año
            </button>
        </div>
    </div>
    <div class="mb-4">
        <div id="chartLoading" class="text-center py-4 hidden">
            <div class="inline-block w-4 h-4 border-2 border-primary border-t-transparent rounded-full animate-spin"></div>
            <span class="ml-2 text-sm text-gray-600">Cargando datos...</span>
        </div>
        <canvas id="salesChart" height="80"></canvas>
    </div>
</div>

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const salesCtx = document.getElementById('salesChart');
        const loadingElement = document.getElementById('chartLoading');
        let salesChart = null;
        let currentInterval = '7dias';
        const vendedorId = {{ isset($vendedor) ? $vendedor->id_empleado : 1 }};

        // Función para inicializar el gráfico
        function initChart(data) {
            if (salesChart) {
                salesChart.destroy();
            }

            if (!data || data.length === 0) {
                salesCtx.style.display = 'none';
                return;
            }

            salesCtx.style.display = 'block';

            // Procesar los datos según el intervalo
            let labels = [];
            let chartData = [];

            if (currentInterval === '7dias') {
                // Últimos 7 días
                const last7Days = [];
                for (let i = 6; i >= 0; i--) {
                    const date = new Date();
                    date.setDate(date.getDate() - i);
                    last7Days.push({
                        fecha: date.toISOString().split('T')[0],
                        label: date.toLocaleDateString('es-ES', { weekday: 'short' })
                    });
                }

                labels = last7Days.map(d => d.label);
                chartData = last7Days.map(d => {
                    const found = data.find(item => item.fecha === d.fecha);
                    return found ? parseInt(found.total) : 0;
                });
            } else {
                // Para meses
                labels = data.map(item => {
                    const date = new Date(item.mes + '-01');
                    return date.toLocaleDateString('es-ES', { month: 'short', year: 'numeric' });
                });
                chartData = data.map(item => parseInt(item.total));
            }

            salesChart = new Chart(salesCtx, {
                type: 'bar',
                data: {
                    labels: labels,
                    datasets: [{
                        label: 'Cotizaciones',
                        data: chartData,
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
                            ticks: {
                                stepSize: 1
                            }
                        }
                    }
                }
            });
        }

        // Función para cargar datos del gráfico
        function loadChartData(interval) {
            loadingElement.classList.remove('hidden');
            salesCtx.style.opacity = '0.5';

            // Llamada AJAX real
            fetch(`{{ route('vendedor.api.cotizaciones.chart') }}?intervalo=${interval}&vendedor_id=${vendedorId}`, {
                method: 'GET',
                headers: {
                    'Content-Type': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || ''
                }
            })
            .then(response => response.json())
            .then(data => {
                initChart(data);
                loadingElement.classList.add('hidden');
                salesCtx.style.opacity = '1';
            })
            .catch(error => {
                console.error('Error loading chart data:', error);
                // Usar datos por defecto en caso de error
                @if(isset($cotizacionesPorTiempo))
                    const fallbackData = @json($cotizacionesPorTiempo);
                    initChart(fallbackData);
                @else
                    initChart([]);
                @endif
                loadingElement.classList.add('hidden');
                salesCtx.style.opacity = '1';
            });
        }

        // Configurar botones de intervalo
        document.querySelectorAll('.interval-btn').forEach(btn => {
            btn.addEventListener('click', function() {
                // Actualizar estado visual de botones
                document.querySelectorAll('.interval-btn').forEach(b => {
                    b.classList.remove('bg-primary', 'text-white');
                    b.classList.add('border', 'border-gray-300');
                });
                
                this.classList.add('bg-primary', 'text-white');
                this.classList.remove('border', 'border-gray-300');

                // Actualizar intervalo y cargar datos
                currentInterval = this.dataset.interval;
                loadChartData(currentInterval);
            });
        });

        // Inicializar gráfico con datos iniciales
        @if(isset($cotizacionesPorTiempo))
            initChart(@json($cotizacionesPorTiempo));
        @endif
    });
</script>
@endpush