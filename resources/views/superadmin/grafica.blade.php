<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gráfica de Usuarios - Biblioteca Digital</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body class="bg-[#13151f] text-white min-h-screen">

    @include('components.navigation')

    <main class="lg:ml-64 min-h-screen p-6 lg:p-10">

        {{-- Encabezado --}}
        <div class="mb-8">
            <a href="{{ route('superadmin.index') }}" class="text-white/40 hover:text-white text-sm transition-colors duration-200 flex items-center gap-2 mb-4">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                </svg>
                Volver al panel
            </a>
            <h1 class="text-3xl font-bold text-white">Usuarios registrados</h1>
            <p class="text-white/50 mt-1">Registros por fecha</p>
        </div>

        {{-- Filtros --}}
        <div class="flex gap-3 mb-8">
            <button onclick="cargarGrafica('dia')" id="btn-dia"
                class="filtro-btn px-4 py-2 rounded-xl text-sm border border-indigo-500 bg-indigo-500/20 text-indigo-400 transition-all duration-200">
                Por día
            </button>
            <button onclick="cargarGrafica('semana')" id="btn-semana"
                class="filtro-btn px-4 py-2 rounded-xl text-sm border border-white/10 text-white/50 hover:border-indigo-500/50 hover:text-indigo-400 transition-all duration-200">
                Por semana
            </button>
            <button onclick="cargarGrafica('mes')" id="btn-mes"
                class="filtro-btn px-4 py-2 rounded-xl text-sm border border-white/10 text-white/50 hover:border-indigo-500/50 hover:text-indigo-400 transition-all duration-200">
                Por mes
            </button>
        </div>

        {{-- Gráfica --}}
        <div class="bg-[#1a1d2e] rounded-2xl border border-white/5 p-6">
            <canvas id="graficaUsuarios" height="100"></canvas>
        </div>

    </main>

    <script>
        let grafica = null;

        async function cargarGrafica(filtro = 'dia') {
            // Actualizar botones
            document.querySelectorAll('.filtro-btn').forEach(btn => {
                btn.className = 'filtro-btn px-4 py-2 rounded-xl text-sm border border-white/10 text-white/50 hover:border-indigo-500/50 hover:text-indigo-400 transition-all duration-200';
            });
            document.getElementById('btn-' + filtro).className = 'filtro-btn px-4 py-2 rounded-xl text-sm border border-indigo-500 bg-indigo-500/20 text-indigo-400 transition-all duration-200';

            const response = await fetch('{{ route("superadmin.stats.usuarios") }}?filtro=' + filtro);
            const data = await response.json();

            if (grafica) grafica.destroy();

            const ctx = document.getElementById('graficaUsuarios').getContext('2d');
            grafica = new Chart(ctx, {
                type: 'bar',
                data: {
                    labels: data.fechas,
                    datasets: [{
                        label: 'Usuarios registrados',
                        data: data.totales,
                        backgroundColor: 'rgba(99, 102, 241, 0.5)',
                        borderColor: 'rgba(99, 102, 241, 1)',
                        borderWidth: 2,
                        borderRadius: 6,
                    }]
                },
                options: {
                    responsive: true,
                    plugins: {
                        legend: {
                            labels: { color: 'rgba(255,255,255,0.6)' }
                        }
                    },
                    scales: {
                        x: {
                            ticks: { color: 'rgba(255,255,255,0.4)' },
                            grid: { color: 'rgba(255,255,255,0.05)' }
                        },
                        y: {
                            ticks: {
                                color: 'rgba(255,255,255,0.4)',
                                stepSize: 1
                            },
                            grid: { color: 'rgba(255,255,255,0.05)' }
                        }
                    }
                }
            });
        }

        // Cargar al iniciar
        cargarGrafica('dia');
    </script>

</body>
</html>