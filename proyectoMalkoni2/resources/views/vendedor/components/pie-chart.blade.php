<!-- Pie Chart for Product Quotations -->
<div class="bg-white rounded-lg p-6 border border-gray-200 shadow-sm">
    <h2 class="text-lg font-syncopate font-bold text-gray-900 mb-6">
        COTIZACIONES POR PRODUCTO
    </h2>
    
    @if(isset($cotizacionesPorProducto) && $cotizacionesPorProducto->count() > 0)
        <!-- Pie Chart Canvas -->
        <div class="flex justify-center mb-4">
            <canvas id="productChart" width="300" height="300"></canvas>
        </div>

        <!-- Legend -->
        <div class="flex justify-center flex-wrap gap-4 text-sm">
            @php $colors = ['#D88429', '#166379', '#16a34a', '#d946ef']; @endphp
            @foreach($cotizacionesPorProducto as $index => $item)
                <div class="flex items-center gap-2">
                    <div class="w-4 h-4 rounded-full" style="background-color: {{ $colors[$index] ?? '#E1DFD9' }};"></div>
                    <span>{{ $item->tipo }} - {{ $item->subtipo }} ({{ $item->total_cotizaciones }})</span>
                </div>
            @endforeach
        </div>
    @else
        <div class="text-center py-8 text-gray-500">
            <p>No hay cotizaciones de productos para mostrar</p>
        </div>
    @endif
</div>

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const productCtx = document.getElementById('productChart');
        if (productCtx) {
            @if(isset($cotizacionesPorProducto) && $cotizacionesPorProducto->count() > 0)
                new Chart(productCtx, {
                    type: 'doughnut',
                    data: {
                        labels: [
                            @foreach($cotizacionesPorProducto as $item)
                                '{{ $item->tipo }} - {{ $item->subtipo }}',
                            @endforeach
                        ],
                        datasets: [{
                            data: [
                                @foreach($cotizacionesPorProducto as $item)
                                    {{ $item->total_cotizaciones }},
                                @endforeach
                            ],
                            backgroundColor: [
                                '#D88429',  // Malkoni Primary
                                '#166379',  // Malkoni Secondary
                                '#16a34a',  // Green
                                '#d946ef'   // Purple
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
            @endif
        }
    });
</script>
@endpush