@extends('layouts.app')

@section('content')
    <div x-data="{ visible: false }" x-init="setTimeout(() => visible = true, 50)" x-show="visible" x-transition:enter="transition ease-out duration-500"
        x-transition:enter-start="opacity-0 translate-y-4" x-transition:enter-end="opacity-100 translate-y-0"
        class="max-w-4xl mx-auto px-4 py-8">
        {{-- Botón volver --}}
        <a href="{{ route('catalogo') }}"
            class="inline-flex items-center gap-2 text-sm text-gray-400 hover:text-white transition mb-6">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
            </svg>
            Volver al catálogo
        </a>

        <div class="bg-[#1e2130] rounded-2xl overflow-hidden shadow-xl flex flex-col md:flex-row gap-0">

            {{-- Portada --}}
            <div class="md:w-64 flex-shrink-0 bg-[#2a2d3e] flex items-center justify-center min-h-64">
                @if ($libro->portada_url)
                    <img src="{{ $libro->portada_url }}" alt="{{ $libro->titulo }}" class="w-full h-full object-cover">
                @else
                    <span class="text-8xl">📖</span>
                @endif
            </div>

            {{-- Información --}}
            <div class="flex-1 p-8">

                {{-- Categoría --}}
                <span class="text-xs font-semibold px-3 py-1 rounded-full"
                    style="background-color: {{ $libro->categoria->color }}30; color: {{ $libro->categoria->color }}">
                    {{ $libro->categoria->nombre }}
                </span>

                {{-- Título --}}
                <h1 class="text-3xl font-bold text-white mt-3">{{ $libro->titulo }}</h1>

                {{-- Autor --}}
                <p class="text-lg text-gray-400 mt-1">{{ $libro->autor->nombre }}</p>

                {{-- Año --}}
                @if ($libro->anio_publicacion)
                    <p class="text-sm text-gray-500 mt-1">{{ $libro->anio_publicacion }}</p>
                @endif

                {{-- Disponibilidad --}}
                <div class="mt-4">
                    @if ($libro->estaDisponible())
                        <span
                            class="inline-flex items-center gap-2 text-sm text-green-400 bg-green-400/10 px-3 py-1 rounded-full">
                            ✓ Disponible — {{ $libro->copias_disponibles }}
                            {{ $libro->copias_disponibles === 1 ? 'copia' : 'copias' }}
                        </span>
                    @else
                        <span
                            class="inline-flex items-center gap-2 text-sm text-red-400 bg-red-400/10 px-3 py-1 rounded-full">
                            ✗ No disponible
                        </span>
                    @endif
                </div>

                {{-- Favorito --}}
                @auth
                    @php $esFav = auth()->user()->esFavorito($libro->id); @endphp
                    <div class="mt-4" x-data="{ esFavorito: {{ $esFav ? 'true' : 'false' }} }">
                        <button
                            x-on:click="
                                fetch('/favoritos/{{ $libro->id }}/toggle', {
                                    method: 'POST',
                                    headers: {
                                        'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content,
                                        'Content-Type': 'application/json'
                                    }
                                }).then(r => r.json()).then(d => esFavorito = d.esFavorito)
                            "
                            x-bind:class="esFavorito ? 'text-pink-400 bg-pink-400/20 border-pink-400/30' : 'text-gray-400 bg-white/5 border-white/10 hover:text-pink-400'"
                            class="inline-flex items-center gap-2 text-sm px-4 py-2 rounded-full border transition-all duration-200"
                        >
                            <svg x-bind:class="esFavorito ? 'fill-current' : ''" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"/>
                            </svg>
                            <span x-text="esFavorito ? 'Quitar de favoritos' : 'Agregar a favoritos'"></span>
                        </button>
                    </div>
                @endauth

                {{-- Descripción --}}
                @if ($libro->descripcion)
                    <div class="mt-6">
                        <h2 class="text-sm font-semibold text-gray-400 uppercase tracking-wider mb-2">Descripción</h2>
                        <p class="text-gray-300 leading-relaxed">{{ $libro->descripcion }}</p>
                    </div>
                @endif

                {{-- Info del autor --}}
                @if ($libro->autor->biografia)
                    <div class="mt-6 border-t border-white/5 pt-6">
                        <h2 class="text-sm font-semibold text-gray-400 uppercase tracking-wider mb-2">Sobre el autor</h2>
                        <p class="text-gray-400 text-sm leading-relaxed">{{ $libro->autor->biografia }}</p>
                    </div>
                @endif

            </div>
        </div>
    </div>
@endsection
