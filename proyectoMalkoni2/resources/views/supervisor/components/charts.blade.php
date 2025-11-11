<!-- Charts Section -->
<div class="bg-white rounded-lg p-6 border border-gray-200 shadow-sm">
    <h2 class="text-lg font-syncopate font-bold text-gray-900 mb-6">
        Gráfico de torta con ventas por vendedor
    </h2>
    
    <!-- Pie Chart Canvas -->
    <div class="flex justify-center mb-4">
        <canvas id="pieChart" width="300" height="300"></canvas>
    </div>

    <!-- Legend -->
    <div class="flex justify-center flex-wrap gap-4 text-sm">
        <div class="flex items-center gap-2">
            <div class="w-4 h-4 rounded-full" style="background-color: #D88429;"></div>
            <span>Juan Pérez</span>
        </div>
        <div class="flex items-center gap-2">
            <div class="w-4 h-4 rounded-full" style="background-color: #166379;"></div>
            <span>María García</span>
        </div>
        <div class="flex items-center gap-2">
            <div class="w-4 h-4 rounded-full" style="background-color: #B1B7BB;"></div>
            <span>Carlos Ruiz</span>
        </div>
        <div class="flex items-center gap-2">
            <div class="w-4 h-4 rounded-full" style="background-color: #E1DFD9;"></div>
            <span>Ana López</span>
        </div>
    </div>
</div>

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const ctx = document.getElementById('pieChart');
        if (ctx) {
            const pieChart = new Chart(ctx, {
                type: 'doughnut',
                data: {
                    labels: ['Juan Pérez', 'María García', 'Carlos Ruiz', 'Ana López'],
                    datasets: [{
                        data: [35, 25, 25, 15],
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
                    maintainAspectRatio: true,
                    plugins: {
                        legend: {
                            display: false
                        }
                    }
                }
            });
        }
    });
</script>
@endpush