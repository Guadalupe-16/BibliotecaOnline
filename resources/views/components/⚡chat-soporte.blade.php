<?php

use Livewire\Component;
use App\Models\MensajeSoporte;

new class extends Component
{
    public string $input = '';

    private array $respuestas = [
        'contraseña'   => '🔑 Puedes recuperar tu contraseña haciendo clic en "¿Olvidaste tu contraseña?" en la pantalla de inicio de sesión.',
        'password'     => '🔑 Puedes recuperar tu contraseña haciendo clic en "¿Olvidaste tu contraseña?" en la pantalla de inicio de sesión.',
        'descargar'    => '📥 Para descargar un libro necesitas tener sesión iniciada y el libro debe estar disponible en el catálogo.',
        'libro'        => '📚 Puedes buscar libros en el catálogo usando el buscador. Filtra por categoría, autor o título.',
        'buscar'       => '🔍 Usa el buscador en la parte superior para encontrar libros por título, autor o categoría.',
        'prestamo'     => '📖 Para solicitar un préstamo, abre el detalle del libro y haz clic en "Solicitar préstamo".',
        'favorito'     => '❤️ Puedes agregar libros a favoritos haciendo clic en el ícono de corazón en cada libro.',
        'cuenta'       => '👤 Puedes editar tu cuenta desde el menú de perfil en la parte superior derecha.',
        'registro'     => '✏️ Para registrarte, haz clic en "Registrarse" en el menú y completa el formulario.',
        'login'        => '🔐 Para iniciar sesión, haz clic en "Iniciar sesión" en el menú lateral.',
        'hola'         => '👋 ¡Hola! ¿En qué puedo ayudarte hoy?',
        'ayuda'        => '🤝 Claro, estoy aquí para ayudarte. Puedes preguntarme sobre libros, préstamos, tu cuenta o cualquier duda.',
        'gracias'      => '😊 ¡De nada! Si tienes alguna otra duda, con gusto te ayudo.',
        'adios'        => '👋 ¡Hasta luego! Que tengas un buen día.',
        'error'        => '⚠️ Si estás experimentando un error, intenta recargar la página. Si persiste, contáctanos en soporte@biblioteca.mx',
        'contacto'     => '📧 Puedes contactarnos en soporte@biblioteca.mx o por teléfono al (614) 123-4567.',
        'horario'      => '🕐 Nuestro horario de atención es de Lunes a Viernes de 8:00 a 20:00 hrs.',
        'catalogo'     => '📚 El catálogo está disponible en el menú lateral. Ahí encontrarás todos los libros disponibles.',
    ];

    public function responder(string $texto): string
    {
        MensajeSoporte::create([
            'nombre'  => 'Usuario',
            'email'   => 'anonimo@biblioteca.mx',
            'mensaje' => $texto,
        ]);

        return $this->obtenerRespuesta($texto);
    }

    private function obtenerRespuesta(string $texto): string
    {
        $textoLower = mb_strtolower($texto);

        foreach ($this->respuestas as $palabra => $respuesta) {
            if (str_contains($textoLower, $palabra)) {
                return $respuesta;
            }
        }

        return '🤔 Gracias por tu mensaje. Un agente revisará tu consulta pronto. También puedes escribirnos a soporte@biblioteca.mx';
    }
};
?>

<div
    x-data="{
        abierto: false,
        hover: false,
        input: '',
        mensajes: [],
        enviando: false,

        toggle() { this.abierto = !this.abierto },

        hora() {
            return new Date().toLocaleTimeString('es-MX', { hour: '2-digit', minute: '2-digit' });
        },

        scrollAbajo() {
            this.$nextTick(() => {
                let c = this.$refs.chat;
                if (c) c.scrollTop = c.scrollHeight;
            });
        },

        async enviar() {
            const texto = this.input.trim();
            if (!texto || this.enviando) return;

            this.mensajes.push({ de: 'usuario', texto: texto, hora: this.hora() });
            this.input = '';
            this.enviando = true;
            this.scrollAbajo();

            const respuesta = await $wire.responder(texto);

            this.mensajes.push({ de: 'bot', texto: respuesta, hora: this.hora() });
            this.enviando = false;
            this.scrollAbajo();
        }
    }"
    class="fixed bottom-6 right-6 z-50 flex flex-col items-end gap-3"
>
    {{-- Panel del chat --}}
    <div
        x-show="abierto"
        x-transition:enter="transition ease-out duration-300"
        x-transition:enter-start="opacity-0 translate-y-4 scale-95"
        x-transition:enter-end="opacity-100 translate-y-0 scale-100"
        x-transition:leave="transition ease-in duration-200"
        x-transition:leave-start="opacity-100 translate-y-0 scale-100"
        x-transition:leave-end="opacity-0 translate-y-4 scale-95"
        class="w-80 bg-[#1e2130] border border-[#2e3250] rounded-2xl shadow-2xl flex flex-col overflow-hidden"
        style="height: 430px"
    >
        {{-- Header --}}
        <div class="bg-indigo-600 px-4 py-3 flex items-center justify-between flex-shrink-0">
            <div class="flex items-center gap-2">
                <div class="w-2 h-2 bg-green-400 rounded-full animate-pulse"></div>
                <span class="text-white font-semibold text-sm">Soporte en línea</span>
            </div>
            <button @click="toggle()" class="text-white/70 hover:text-white transition-colors">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                </svg>
            </button>
        </div>

        {{-- Mensajes --}}
        <div
            x-ref="chat"
            class="flex-1 overflow-y-auto p-3 flex flex-col gap-2 scroll-smooth"
        >
            {{-- Saludo fijo --}}
            <div class="flex items-end gap-2">
                <div class="w-7 h-7 rounded-full bg-indigo-600 flex items-center justify-center flex-shrink-0">
                    <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17H3a2 2 0 01-2-2V5a2 2 0 012-2h14a2 2 0 012 2v10a2 2 0 01-2 2h-2"/>
                    </svg>
                </div>
                <div class="max-w-[80%]">
                    <div class="bg-[#2a2f45] text-white text-xs rounded-2xl rounded-bl-sm px-3 py-2 leading-relaxed">
                        👋 ¡Hola! Soy el asistente de Biblioteca Digital. ¿En qué puedo ayudarte?
                    </div>
                </div>
            </div>

            {{-- Mensajes dinámicos --}}
            <template x-for="(msg, i) in mensajes" :key="i">
                <div
                    :class="msg.de === 'usuario' ? 'flex items-end gap-2 justify-end' : 'flex items-end gap-2'"
                    x-transition:enter="transition ease-out duration-300"
                    x-transition:enter-start="opacity-0 translate-y-2"
                    x-transition:enter-end="opacity-100 translate-y-0"
                >
                    {{-- Avatar bot --}}
                    <div x-show="msg.de === 'bot'" class="w-7 h-7 rounded-full bg-indigo-600 flex items-center justify-center flex-shrink-0">
                        <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17H3a2 2 0 01-2-2V5a2 2 0 012-2h14a2 2 0 012 2v10a2 2 0 01-2 2h-2"/>
                        </svg>
                    </div>

                    <div class="max-w-[80%]">
                        <div
                            :class="msg.de === 'usuario'
                                ? 'bg-indigo-600 text-white text-xs rounded-2xl rounded-br-sm px-3 py-2 leading-relaxed'
                                : 'bg-[#2a2f45] text-white text-xs rounded-2xl rounded-bl-sm px-3 py-2 leading-relaxed'"
                            x-text="msg.texto"
                        ></div>
                        <span
                            :class="msg.de === 'usuario' ? 'text-gray-600 text-[10px] mr-1 text-right block' : 'text-gray-600 text-[10px] ml-1'"
                            x-text="msg.hora"
                        ></span>
                    </div>
                </div>
            </template>

            {{-- Indicador escribiendo --}}
            <div x-show="enviando" class="flex items-end gap-2">
                <div class="w-7 h-7 rounded-full bg-indigo-600 flex items-center justify-center flex-shrink-0">
                    <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17H3a2 2 0 01-2-2V5a2 2 0 012-2h14a2 2 0 012 2v10a2 2 0 01-2 2h-2"/>
                    </svg>
                </div>
                <div class="bg-[#2a2f45] rounded-2xl rounded-bl-sm px-3 py-2 flex gap-1 items-center">
                    <span class="w-1.5 h-1.5 bg-gray-400 rounded-full animate-bounce" style="animation-delay: 0ms"></span>
                    <span class="w-1.5 h-1.5 bg-gray-400 rounded-full animate-bounce" style="animation-delay: 150ms"></span>
                    <span class="w-1.5 h-1.5 bg-gray-400 rounded-full animate-bounce" style="animation-delay: 300ms"></span>
                </div>
            </div>
        </div>

        {{-- Input --}}
        <div class="p-3 border-t border-[#2e3250] flex-shrink-0">
            <form @submit.prevent="enviar()" class="flex gap-2">
                <input
                    x-model="input"
                    @keydown.enter.prevent="enviar()"
                    type="text"
                    placeholder="Escribe un mensaje..."
                    autocomplete="off"
                    @mouseenter="$el.classList.add('border-indigo-400')"
                    @mouseleave="$el.classList.remove('border-indigo-400')"
                    class="flex-1 bg-[#13151f] border border-[#2e3250] rounded-xl px-3 py-2 text-xs text-white placeholder-gray-500 focus:outline-none focus:border-indigo-500 transition-colors"
                >
                <button
                    type="submit"
                    :disabled="enviando"
                    @mouseenter="$el.classList.add('bg-indigo-500')"
                    @mouseleave="$el.classList.remove('bg-indigo-500')"
                    class="bg-indigo-600 hover:bg-indigo-500 text-white rounded-xl px-3 py-2 transition-all duration-200 flex-shrink-0 disabled:opacity-50"
                >
                    <svg class="w-4 h-4 rotate-90" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"/>
                    </svg>
                </button>
            </form>
        </div>
    </div>

    {{-- Botón flotante --}}
    <button
        @click="toggle()"
        @mouseenter="hover = true"
        @mouseleave="hover = false"
        class="w-14 h-14 rounded-full shadow-lg flex items-center justify-center transition-all duration-300"
        :class="abierto ? 'bg-red-500 hover:bg-red-400' : 'bg-indigo-600 hover:bg-indigo-500'"
        :style="hover && !abierto ? 'transform: scale(1.1)' : 'transform: scale(1)'"
        title="Chat de soporte"
    >
        <svg
            x-show="!abierto"
            x-transition:enter="transition duration-200"
            x-transition:enter-start="opacity-0 scale-75"
            x-transition:enter-end="opacity-100 scale-100"
            class="w-6 h-6 text-white"
            fill="none" stroke="currentColor" viewBox="0 0 24 24"
        >
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/>
        </svg>
        <svg
            x-show="abierto"
            x-transition:enter="transition duration-200"
            x-transition:enter-start="opacity-0 scale-75"
            x-transition:enter-end="opacity-100 scale-100"
            class="w-6 h-6 text-white"
            fill="none" stroke="currentColor" viewBox="0 0 24 24"
        >
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
        </svg>
    </button>
</div>
