<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Recuperar Contraseña - Biblioteca Digital</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Sora:wght@300;400;500;600;700&display=swap');
        body { font-family: 'Sora', sans-serif; }
    </style>
</head>
<body class="min-h-screen flex items-center justify-center px-4"
      style="background: radial-gradient(ellipse at 60% 20%, #1a1a2e 0%, #0d0d14 70%);">

    {{-- Fondo decorativo --}}
    <div class="absolute inset-0 overflow-hidden pointer-events-none">
        <div class="absolute top-20 left-20 w-64 h-64 bg-purple-600/10 rounded-full blur-3xl"></div>
        <div class="absolute bottom-20 right-20 w-80 h-80 bg-blue-600/10 rounded-full blur-3xl"></div>
        <div class="absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 w-96 h-96 bg-indigo-600/5 rounded-full blur-3xl"></div>
    </div>

    {{-- Tarjeta principal --}}
    <div
        x-data="{ cargado: false, hovering: false, enviado: false }"
        x-init="setTimeout(() => cargado = true, 100)"
        x-show="cargado"
        x-transition:enter="transition ease-out duration-700"
        x-transition:enter-start="opacity-0 translate-y-8 scale-95"
        x-transition:enter-end="opacity-100 translate-y-0 scale-100"
        class="relative w-full max-w-md"
    >
        <div class="bg-[#13131f]/90 backdrop-blur-xl border border-white/10 rounded-2xl p-8 shadow-2xl shadow-black/50">

            {{-- Logo --}}
            <div class="flex flex-col items-center mb-8">
                <div class="w-14 h-14 rounded-2xl mb-4 flex items-center justify-center shadow-lg"
                     style="background: linear-gradient(135deg, #6366f1, #8b5cf6, #ec4899);">
                    <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z"/>
                    </svg>
                </div>
                <h1 class="text-white text-2xl font-semibold tracking-tight">Recuperar contraseña</h1>
                <p class="text-white/40 text-sm mt-1 text-center">Te enviaremos un enlace para restablecer tu contraseña</p>
            </div>

            {{-- Mensaje de éxito --}}
            @if(session('status'))
                <div x-data="{ visible: true }"
                     x-show="visible"
                     x-transition
                     class="mb-6 p-4 bg-green-500/10 border border-green-500/30 rounded-xl text-green-400 text-sm flex items-center gap-3">
                    <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    {{ session('status') }}
                </div>
            @endif

            {{-- Formulario --}}
            <form method="POST" action="{{ route('password.email') }}" class="space-y-5">
                @csrf

                {{-- Correo electrónico --}}
                <div>
                    <label for="email" class="block text-white/60 text-xs font-medium mb-2 uppercase tracking-widest">
                        Correo electrónico
                    </label>
                    <input
                        id="email"
                        type="email"
                        name="email"
                        value="{{ old('email') }}"
                        placeholder="usuario@ejemplo.com"
                        autocomplete="email"
                        required
                        class="w-full bg-white/5 border border-white/10 text-white placeholder-white/20
                               rounded-xl px-4 py-3 text-sm outline-none
                               focus:border-indigo-500/60 focus:ring-1 focus:ring-indigo-500/30
                               transition-all duration-300"
                    >
                    @error('email')
                        <p class="mt-1 text-red-400 text-xs">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Botón enviar --}}
                <button
                    type="submit"
                    @mouseenter="hovering = true"
                    @mouseleave="hovering = false"
                    class="w-full py-3 px-4 rounded-xl text-white text-sm font-medium
                           transition-all duration-300 transform
                           hover:scale-[1.02] hover:shadow-lg hover:shadow-indigo-500/25
                           active:scale-[0.98]"
                    style="background: linear-gradient(135deg, #6366f1, #8b5cf6);"
                    :style="hovering ? 'background: linear-gradient(135deg, #4f46e5, #7c3aed); box-shadow: 0 0 30px rgba(99,102,241,0.4)' : 'background: linear-gradient(135deg, #6366f1, #8b5cf6)'"
                >
                    Enviar enlace de recuperación
                </button>

            </form>

            {{-- Volver al login --}}
            <p class="text-center text-white/30 text-xs mt-6">
                ¿Recordaste tu contraseña?
                <a href="{{ route('login') }}"
                   class="text-indigo-400 hover:text-indigo-300 font-medium transition-colors duration-200 ml-1">
                    Inicia sesión
                </a>
            </p>

        </div>
    </div>

    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
</body>
</html>
