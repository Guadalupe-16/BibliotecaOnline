<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Biblioteca Digital</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-[#13151f] text-white min-h-screen">

    @include('components.navigation')

    <main class="lg:ml-64 min-h-screen p-6 lg:p-8">
        <h1 class="text-2xl font-bold text-white">Bienvenido</h1>
    </main>

    <livewire:chat-soporte />
</body>
</html>