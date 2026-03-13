@extends('layouts.app')

@section('content')
    <div class="max-w-7xl mx-auto px-4 py-8">

        {{-- Encabezado --}}
        <div class="mb-8">
            <h1 class="text-3xl font-bold text-white">Catálogo de Libros</h1>
            <p class="text-gray-400 mt-1">{{ $libros->total() }} libros disponibles</p>
        </div>

        {{-- Grid de tarjetas --}}
        @if ($libros->isEmpty())
            <div class="text-center text-gray-500 py-20">
                <p class="text-5xl mb-4">📚</p>
                <p class="text-xl">No hay libros en el catálogo aún.</p>
            </div>
        @else
            <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-6">
                @foreach ($libros as $libro)
                    <div x-data="{ hover: false }" x-on:mouseenter="hover = true" x-on:mouseleave="hover = false"
                        :class="hover ? 'scale-105 shadow-2xl' : 'scale-100 shadow'"
                        class="bg-[#1e2130] rounded-xl overflow-hidden transition-all duration-300 cursor-pointer">
                        {{-- Portada --}}
                        <div class="h-52 bg-[#2a2d3e] flex items-center justify-center overflow-hidden">
                            @if ($libro->portada_url)
                                <img src="{{ $libro->portada_url }}" alt="{{ $libro->titulo }}"
                                    class="h-full w-full object-cover">
                            @else
                                <span class="text-6xl">📖</span>
                            @endif
                        </div>

                        {{-- Info --}}
                        <div class="p-4">
                            {{-- Categoría --}}
                            <span class="text-xs font-semibold px-2 py-0.5 rounded-full"
                                style="background-color: {{ $libro->categoria->color }}30; color: {{ $libro->categoria->color }}">
                                {{ $libro->categoria->nombre }}
                            </span>

                            <h3 class="font-bold text-white mt-2 line-clamp-2">{{ $libro->titulo }}</h3>
                            <p class="text-sm text-gray-400 mt-1">{{ $libro->autor->nombre }}</p>

                            @if ($libro->anio_publicacion)
                                <p class="text-xs text-gray-500 mt-1">{{ $libro->anio_publicacion }}</p>
                            @endif

                            {{-- Disponibilidad --}}
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
                @endforeach
            </div>

            {{-- Paginación --}}
            <div class="mt-8">
                {{ $libros->links() }}
            </div>
        @endif

    </div>
@endsection
