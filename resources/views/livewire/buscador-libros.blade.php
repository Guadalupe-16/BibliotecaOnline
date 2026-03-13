<div>
    {{-- Campo de búsqueda --}}
    <div class="relative mb-8">
        <svg class="absolute left-4 top-1/2 -translate-y-1/2 w-5 h-5 text-gray-400" fill="none" stroke="currentColor"
            viewBox="0 0 
  24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
        </svg>
        <input type="text" wire:model.live.debounce.300ms="termino" placeholder="Buscar por título o autor..."
            class="w-full bg-[#1e2130] border border-white/10 rounded-xl pl-12 pr-4 py-3 text-white placeholder-gray-500
  focus:outline-none focus:ring-2 focus:ring-indigo-500 transition">
        @if ($termino)
            <button wire:click="$set('termino', '')"
                class="absolute right-4 top-1/2 -translate-y-1/2 text-gray-400 hover:text-white transition">✕</button>
        @endif
    </div>

    {{-- Resultados --}}
    @if ($libros->isEmpty())
        <div class="text-center py-20 text-gray-500">
            <p class="text-5xl mb-4">🔍</p>
            <p class="text-xl">No se encontraron libros para "{{ $termino }}".</p>
        </div>
    @else
        <p class="text-sm text-gray-500 mb-4">{{ $libros->total() }}
            {{ $libros->total() === 1 ? 'resultado' : 'resultados' }}</p>

        <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-6">
            @foreach ($libros as $libro)
                <a href="{{ route('libros.show', $libro) }}" class="block">
                    <div x-data="{ hover: false }" x-on:mouseenter="hover = true" x-on:mouseleave="hover = false"
                        :class="hover ? 'scale-105 shadow-2xl' : 'scale-100 shadow'"
                        class="bg-[#1e2130] border border-white/5 rounded-xl overflow-hidden transition-all duration-300">
                        {{-- Portada --}}
                        <div class="h-48 bg-[#2a2d3e] flex items-center justify-center overflow-hidden">
                            @if ($libro->portada_url)
                                <img src="{{ $libro->portada_url }}" alt="{{ $libro->titulo }}"
                                    class="h-full w-full object-cover">
                            @else
                                <span class="text-5xl">📖</span>
                            @endif
                        </div>

                        {{-- Info --}}
                        <div class="p-4">
                            <span class="text-xs font-semibold px-2 py-0.5 rounded-full"
                                style="background-color: {{ $libro->categoria->color }}30; color: {{ $libro->categoria->color }}">
                                {{ $libro->categoria->nombre }}
                            </span>
                            <h3 class="font-bold text-white mt-2 line-clamp-2">{{ $libro->titulo }}</h3>
                            <p class="text-sm text-gray-400 mt-1">{{ $libro->autor->nombre }}</p>
                            <div class="mt-3">
                                @if ($libro->estaDisponible())
                                    <span class="text-xs text-green-400">✓ Disponible
                                        ({{ $libro->copias_disponibles }})</span>
                                @else
                                    <span class="text-xs text-red-400">✗ No disponible</span>
                                @endif
                            </div>
                        </div>
                    </div>
                </a>
            @endforeach
        </div>

        <div class="mt-8">
            {{ $libros->links() }}
        </div>
    @endif
</div>
