<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestión de Roles - Biblioteca Digital</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Sora:wght@300;400;500;600;700&display=swap');
        body { font-family: 'Sora', sans-serif; }
    </style>
</head>
<body class="min-h-screen px-4 py-8"
      style="background: radial-gradient(ellipse at 60% 20%, #1a1a2e 0%, #0d0d14 70%);">

    <div class="absolute inset-0 overflow-hidden pointer-events-none">
        <div class="absolute top-20 left-20 w-64 h-64 bg-purple-600/10 rounded-full blur-3xl"></div>
        <div class="absolute bottom-20 right-20 w-80 h-80 bg-blue-600/10 rounded-full blur-3xl"></div>
    </div>

    <div
        x-data="{ cargado: false }"
        x-init="setTimeout(() => cargado = true, 100)"
        x-show="cargado"
        x-transition:enter="transition ease-out duration-700"
        x-transition:enter-start="opacity-0 translate-y-6"
        x-transition:enter-end="opacity-100 translate-y-0"
        class="relative max-w-5xl mx-auto"
    >
        {{-- Encabezado --}}
        <div class="flex items-center justify-between mb-8">
            <div>
                <h1 class="text-white text-2xl font-semibold">Gestión de Roles</h1>
                <p class="text-white/40 text-sm mt-1">Asigna y administra los roles de los usuarios</p>
            </div>
            <a href="{{ route('catalogo') }}"
               class="text-white/40 hover:text-white/70 text-sm transition-colors duration-200 flex items-center gap-2">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                </svg>
                Volver al catálogo
            </a>
        </div>

        {{-- Mensajes --}}
        @if(session('success'))
            <div x-data="{ visible: true }" x-show="visible" x-transition
                 class="mb-6 p-4 bg-green-500/10 border border-green-500/30 rounded-xl text-green-400 text-sm flex items-center gap-3">
                <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                {{ session('success') }}
            </div>
        @endif

        @if(session('error'))
            <div x-data="{ visible: true }" x-show="visible" x-transition
                 class="mb-6 p-4 bg-red-500/10 border border-red-500/30 rounded-xl text-red-400 text-sm flex items-center gap-3">
                <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                {{ session('error') }}
            </div>
        @endif

        {{-- Resumen de roles --}}
        <div class="grid grid-cols-3 gap-4 mb-8">
            @foreach(['usuario' => ['color' => 'blue', 'icono' => 'M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z'], 'admin' => ['color' => 'purple', 'icono' => 'M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z'], 'superadmin' => ['color' => 'pink', 'icono' => 'M5 3v4M3 5h4M6 17v4m-2-2h4m5-16l2.286 6.857L21 12l-5.714 2.143L13 21l-2.286-6.857L5 12l5.714-2.143L13 3z']] as $rol => $config)
                <div class="bg-[#13131f]/90 border border-white/10 rounded-xl p-4">
                    <div class="flex items-center gap-3 mb-2">
                        <div class="w-8 h-8 rounded-lg flex items-center justify-center bg-{{ $config['color'] }}-500/20">
                            <svg class="w-4 h-4 text-{{ $config['color'] }}-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="{{ $config['icono'] }}"/>
                            </svg>
                        </div>
                        <span class="text-white/60 text-sm capitalize">{{ $rol }}</span>
                    </div>
                    <p class="text-white text-2xl font-bold">
                        {{ $usuarios->where('rol', $rol)->count() }}
                    </p>
                </div>
            @endforeach
        </div>

        {{-- Tabla de usuarios --}}
        <div class="bg-[#13131f]/90 backdrop-blur-xl border border-white/10 rounded-2xl overflow-hidden shadow-2xl shadow-black/50">
            <div class="p-6 border-b border-white/10">
                <h2 class="text-white font-medium">Usuarios del sistema</h2>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead>
                        <tr class="border-b border-white/5">
                            <th class="text-left text-white/40 text-xs uppercase tracking-widest font-medium px-6 py-4">Usuario</th>
                            <th class="text-left text-white/40 text-xs uppercase tracking-widest font-medium px-6 py-4">Correo</th>
                            <th class="text-left text-white/40 text-xs uppercase tracking-widest font-medium px-6 py-4">Rol actual</th>
                            <th class="text-left text-white/40 text-xs uppercase tracking-widest font-medium px-6 py-4">Verificado</th>
                            <th class="text-left text-white/40 text-xs uppercase tracking-widest font-medium px-6 py-4">Cambiar rol</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-white/5">
                        @foreach($usuarios as $usuario)
                            <tr class="hover:bg-white/[0.02] transition-colors duration-150"
                                x-data="{ hovering: false }"
                                @mouseenter="hovering = true"
                                @mouseleave="hovering = false">

                                <td class="px-6 py-4">
                                    <div class="flex items-center gap-3">
                                        <div class="w-8 h-8 rounded-full flex items-center justify-center text-white text-xs font-bold flex-shrink-0"
                                             style="background: linear-gradient(135deg, #6366f1, #8b5cf6);">
                                            {{ strtoupper(substr($usuario->name, 0, 1)) }}
                                        </div>
                                        <span class="text-white text-sm">
                                            {{ $usuario->name }}
                                            @if($usuario->id === auth()->id())
                                                <span class="text-indigo-400 text-xs ml-1">(tú)</span>
                                            @endif
                                        </span>
                                    </div>
                                </td>

                                <td class="px-6 py-4 text-white/50 text-sm">{{ $usuario->email }}</td>

                                <td class="px-6 py-4">
                                    @php
                                        $colores = ['usuario' => 'blue', 'admin' => 'purple', 'superadmin' => 'pink'];
                                        $color = $colores[$usuario->rol] ?? 'gray';
                                    @endphp
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                                                 bg-{{ $color }}-500/15 text-{{ $color }}-400 border border-{{ $color }}-500/30">
                                        {{ $usuario->rol }}
                                    </span>
                                </td>

                                <td class="px-6 py-4">
                                    @if($usuario->email_verified_at)
                                        <span class="text-green-400 text-xs flex items-center gap-1">
                                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                                            </svg>
                                            Verificado
                                        </span>
                                    @else
                                        <span class="text-yellow-500/70 text-xs">Pendiente</span>
                                    @endif
                                </td>

                                <td class="px-6 py-4">
                                    @if($usuario->id !== auth()->id())
                                        <form method="POST" action="{{ route('admin.rbac.actualizar', $usuario->id) }}"
                                              class="flex items-center gap-2">
                                            @csrf
                                            @method('PUT')
                                            <select name="rol"
                                                    class="bg-white/5 border border-white/10 text-white text-xs rounded-lg px-3 py-1.5
                                                           focus:border-indigo-500/60 focus:ring-1 focus:ring-indigo-500/30 outline-none
                                                           transition-all duration-200 cursor-pointer">
                                                @foreach(App\Models\User::rolesDisponibles() as $rol)
                                                    <option value="{{ $rol }}" {{ $usuario->rol === $rol ? 'selected' : '' }}
                                                            class="bg-[#13131f]">
                                                        {{ $rol }}
                                                    </option>
                                                @endforeach
                                            </select>
                                            <button type="submit"
                                                    class="px-3 py-1.5 rounded-lg text-white text-xs font-medium
                                                           transition-all duration-200 hover:scale-105 active:scale-95"
                                                    style="background: linear-gradient(135deg, #6366f1, #8b5cf6);">
                                                Guardar
                                            </button>
                                        </form>
                                    @else
                                        <span class="text-white/20 text-xs">—</span>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
</body>
</html>
