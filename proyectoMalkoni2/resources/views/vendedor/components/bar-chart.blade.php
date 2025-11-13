<!-- Bar Chart for Quotations Over Time -->
<div class="bg-white rounded-xl p-4 sm:p-6 border border-gray-200 shadow-lg hover:shadow-xl transition-shadow duration-300">
    <div class="flex flex-col sm:flex-row sm:items-center justify-between mb-4 sm:mb-6 gap-4">
        <h2 class="text-lg sm:text-xl font-syncopate font-bold text-gray-900 tracking-wide">
            RESUMEN DE COTIZACIONES
        </h2>
        <div class="flex flex-wrap gap-1 bg-gray-100 rounded-lg p-1">
            <button class="interval-btn px-2 sm:px-4 py-2 text-xs font-semibold bg-gradient-to-r from-amber-500 to-orange-600 text-white rounded-md shadow-sm hover:from-amber-600 hover:to-orange-700 transition-all duration-200" data-interval="7dias">
                7 Días
            </button>
            <button class="interval-btn px-2 sm:px-4 py-2 text-xs font-semibold bg-transparent text-gray-600 rounded-md hover:bg-white hover:text-gray-800 transition-all duration-200" data-interval="3meses">
                3 Meses
            </button>
            <button class="interval-btn px-2 sm:px-4 py-2 text-xs font-semibold bg-transparent text-gray-600 rounded-md hover:bg-white hover:text-gray-800 transition-all duration-200" data-interval="6meses">
                6 Meses
            </button>
            <button class="interval-btn px-2 sm:px-4 py-2 text-xs font-semibold bg-transparent text-gray-600 rounded-md hover:bg-white hover:text-gray-800 transition-all duration-200" data-interval="1ano">
                1 Año
            </button>
        </div>
    </div>
    <div class="mb-4">
        <div id="chartLoading" class="text-center py-4 hidden">
            <div class="inline-block w-4 h-4 border-2 border-primary border-t-transparent rounded-full animate-spin"></div>
            <span class="ml-2 text-sm text-gray-600">Cargando datos...</span>
        </div>
        <div id="chartContainer" class="relative w-full bg-gradient-to-b from-gray-50 to-gray-100 rounded-lg p-2 sm:p-3" 
             style="height: clamp(250px, 40vh, 500px);">
            <canvas id="salesChart" class="w-full h-full"></canvas>
        </div>
    </div>
</div>

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const salesCtx = document.getElementById('salesChart');
        const chartContainer = document.getElementById('chartContainer');
        const loadingElement = document.getElementById('chartLoading');
        let salesChart = null;
        let currentInterval = '7dias';
        let resizeTimeout = null;
        const empleadoId = {{ isset($vendedor) ? $vendedor->id_empleado : 1 }};

        // Función para obtener configuración responsive
        function getResponsiveConfig() {
            const width = window.innerWidth;
            const containerWidth = chartContainer.offsetWidth;
            const containerHeight = chartContainer.offsetHeight;
            
            return {
                fontSize: width < 640 ? 10 : width < 1024 ? 11 : 12,
                padding: width < 640 ? 3 : width < 1024 ? 5 : 8,
                maxBarThickness: Math.min(Math.max(containerWidth / 15, 30), 80),
                categoryPercentage: width < 640 ? 0.9 : 0.8,
                barPercentage: width < 640 ? 0.8 : 0.75
            };
        }

        // Función para crear gradiente dinámico
        function createDynamicGradient() {
            const containerHeight = chartContainer.offsetHeight;
            const gradient = salesCtx.getContext('2d').createLinearGradient(0, 0, 0, containerHeight - 20);
            gradient.addColorStop(0, '#F59E0B');
            gradient.addColorStop(1, '#D97706');
            return gradient;
        }

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

            // Obtener configuración responsive
            const config = getResponsiveConfig();

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
                    const total = found ? parseInt(found.total) : 0;
                    return total;
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
                        backgroundColor: createDynamicGradient(),
                        borderColor: '#B45309',
                        borderWidth: 1,
                        borderRadius: {
                            topLeft: 6,
                            topRight: 6,
                            bottomLeft: 0,
                            bottomRight: 0
                        },
                        borderSkipped: 'bottom',
                        maxBarThickness: config.maxBarThickness,
                        minBarLength: 2
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    animation: {
                        duration: 1200,
                        easing: 'easeOutQuart'
                    },
                    interaction: {
                        intersect: false,
                        mode: 'index'
                    },
                    plugins: {
                        legend: {
                            display: false
                        },
                        tooltip: {
                            backgroundColor: 'rgba(0, 0, 0, 0.8)',
                            titleColor: '#fff',
                            bodyColor: '#fff',
                            cornerRadius: 8,
                            padding: 12,
                            displayColors: false,
                            titleFont: {
                                size: config.fontSize + 1
                            },
                            bodyFont: {
                                size: config.fontSize
                            },
                            callbacks: {
                                label: function(context) {
                                    return `Cotizaciones: ${context.parsed.y}`;
                                }
                            }
                        }
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                            grace: '2%',
                            ticks: {
                                stepSize: 1,
                                font: {
                                    size: config.fontSize,
                                    family: 'Inter, system-ui, sans-serif',
                                    weight: '500'
                                },
                                color: '#6B7280',
                                padding: config.padding
                            },
                            grid: {
                                color: '#F3F4F6',
                                lineWidth: 1,
                                drawBorder: false
                            },
                            border: {
                                display: false
                            }
                        },
                        x: {
                            categoryPercentage: config.categoryPercentage,
                            barPercentage: config.barPercentage,
                            ticks: {
                                font: {
                                    size: config.fontSize,
                                    family: 'Inter, system-ui, sans-serif',
                                    weight: '600'
                                },
                                color: '#374151',
                                padding: config.padding,
                                maxRotation: window.innerWidth < 640 ? 45 : 0,
                                minRotation: 0
                            },
                            grid: {
                                display: false
                            },
                            border: {
                                display: false
                            }
                        }
                    },
                    layout: {
                        padding: {
                            top: config.padding * 2,
                            bottom: config.padding,
                            left: config.padding,
                            right: config.padding
                        }
                    },
                    elements: {
                        bar: {
                            backgroundColor: function(context) {
                                if (context.hovered) {
                                    return '#F59E0B';
                                }
                                return context.element.options.backgroundColor;
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
            fetch(`{{ route('vendedor.api.cotizaciones.chart') }}?intervalo=${interval}&empleado_id=${empleadoId}`, {
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
                    b.classList.remove('bg-gradient-to-r', 'from-amber-500', 'to-orange-600', 'text-white', 'shadow-sm', 'from-amber-600', 'to-orange-700');
                    b.classList.add('bg-transparent', 'text-gray-600');
                });
                
                this.classList.remove('bg-transparent', 'text-gray-600');
                this.classList.add('bg-gradient-to-r', 'from-amber-500', 'to-orange-600', 'text-white', 'shadow-sm');

                // Actualizar intervalo y cargar datos
                currentInterval = this.dataset.interval;
                loadChartData(currentInterval);
            });
        });

        // Manejar redimensionamiento de ventana
        function handleResize() {
            if (resizeTimeout) {
                clearTimeout(resizeTimeout);
            }
            
            resizeTimeout = setTimeout(() => {
                if (salesChart && !salesChart.destroyed) {
                    // Obtener los datos actuales antes de destruir
                    const currentData = salesChart.data;
                    
                    // Recrear el gráfico con nueva configuración responsive
                    salesChart.destroy();
                    
                    // Simular los datos actuales para recrear el gráfico
                    const mockData = currentData.labels.map((label, index) => ({
                        fecha: new Date().toISOString().split('T')[0],
                        total: currentData.datasets[0].data[index] || 0
                    }));
                    
                    initChart(mockData);
                }
            }, 250);
        }

        // Agregar listener para redimensionamiento
        window.addEventListener('resize', handleResize);
        
        // Cleanup al salir de la página
        window.addEventListener('beforeunload', () => {
            if (resizeTimeout) {
                clearTimeout(resizeTimeout);
            }
            window.removeEventListener('resize', handleResize);
        });

        // Inicializar gráfico con datos iniciales
        @if(isset($cotizacionesPorTiempo))
            initChart(@json($cotizacionesPorTiempo));
        @endif
    });
</script>
@endpush