<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Verificar Correo - Biblioteca Digital</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Sora:wght@300;400;500;600;700&display=swap');
        body { font-family: 'Sora', sans-serif; }
    </style>
</head>
<body class="min-h-screen flex items-center justify-center px-4"
      style="background: radial-gradient(ellipse at 60% 20%, #1a1a2e 0%, #0d0d14 70%);">

    <div class="absolute inset-0 overflow-hidden pointer-events-none">
        <div class="absolute top-20 left-20 w-64 h-64 bg-purple-600/10 rounded-full blur-3xl"></div>
        <div class="absolute bottom-20 right-20 w-80 h-80 bg-blue-600/10 rounded-full blur-3xl"></div>
        <div class="absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 w-96 h-96 bg-indigo-600/5 rounded-full blur-3xl"></div>
    </div>

    <div
        x-data="{
            cargado: false,
            hovering: false,
            digitos: ['', '', '', '', '', ''],
            enfocarSiguiente(index, event) {
                const val = event.target.value.replace(/\D/g, '');
                this.digitos[index] = val.slice(-1);
                if (val && index < 5) {
                    this.$refs['d' + (index + 1)].focus();
                }
            },
            alRetroceso(index, event) {
                if (event.key === 'Backspace' && !this.digitos[index] && index > 0) {
                    this.$refs['d' + (index - 1)].focus();
                }
            },
            get pinCompleto() {
                return this.digitos.join('');
            }
        }"
        x-init="setTimeout(() => cargado = true, 100)"
        x-show="cargado"
        x-transition:enter="transition ease-out duration-700"
        x-transition:enter-start="opacity-0 translate-y-8 scale-95"
        x-transition:enter-end="opacity-100 translate-y-0 scale-100"
        class="relative w-full max-w-md"
    >
        <div class="bg-[#13131f]/90 backdrop-blur-xl border border-white/10 rounded-2xl p-8 shadow-2xl shadow-black/50">

            {{-- Icono --}}
            <div class="flex flex-col items-center mb-8">
                <div class="w-14 h-14 rounded-2xl mb-4 flex items-center justify-center shadow-lg"
                     style="background: linear-gradient(135deg, #6366f1, #8b5cf6, #ec4899);">
                    <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                    </svg>
                </div>
                <h1 class="text-white text-2xl font-semibold tracking-tight">Verifica tu correo</h1>
                <p class="text-white/40 text-sm mt-1 text-center">
                    Ingresa el PIN de 6 dígitos que enviamos a<br>
                    <span class="text-indigo-400">{{ $usuario->email }}</span>
                </p>
            </div>

            {{-- Mensajes --}}
            @if(session('info'))
                <div x-data="{ visible: true }" x-show="visible" x-transition
                     class="mb-6 p-4 bg-blue-500/10 border border-blue-500/30 rounded-xl text-blue-400 text-sm flex items-center gap-3">
                    <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    {{ session('info') }}
                </div>
            @endif

            @if($errors->any())
                <div x-data="{ visible: true }" x-show="visible" x-transition
                     class="mb-6 p-4 bg-red-500/10 border border-red-500/30 rounded-xl text-red-400 text-sm">
                    {{ $errors->first('pin') }}
                </div>
            @endif

            {{-- Formulario PIN --}}
            <form method="POST" action="{{ route('verificar.email.verificar', $usuario->id) }}">
                @csrf

                {{-- Inputs individuales por dígito --}}
                <div class="flex gap-3 justify-center mb-6">
                    @for($i = 0; $i < 6; $i++)
                        <input
                            x-ref="d{{ $i }}"
                            type="text"
                            inputmode="numeric"
                            maxlength="1"
                            x-model="digitos[{{ $i }}]"
                            @input="enfocarSiguiente({{ $i }}, $event)"
                            @keydown="alRetroceso({{ $i }}, $event)"
                            class="w-12 h-14 text-center text-white text-xl font-bold
                                   bg-white/5 border border-white/10 rounded-xl outline-none
                                   focus:border-indigo-500/60 focus:ring-2 focus:ring-indigo-500/30
                                   transition-all duration-200"
                        >
                    @endfor
                </div>

                {{-- Campo oculto con el PIN completo --}}
                <input type="hidden" name="pin" :value="pinCompleto">

                {{-- Botón verificar --}}
                <button
                    type="submit"
                    @mouseenter="hovering = true"
                    @mouseleave="hovering = false"
                    :disabled="pinCompleto.length < 6"
                    class="w-full py-3 px-4 rounded-xl text-white text-sm font-medium
                           transition-all duration-300 transform
                           hover:scale-[1.02] hover:shadow-lg hover:shadow-indigo-500/25
                           active:scale-[0.98] disabled:opacity-40 disabled:cursor-not-allowed"
                    style="background: linear-gradient(135deg, #6366f1, #8b5cf6);"
                    :style="hovering && pinCompleto.length === 6 ? 'background: linear-gradient(135deg, #4f46e5, #7c3aed); box-shadow: 0 0 30px rgba(99,102,241,0.4)' : 'background: linear-gradient(135deg, #6366f1, #8b5cf6)'"
                >
                    Verificar cuenta
                </button>
            </form>

            {{-- Reenviar PIN --}}
            <div class="text-center mt-6">
                <p class="text-white/30 text-xs">
                    ¿No recibiste el correo?
                </p>
                <form method="POST" action="{{ route('verificar.email.reenviar', $usuario->id) }}" class="inline">
                    @csrf
                    <button type="submit"
                            class="text-indigo-400 hover:text-indigo-300 text-xs font-medium transition-colors duration-200 mt-1">
                        Reenviar PIN
                    </button>
                </form>
            </div>

        </div>
    </div>

    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
</body>
</html>
