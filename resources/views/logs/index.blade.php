<!DOCTYPE html>
<html lang="es" class="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registro de Actividad - Biblioteca Digital</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @livewireStyles
</head>
<body class="bg-[#13151f] text-white min-h-screen">

    @include('components.navigation')

    <main class="lg:ml-64 min-h-screen p-6 lg:p-8">
        <div class="mb-6">
            <h1 class="text-2xl font-bold text-white">Registro de Actividad</h1>
            <p class="text-gray-400 text-sm mt-1">Historial de acciones realizadas en el sistema.</p>
        </div>

        <livewire:activity-logger />
    </main>

    <livewire:chat-soporte />

    @livewireScripts
</body>
</html>
