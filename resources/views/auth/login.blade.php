<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Iniciar Sesión - Biblioteca Digital</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Sora:wght@300;400;500;600;700&display=swap');
        body { font-family: 'Sora', sans-serif; }
    </style>
</head>
<body class="min-h-screen bg-[#0d0d14] flex items-center justify-center px-4"
      style="background: radial-gradient(ellipse at 60% 20%, #1a1a2e 0%, #0d0d14 70%);">

    {{-- Fondo con partículas decorativas --}}
    <div class="absolute inset-0 overflow-hidden pointer-events-none">
        <div class="absolute top-20 left-20 w-64 h-64 bg-purple-600/10 rounded-full blur-3xl"></div>
        <div class="absolute bottom-20 right-20 w-80 h-80 bg-blue-600/10 rounded-full blur-3xl"></div>
        <div class="absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 w-96 h-96 bg-indigo-600/5 rounded-full blur-3xl"></div>
    </div>

    {{-- Tarjeta principal con Alpine.js --}}
    <div
        x-data="{ cargado: false, hovering: false, mostrarPass: false }"
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
                              d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.746 0 3.332.477 4.5 1.253v13C19.832 18.477 18.246 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/>
                    </svg>
                </div>
                <h1 class="text-white text-2xl font-semibold tracking-tight">Biblioteca Digital</h1>
                <p class="text-white/40 text-sm mt-1">Accede a tu cuenta para continuar</p>
            </div>

            {{-- Errores de sesión --}}
            @if(session('error'))
                <div class="mb-4 p-3 bg-red-500/10 border border-red-500/30 rounded-lg text-red-400 text-sm">
                    {{ session('error') }}
                </div>
            @endif

            {{-- Formulario --}}
            <form method="POST" action="{{ route('login') }}" class="space-y-5">
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

                {{-- Contraseña --}}
                <div>
                    <label for="password" class="block text-white/60 text-xs font-medium mb-2 uppercase tracking-widest">
                        Contraseña
                    </label>
                    <div class="relative">
                        <input
                            id="password"
                            :type="mostrarPass ? 'text' : 'password'"
                            name="password"
                            placeholder="••••••••"
                            autocomplete="current-password"
                            required
                            class="w-full bg-white/5 border border-white/10 text-white placeholder-white/20
                                   rounded-xl px-4 py-3 pr-12 text-sm outline-none
                                   focus:border-indigo-500/60 focus:ring-1 focus:ring-indigo-500/30
                                   transition-all duration-300"
                        >
                        {{-- Toggle mostrar contraseña --}}
                        <button type="button"
                                @click="mostrarPass = !mostrarPass"
                                class="absolute right-3 top-1/2 -translate-y-1/2 text-white/30 hover:text-white/60 transition-colors">
                            <svg x-show="!mostrarPass" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                            </svg>
                            <svg x-show="mostrarPass" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21"/>
                            </svg>
                        </button>
                    </div>
                    @error('password')
                        <p class="mt-1 text-red-400 text-xs">{{ $message }}</p>
                    @enderror
                </div>

                {{-- ¿Olvidaste tu contraseña? --}}
                <div class="flex justify-end">
                    <a href="{{ route('password.request') }}"
                       class="text-indigo-400/70 hover:text-indigo-300 text-xs transition-colors duration-200">
                        ¿Olvidaste tu contraseña?
                    </a>
                </div>

                {{-- Botón Iniciar sesión con hover --}}
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
                    Iniciar sesión
                </button>

            </form>

            {{-- Registro --}}
            <p class="text-center text-white/30 text-xs mt-6">
                ¿No tienes cuenta?
                <a href="{{ route('register') }}"
                   class="text-indigo-400 hover:text-indigo-300 font-medium transition-colors duration-200 ml-1">
                    Regístrate aquí
                </a>
            </p>

        </div>
    </div>

    {{-- Alpine.js --}}
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
</body>
</html>
