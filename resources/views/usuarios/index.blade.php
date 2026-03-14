<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Usuarios - Biblioteca Digital</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-[#13151f] text-white min-h-screen">

    @include('components.navigation')

    <main class="lg:ml-64 min-h-screen p-6 lg:p-10">

        <div class="flex items-center justify-between mb-8">
            <div>
                <h1 class="text-3xl font-bold text-white">Usuarios</h1>
                <p class="text-white/50 mt-1">Gestión de usuarios del sistema</p>
            </div>
        </div>

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

        <div class="bg-[#1a1d2e] rounded-2xl border border-white/5 overflow-hidden">
            <table class="w-full">
                <thead>
                    <tr class="border-b border-white/5">
                        <th class="text-left px-6 py-4 text-xs font-semibold tracking-widest text-white/30 uppercase">Usuario</th>
                        <th class="text-left px-6 py-4 text-xs font-semibold tracking-widest text-white/30 uppercase">Correo</th>
                        <th class="text-left px-6 py-4 text-xs font-semibold tracking-widest text-white/30 uppercase">Registro</th>
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
                                <span class="text-sm text-white">{{ $usuario->name }}</span>
                            </div>
                        </td>
                        <td class="px-6 py-4 text-sm text-white/60">{{ $usuario->email }}</td>
                        <td class="px-6 py-4 text-sm text-white/40">{{ $usuario->created_at->format('d/m/Y') }}</td>
                        <td class="px-6 py-4">
                            <div class="flex items-center justify-end gap-2">
                                <a href="{{ route('usuarios.edit', $usuario->id) }}" class="px-3 py-1.5 rounded-lg text-xs text-white/60 border border-white/10 hover:border-indigo-500/50 hover:text-indigo-400 transition-all duration-200">
                                    Editar
                                </a>
                                <form method="POST" action="{{ route('usuarios.destroy', $usuario->id) }}"
                                    x-data
                                    x-on:submit.prevent="if(confirm('¿Eliminar a {{ $usuario->name }}?')) $el.submit()"
                                >
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="px-3 py-1.5 rounded-lg text-xs text-white/60 border border-white/10 hover:border-red-500/50 hover:text-red-400 transition-all duration-200">
                                        Eliminar
                                    </button>
                                </form>
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