<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Panel Superadmin - Biblioteca Digital</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>[x-cloak] { display: none !important; }</style>
</head>
<script>
function toggleDropdown(btn) {
    const menu = btn.nextElementSibling;
    menu.classList.toggle('hidden');

    // Cerrar al hacer clic fuera
    document.addEventListener('click', function closeMenu(e) {
        if (!btn.contains(e.target)) {
            menu.classList.add('hidden');
            document.removeEventListener('click', closeMenu);
        }
    });
}
</script>
<body class="bg-[#13151f] text-white min-h-screen">

    @include('components.navigation')

    <main class="lg:ml-64 min-h-screen p-6 lg:p-10">

        {{-- Encabezado --}}
        <div class="mb-8">
            <h1 class="text-3xl font-bold text-white">Panel Superadministrador</h1>
            <p class="text-white/50 mt-1">Gestión completa del sistema</p>
        </div>

        {{-- Mensajes --}}
        @if(session('success'))
        <div
            x-data="{ visible: true }"
            x-show="visible"
            x-init="setTimeout(() => visible = false, 3000)"
            class="mb-6 px-4 py-3 bg-green-500/20 border border-green-500/30 rounded-xl text-green-400 text-sm"
        >
            {{ session('success') }}
        </div>
        @endif

        @if(session('error'))
        <div class="mb-6 px-4 py-3 bg-red-500/20 border border-red-500/30 rounded-xl text-red-400 text-sm">
            {{ session('error') }}
        </div>
        @endif

        {{-- Tarjetas de resumen --}}
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 mb-8">
            <div class="bg-[#1a1d2e] rounded-2xl p-6 border border-white/5">
                <p class="text-white/40 text-sm">Total usuarios</p>
                <p class="text-3xl font-bold text-white mt-1">{{ $total_usuarios }}</p>
            </div>
            <div class="bg-[#1a1d2e] rounded-2xl p-6 border border-white/5">
                <p class="text-white/40 text-sm">Administradores</p>
                <p class="text-3xl font-bold text-indigo-400 mt-1">{{ $total_admins }}</p>
            </div>
            <div class="bg-[#1a1d2e] rounded-2xl p-6 border border-white/5">
                <p class="text-white/40 text-sm">Superadmins</p>
                <p class="text-3xl font-bold text-purple-400 mt-1">{{ $total_superadmins }}</p>
            </div>
        </div>

        {{-- Tabla de usuarios --}}
        <div class="bg-[#1a1d2e] rounded-2xl border border-white/5 overflow-visible">
            <div class="px-6 py-4 border-b border-white/5">
                <h2 class="text-lg font-semibold text-white">Usuarios del sistema</h2>
            </div>
            <table class="w-full">
                <thead>
                    <tr class="border-b border-white/5">
                        <th class="text-left px-6 py-4 text-xs font-semibold tracking-widest text-white/30 uppercase">Usuario</th>
                        <th class="text-left px-6 py-4 text-xs font-semibold tracking-widest text-white/30 uppercase">Rol</th>
                        <th class="text-left px-6 py-4 text-xs font-semibold tracking-widest text-white/30 uppercase">Estado</th>
                        <th class="text-right px-6 py-4 text-xs font-semibold tracking-widest text-white/30 uppercase">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($usuarios as $usuario)
                    <tr class="border-b border-white/5 hover:bg-white/2 transition-colors duration-200">
                        <td class="px-6 py-4">
                            <div class="flex items-center gap-3">
                                <div class="w-8 h-8 rounded-full bg-indigo-600 flex items-center justify-center text-white text-xs font-bold">
                                    {{ strtoupper(substr($usuario->name, 0, 1)) }}
                                </div>
                                <div>
                                    <p class="text-sm text-white">{{ $usuario->name }}</p>
                                    <p class="text-xs text-white/40">{{ $usuario->email }}</p>
                                </div>
                            </div>
                        </td>
                        <td class="px-6 py-4">
                            @php
                                $colores = [
                                    'superadmin' => 'bg-purple-500/20 text-purple-400',
                                    'admin' => 'bg-indigo-500/20 text-indigo-400',
                                    'usuario' => 'bg-white/10 text-white/50',
                                ];
                            @endphp
                            <span class="px-2 py-1 rounded-lg text-xs font-medium {{ $colores[$usuario->rol] ?? 'bg-white/10 text-white/50' }}">
                                {{ $usuario->rol }}
                            </span>
                        </td>
                        <td class="px-6 py-4">
                            <span class="px-2 py-1 rounded-lg text-xs font-medium {{ $usuario->activo ? 'bg-green-500/20 text-green-400' : 'bg-red-500/20 text-red-400' }}">
                                {{ $usuario->activo ? 'Activo' : 'Inactivo' }}
                            </span>
                        </td>
                        <td class="px-6 py-4">
                            <div class="flex items-center justify-end gap-2">

                                {{-- Cambiar rol --}}
                                <div class="relative">
                                    <button
                                        onclick="toggleDropdown(this)"
                                        class="px-3 py-1.5 rounded-lg text-xs text-white/60 border border-white/10 hover:border-indigo-500/50 hover:text-indigo-400 transition-all duration-200"
                                    >
                                        Cambiar rol
                                    </button>
                                    <div
                                        class="dropdown-menu hidden absolute right-0 mt-2 w-36 bg-[#0f1117] border border-white/10 rounded-xl overflow-hidden z-10"
                                    >
                                        @foreach(['usuario', 'admin', 'superadmin'] as $opcionRol)
                                        <form method="POST" action="{{ route('superadmin.cambiarRol', [$usuario->id, $opcionRol]) }}">
                                            @csrf
                                            <button type="submit" class="w-full text-left px-4 py-2 text-xs text-white/60 hover:bg-white/5 hover:text-white transition-colors duration-200">
                                                {{ $opcionRol }}
                                            </button>
                                        </form>
                                        @endforeach
                                    </div>
                                </div>

                                {{-- Toggle estado --}}
                                @if($usuario->id !== auth()->id())
                                <form method="POST" action="{{ route('superadmin.toggleEstado', $usuario->id) }}">
                                    @csrf
                                    <button
                                        type="submit"
                                        class="px-3 py-1.5 rounded-lg text-xs border transition-all duration-200 {{ $usuario->activo ? 'text-red-400 border-red-500/20 hover:border-red-500/50' : 'text-green-400 border-green-500/20 hover:border-green-500/50' }}"
                                    >
                                        {{ $usuario->activo ? 'Desactivar' : 'Activar' }}
                                    </button>
                                </form>
                                @endif

                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="4" class="px-6 py-12 text-center text-white/30 text-sm">
                            No hay usuarios registrados
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

    </main>

</body>
</html>