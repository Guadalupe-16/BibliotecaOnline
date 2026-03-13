@extends('layouts.app')

@section('content')
    <div x-data="{ visible: false }" x-init="setTimeout(() => visible = true, 50)" x-show="visible" x-transition:enter="transition ease-out duration-500"
        x-transition:enter-start="opacity-0 translate-y-4" x-transition:enter-end="opacity-100 translate-y-0"
        class="max-w-7xl mx-auto px-4 py-8">
        <div class="mb-8">
            <h1 class="text-3xl font-bold text-white">Buscar Libros</h1>
            <p class="text-gray-400 mt-1">Encuentra libros por título o autor</p>
        </div>

        @livewire('buscador-libros')
    </div>
@endsection
