<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mis Favoritos - Biblioteca Digital</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-[#13151f] text-white min-h-screen">

    @include('components.navigation')

    <main class="lg:ml-64 min-h-screen p-6 lg:p-8">

        <h1 class="text-2xl font-bold text-white mb-6">Mis Favoritos</h1>

        @if($favoritos->isEmpty())
            <div class="flex flex-col items-center justify-center mt-20 text-white/40">
                <svg class="w-16 h-16 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"/>
                </svg>
                <p class="text-lg">No tienes libros favoritos aún</p>
                <a href="#" class="mt-4 text-indigo-400 hover:text-indigo-300 transition-colors duration-200">
                    Explorar catálogo
                </a>
            </div>
        @else
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
                @foreach($favoritos as $favorito)
                <div
                    x-data="{ esFavorito: true }"
                    class="bg-[#1a1d2e] rounded-xl overflow-hidden border border-white/5 hover:border-indigo-500/30 transition-all duration-300 hover:scale-105 hover:shadow-lg hover:shadow-indigo-500/10"
                >
                    {{-- Portada del libro --}}
                    <div class="h-48 bg-gradient-to-br from-indigo-600 to-purple-700 relative">
                        <button
                            x-on:click="toggleFavorito({{ $favorito->libro->id }}, $el)"
                            x-bind:class="esFavorito ? 'text-red-400' : 'text-white/40'"
                            class="absolute top-3 right-3 w-9 h-9 bg-black/30 backdrop-blur-sm rounded-full flex items-center justify-center transition-all duration-300 hover:scale-110"
                        >
                            <svg
                                x-bind:class="esFavorito ? 'fill-current' : ''"
                                class="w-5 h-5 transition-all duration-300"
                                fill="none" stroke="currentColor" viewBox="0 0 24 24"
                            >
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"/>
                            </svg>
                        </button>
                    </div>

                    {{-- Info del libro --}}
                    <div class="p-4">
                        <h3 class="text-white font-semibold text-sm truncate">{{ $favorito->libro->titulo }}</h3>
                        <p class="text-white/50 text-xs mt-1">{{ $favorito->libro->autor->nombre ?? 'Autor desconocido' }}</p>
                    </div>
                </div>
                @endforeach
            </div>
        @endif
    </main>

    <script>
        async function toggleFavorito(libroId, boton) {
            const response = await fetch(`/favoritos/${libroId}/toggle`, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content,
                    'Content-Type': 'application/json',
                },
            });
            const data = await response.json();

            // Actualizar estado Alpine
            const alpineEl = boton.closest('[x-data]');
            if (alpineEl && alpineEl._x_dataStack) {
                alpineEl._x_dataStack[0].esFavorito = data.esFavorito;
            }
        }
    </script>

</body>
</html>