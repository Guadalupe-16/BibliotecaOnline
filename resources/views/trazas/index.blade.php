<!DOCTYPE html>
<html lang="es" class="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Trazabilidad - Biblioteca Digital</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @livewireStyles
</head>
<body class="bg-[#13151f] text-white min-h-screen">

    @include('components.navigation')

    <main class="lg:ml-64 min-h-screen p-6 lg:p-8">
        <div class="mb-6">
            <h1 class="text-2xl font-bold text-white">Trazabilidad de solicitudes</h1>
            <p class="text-gray-400 text-sm mt-1">
                Comportamiento técnico de las peticiones HTTP (ruta, duración, status, errores). Para
                ver qué acción de negocio realizó un usuario, consulta el
                <a href="{{ route('admin.logs') }}" class="text-indigo-400 hover:text-indigo-300">Registro de actividad</a>.
            </p>
        </div>

        <livewire:trazabilidad-viewer />
    </main>

    <livewire:chat-soporte />

    @livewireScripts
</body>
</html>
