@extends('layouts.app')
@section('content')
    <div class="max-w-5xl mx-auto px-4 py-8">

        {{-- Formulario de búsqueda --}}
        <form method="GET" action="{{ route('open-library.buscar') }}" class="flex gap-3 mb-8">
            <input type="text" name="termino" value="{{ $termino ?? '' }}" placeholder="Buscar libro por título o autor..."
                class="flex-1 rounded-lg border border-gray-300 px-4 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500"
                required>
            <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-2 rounded-lg transition">
                Buscar
            </button>
        </form>

        {{-- Mensaje de éxito --}}
        @if (session('exito'))
            <div x-data="{ visible: true }" x-show="visible" x-transition x-init="setTimeout(() => visible = false, 4000)"
                class="bg-green-100 text-green-800 px-4 py-3 rounded-lg mb-6">
                {{ session('exito') }}
            </div>
        @endif

        {{-- Resultados --}}
        @isset($resultados)
            @if (count($resultados) === 0)
                <p class="text-gray-500 text-center">No se encontraron resultados para "{{ $termino }}".</p>
            @else
                <p class="text-sm text-gray-500 mb-4">{{ count($resultados) }} resultados para "{{ $termino }}"</p>

                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
                    @foreach ($resultados as $libro)
                        <div x-data="{ hover: false }" x-on:mouseenter="hover = true" x-on:mouseleave="hover = false"
                            :class="hover ? 'shadow-xl -translate-y-1' : 'shadow'"
                            class="bg-white rounded-xl overflow-hidden transition-all duration-200">
                            {{-- Portada --}}
                            <div class="h-48 bg-gray-100 flex items-center justify-center">
                                @if ($libro['portada_url'])
                                    <img src="{{ $libro['portada_url'] }}" alt="{{ $libro['titulo'] }}"
                                        class="h-full w-full object-cover">
                                @else
                                    <span class="text-gray-400 text-5xl">📚</span>
                                @endif
                            </div>

                            {{-- Info --}}
                            <div class="p-4">
                                <h3 class="font-semibold text-gray-800 line-clamp-2">{{ $libro['titulo'] }}</h3>
                                @if ($libro['autor'])
                                    <p class="text-sm text-gray-500 mt-1">{{ $libro['autor'] }}</p>
                                @endif
                                @if ($libro['anio_publicacion'])
                                    <p class="text-xs text-gray-400 mt-1">{{ $libro['anio_publicacion'] }}</p>
                                @endif

                                {{-- Formulario de importar --}}
                                <form method="POST" action="{{ route('open-library.importar') }}" class="mt-3">
                                    @csrf
                                    <input type="hidden" name="olid" value="{{ $libro['olid'] }}">
                                    <select name="categoria_id" class="w-full text-sm border rounded px-2 py-1 mb-2">
                                        @foreach ($categorias as $categoria)
                                            <option value="{{ $categoria->id }}">{{ $categoria->nombre }}</option>
                                        @endforeach
                                    </select>
                                    <button type="submit"
                                        class="w-full bg-blue-600 hover:bg-blue-700 text-white text-sm py-1.5 rounded transition">
                                        Importar libro
                                    </button>
                                </form>
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif
        @endisset
    </div>
@endsection
