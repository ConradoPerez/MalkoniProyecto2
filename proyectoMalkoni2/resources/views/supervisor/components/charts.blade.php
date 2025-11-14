<!-- Charts Section -->
<div class="bg-white rounded-lg p-6 border border-gray-200 shadow-sm">
    <h2 class="text-lg font-syncopate font-bold text-gray-900 mb-6">
        COTIZACIONES POR VENDEDOR
    </h2>
    
    @if(isset($cotizacionesPorVendedor) && $cotizacionesPorVendedor->count() > 0)
        <!-- Pie Chart Canvas -->
        <div class="flex justify-center mb-4">
            <div class="w-full max-w-md" style="height: 700px;">
                <canvas id="pieChart" class="w-full h-full"></canvas>
            </div>
        </div>

        <!-- Legend -->
        <div class="flex justify-center flex-wrap gap-4 text-sm">
            @php $colors = ['#D88429', '#166379', '#B1B7BB', '#E1DFD9']; @endphp
            @foreach($cotizacionesPorVendedor as $index => $vendedor)
                <div class="flex items-center gap-2">
                    <div class="w-4 h-4 rounded-full" style="background-color: {{ $colors[$index] ?? '#E1DFD9' }};"></div>
                    <span class="whitespace-nowrap">{{ $vendedor->nombre }} ({{ $vendedor->cotizaciones_count }})</span>
                </div>
            @endforeach
        </div>
    @else
        <div class="text-center py-8 text-gray-500">
            <p>No hay datos de cotizaciones para mostrar</p>
        </div>
    @endif
</div>

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const ctx = document.getElementById('pieChart');
        if (ctx) {
            @if(isset($cotizacionesPorVendedor) && $cotizacionesPorVendedor->count() > 0)
                const pieChart = new Chart(ctx, {
                    type: 'doughnut',
                    data: {
                        labels: [
                            @foreach($cotizacionesPorVendedor as $vendedor)
                                '{{ $vendedor->nombre }}',
                            @endforeach
                        ],
                        datasets: [{
                            data: [
                                @foreach($cotizacionesPorVendedor as $vendedor)
                                    {{ $vendedor->cotizaciones_count }},
                                @endforeach
                            ],
                            backgroundColor: [
                                '#D88429',  // Malkoni Primary
                                '#166379',  // Malkoni Secondary
                                '#B1B7BB',  // Malkoni Cancel
                                '#E1DFD9'   // Malkoni Background
                            ],
                            borderColor: '#ffffff',
                            borderWidth: 2
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: {
                                display: false
                            }
                        },
                        cutout: '60%',
                        layout: {
                            padding: 10
                        }
                    }
                });

                // Resize chart on window resize
                window.addEventListener('resize', function() {
                    pieChart.resize();
                });
            @endif
        }
    });
</script>
@endpush