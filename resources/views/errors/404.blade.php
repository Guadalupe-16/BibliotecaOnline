<!DOCTYPE html>
<html lang="es" class="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Página no encontrada — Biblioteca Digital</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-[#13151f] text-white min-h-screen flex items-center justify-center">

    <div class="text-center px-6">

        {{-- Ícono --}}
        <div class="flex justify-center mb-8">
            <div class="w-24 h-24 rounded-2xl bg-[#1e2130] flex items-center justify-center">
                <svg class="w-12 h-12 text-indigo-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                        d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.746 0 3.332.477 4.5 1.253v13C19.832 18.477 18.246 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/>
                </svg>
            </div>
        </div>

        {{-- Código de error --}}
        <p class="text-7xl font-bold text-indigo-500 mb-2">404</p>

        {{-- Título --}}
        <h1 class="text-2xl font-semibold text-white mb-3">Página no encontrada</h1>

        {{-- Descripción --}}
        <p class="text-gray-400 max-w-sm mx-auto mb-8">
            La página que estás buscando no existe o fue movida.
        </p>

        {{-- Acciones --}}
        <div class="flex flex-col sm:flex-row items-center justify-center gap-3">
            <a href="{{ route('catalogo') }}"
                class="inline-flex items-center gap-2 px-5 py-2.5 rounded-xl bg-indigo-600 hover:bg-indigo-500 text-white text-sm font-medium transition-colors duration-200">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/>
                </svg>
                Ir al catálogo
            </a>

            <a href="javascript:history.back()"
                class="inline-flex items-center gap-2 px-5 py-2.5 rounded-xl border border-white/10 hover:border-white/30 text-white/60 hover:text-white text-sm font-medium transition-all duration-200">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                </svg>
                Volver
            </a>
        </div>

    </div>

</body>
</html>
