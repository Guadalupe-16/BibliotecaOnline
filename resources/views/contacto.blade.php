<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Contacto - Biblioteca Digital</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-[#13151f] text-white min-h-screen">

    @include('components.navigation')

    <main class="lg:ml-64 min-h-screen p-6 lg:p-10">

        <div
            x-data="{ visible: false }"
            x-init="setTimeout(() => visible = true, 100)"
            x-show="visible"
            x-transition:enter="transition ease-out duration-700"
            x-transition:enter-start="opacity-0 -translate-y-6"
            x-transition:enter-end="opacity-100 translate-y-0"
            class="mb-10"
        >
            <h1 class="text-3xl font-bold text-white">Contacto</h1>
            <p class="text-white/50 mt-2">¿Tienes alguna pregunta o sugerencia? Escríbenos.</p>
        </div>

        <div class="max-w-2xl">
            <div
                x-data="{
                    nombre: '',
                    email: '',
                    mensaje: '',
                    enviado: false,
                    enviando: false,
                    error: '',
                    async enviar() {
                        this.enviando = true;
                        this.error = '';
                        await new Promise(r => setTimeout(r, 1000));
                        this.enviando = false;
                        this.enviado = true;
                    }
                }"
                class="bg-[#1a1d2e] rounded-2xl p-8 border border-white/5"
            >
                {{-- Estado de éxito --}}
                <div
                    x-show="enviado"
                    x-transition:enter="transition ease-out duration-500"
                    x-transition:enter-start="opacity-0 scale-95"
                    x-transition:enter-end="opacity-100 scale-100"
                    class="flex flex-col items-center justify-center py-12 text-center"
                >
                    <div class="w-16 h-16 rounded-full bg-green-500/20 flex items-center justify-center mb-4">
                        <svg class="w-8 h-8 text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                        </svg>
                    </div>
                    <h3 class="text-white font-semibold text-lg">¡Mensaje enviado!</h3>
                    <p class="text-white/50 mt-2 text-sm">Gracias por contactarnos. Te responderemos pronto.</p>
                    <button
                        x-on:click="enviado = false; nombre = ''; email = ''; mensaje = ''"
                        class="mt-6 text-indigo-400 hover:text-indigo-300 text-sm transition-colors duration-200"
                    >
                        Enviar otro mensaje
                    </button>
                </div>

                {{-- Formulario --}}
                <form x-show="!enviado" x-on:submit.prevent="enviar()">

                    <div class="flex flex-col gap-6">

                        <div class="flex flex-col gap-2">
                            <label class="text-sm text-white/60">Nombre</label>
                            <input
                                x-model="nombre"
                                type="text"
                                placeholder="Tu nombre completo"
                                required
                                class="bg-[#13151f] border border-white/10 rounded-xl px-4 py-3 text-sm text-white placeholder-white/20 focus:outline-none focus:border-indigo-500/50 transition-colors duration-200"
                            >
                        </div>

                        <div class="flex flex-col gap-2">
                            <label class="text-sm text-white/60">Correo electrónico</label>
                            <input
                                x-model="email"
                                type="email"
                                placeholder="tu@correo.com"
                                required
                                class="bg-[#13151f] border border-white/10 rounded-xl px-4 py-3 text-sm text-white placeholder-white/20 focus:outline-none focus:border-indigo-500/50 transition-colors duration-200"
                            >
                        </div>

                        <div class="flex flex-col gap-2">
                            <label class="text-sm text-white/60">Mensaje</label>
                            <textarea
                                x-model="mensaje"
                                rows="5"
                                placeholder="Escribe tu mensaje aquí..."
                                required
                                class="bg-[#13151f] border border-white/10 rounded-xl px-4 py-3 text-sm text-white placeholder-white/20 focus:outline-none focus:border-indigo-500/50 transition-colors duration-200 resize-none"
                            ></textarea>
                        </div>

                        <button
                            type="submit"
                            x-bind:disabled="enviando"
                            class="w-full py-3 rounded-xl bg-indigo-600 hover:bg-indigo-500 disabled:opacity-50 disabled:cursor-not-allowed text-white text-sm font-medium transition-all duration-200 flex items-center justify-center gap-2"
                        >
                            <svg x-show="enviando" class="w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8z"/>
                            </svg>
                            <span x-text="enviando ? 'Enviando...' : 'Enviar mensaje'"></span>
                        </button>

                    </div>
                </form>

            </div>
        </div>

    </main>

</body>
</html>