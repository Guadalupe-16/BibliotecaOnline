<!DOCTYPE html>
<html lang="es" class="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $titulo ?? 'Biblioteca Digital' }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-[#13151f] text-white min-h-screen">

    {{-- Menú lateral --}}
    @include('components.navigation')

    {{-- Contenido principal --}}
    <main class="lg:ml-64 min-h-screen p-6 lg:p-8">
        @yield('content')
    </main>

</body>
</html>
