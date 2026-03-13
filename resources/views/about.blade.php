<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Acerca de - Biblioteca Digital</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-[#13151f] text-white min-h-screen">

    @include('components.navigation')

    <main class="lg:ml-64 min-h-screen p-6 lg:p-10">

        {{-- Encabezado con animación de entrada --}}
        <div
            x-data="{ visible: false }"
            x-init="setTimeout(() => visible = true, 100)"
            x-show="visible"
            x-transition:enter="transition ease-out duration-700"
            x-transition:enter-start="opacity-0 -translate-y-6"
            x-transition:enter-end="opacity-100 translate-y-0"
            class="mb-12"
        >
            <h1 class="text-3xl font-bold text-white">Acerca de</h1>
            <p class="text-white/50 mt-2 max-w-xl">
                Somos un equipo de estudiantes desarrollando una biblioteca digital moderna como proyecto universitario.
            </p>
        </div>

        {{-- Descripción del proyecto --}}
        <div
            x-data="{ visible: false }"
            x-init="setTimeout(() => visible = true, 300)"
            x-show="visible"
            x-transition:enter="transition ease-out duration-700"
            x-transition:enter-start="opacity-0 translate-y-6"
            x-transition:enter-end="opacity-100 translate-y-0"
            class="bg-[#1a1d2e] rounded-2xl p-6 border border-white/5 mb-12"
        >
            <h2 class="text-lg font-semibold text-white mb-3">📚 El Proyecto</h2>
            <p class="text-white/60 leading-relaxed">
                Biblioteca Digital es una plataforma web desarrollada con Laravel, Alpine.js y Tailwind CSS.
                Permite a los usuarios explorar un catálogo de libros, guardar favoritos, buscar títulos
                y gestionar préstamos. El proyecto aplica buenas prácticas de desarrollo como
                skinny controllers, Repository pattern y pruebas automatizadas.
            </p>
        </div>

        {{-- Equipo --}}
        <div
            x-data="{ visible: false }"
            x-init="setTimeout(() => visible = true, 500)"
            x-show="visible"
            x-transition:enter="transition ease-out duration-700"
            x-transition:enter-start="opacity-0 translate-y-6"
            x-transition:enter-end="opacity-100 translate-y-0"
        >
            <h2 class="text-lg font-semibold text-white mb-6">👥 El Equipo</h2>

            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">

                @foreach([
                    ['nombre' => 'Guadalupe Amavizca', 'rol' => 'Base de datos & API', 'inicial' => 'G', 'color' => 'from-pink-500 to-rose-600'],
                    ['nombre' => 'Joel Ibarra', 'rol' => 'Autenticación & Roles', 'inicial' => 'J', 'color' => 'from-blue-500 to-indigo-600'],
                    ['nombre' => 'Jorge Martinez', 'rol' => 'Navegación & Favoritos', 'inicial' => 'J', 'color' => 'from-indigo-500 to-purple-600'],
                    ['nombre' => 'Javier Romo', 'rol' => 'Features & Soporte', 'inicial' => 'J', 'color' => 'from-green-500 to-emerald-600'],
                ] as $integrante)
                <div
                    x-data="{ hover: false }"
                    x-on:mouseenter="hover = true"
                    x-on:mouseleave="hover = false"
                    x-bind:class="hover ? 'scale-105 border-indigo-500/30 shadow-lg shadow-indigo-500/10' : 'border-white/5'"
                    class="bg-[#1a1d2e] rounded-2xl p-6 border transition-all duration-300 flex flex-col items-center text-center"
                >
                    {{-- Avatar --}}
                    <div class="w-20 h-20 rounded-full bg-gradient-to-br {{ $integrante['color'] }} flex items-center justify-center text-white text-2xl font-bold mb-4 shadow-lg">
                        {{ $integrante['inicial'] }}
                    </div>

                    <h3 class="text-white font-semibold text-sm">{{ $integrante['nombre'] }}</h3>
                    <p class="text-white/40 text-xs mt-1">{{ $integrante['rol'] }}</p>
                </div>
                @endforeach

            </div>
        </div>

        {{-- Stack tecnológico --}}
        <div
            x-data="{ visible: false }"
            x-init="setTimeout(() => visible = true, 700)"
            x-show="visible"
            x-transition:enter="transition ease-out duration-700"
            x-transition:enter-start="opacity-0 translate-y-6"
            x-transition:enter-end="opacity-100 translate-y-0"
            class="mt-12"
        >
            <h2 class="text-lg font-semibold text-white mb-6">🛠️ Tecnologías</h2>
            <div class="flex flex-wrap gap-3">
                @foreach(['Laravel 12', 'Alpine.js', 'Tailwind CSS', 'Livewire', 'SQLite', 'PHP 8.5', 'Vite'] as $tech)
                <span class="px-4 py-2 bg-[#1a1d2e] border border-white/10 rounded-full text-sm text-white/60 hover:text-white hover:border-indigo-500/50 transition-all duration-200">
                    {{ $tech }}
                </span>
                @endforeach
            </div>
        </div>

    </main>

</body>
</html>