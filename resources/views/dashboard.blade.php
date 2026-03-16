<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - Biblioteca Digital</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Sora:wght@300;400;500;600;700&display=swap');
        body { font-family: 'Sora', sans-serif; }
    </style>
</head>
<body class="min-h-screen bg-[#0d0d14]"
    style="background: radial-gradient(ellipse at 60% 20%, #1a1a2e 0%, #0d0d14 70%);">

    {{-- Navbar --}}
    <nav class="border-b border-white/10 bg-[#13131f]/80 backdrop-blur-xl px-6 py-4">
        <div class="max-w-6xl mx-auto flex items-center justify-between">
            <div class="flex items-center gap-3">
                <div class="w-9 h-9 rounded-xl flex items-center justify-center"
                    style="background: linear-gradient(135deg, #6366f1, #8b5cf6, #ec4899);">
                    <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.746 0 3.332.477 4.5 1.253v13C19.832 18.477 18.246 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
                    </svg>
                </div>
                <span class="text-white font-semibold text-lg">Biblioteca Digital</span>
            </div>
            <div class="flex items-center gap-4">
                <span class="text-white/50 text-sm">{{ auth()->user()->name }}</span>
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit"
                        class="text-sm text-white/40 hover:text-white/80 transition-colors px-3 py-1.5 rounded-lg border border-white/10 hover:border-white/20">
                        Cerrar sesión
                    </button>
                </form>
            </div>
        </div>
    </nav>

    <div class="max-w-6xl mx-auto px-6 py-10">

        {{-- Mensaje de éxito --}}
        @if (session('status'))
            <div x-data="{ show: true }" x-show="show"
                x-transition:enter="transition ease-out duration-500"
                x-transition:enter-start="opacity-0 -translate-y-2"
                x-transition:enter-end="opacity-100 translate-y-0"
                class="mb-6 flex items-center gap-3 p-4 bg-green-500/10 border border-green-500/30 rounded-xl text-green-400 text-sm">
                <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                </svg>
                Tu contraseña ha sido restablecida correctamente.
            </div>
        @endif

        @if (session('login_success'))
            <div x-data="{ show: true }" x-show="show"
                x-transition:enter="transition ease-out duration-500"
                x-transition:enter-start="opacity-0 -translate-y-2"
                x-transition:enter-end="opacity-100 translate-y-0"
                class="mb-6 flex items-center gap-3 p-4 bg-green-500/10 border border-green-500/30 rounded-xl text-green-400 text-sm">
                <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                </svg>
                Inicio de sesión exitoso. ¡Bienvenido, {{ auth()->user()->name }}!
            </div>
        @endif

        {{-- Bienvenida --}}
        <div class="mb-8">
            <h1 class="text-white text-2xl font-semibold">Bienvenido, {{ auth()->user()->name }} 👋</h1>
            <p class="text-white/40 text-sm mt-1">Explora el catálogo de la Biblioteca Digital Online</p>
        </div>

        {{-- Cards --}}
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <div class="bg-[#13131f]/90 border border-white/10 rounded-2xl p-6">
                <div class="w-10 h-10 rounded-xl mb-4 flex items-center justify-center bg-indigo-500/20">
                    <svg class="w-5 h-5 text-indigo-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253"/>
                    </svg>
                </div>
                <h3 class="text-white font-medium">Catálogo</h3>
                <p class="text-white/40 text-sm mt-1">Explora todos los libros disponibles</p>
            </div>
            <div class="bg-[#13131f]/90 border border-white/10 rounded-2xl p-6">
                <div class="w-10 h-10 rounded-xl mb-4 flex items-center justify-center bg-pink-500/20">
                    <svg class="w-5 h-5 text-pink-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"/>
                    </svg>
                </div>
                <h3 class="text-white font-medium">Favoritos</h3>
                <p class="text-white/40 text-sm mt-1">Tus libros guardados</p>
            </div>
            <div class="bg-[#13131f]/90 border border-white/10 rounded-2xl p-6">
                <div class="w-10 h-10 rounded-xl mb-4 flex items-center justify-center bg-purple-500/20">
                    <svg class="w-5 h-5 text-purple-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                    </svg>
                </div>
                <h3 class="text-white font-medium">Mi perfil</h3>
                <p class="text-white/40 text-sm mt-1">Edita tu información personal</p>
            </div>
        </div>

    </div>
</body>
</html>
