<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Editar Usuario - Biblioteca Digital</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-[#13151f] text-white min-h-screen">

    @include('components.navigation')

    <main class="lg:ml-64 min-h-screen p-6 lg:p-10">

        <div class="flex items-center gap-3 mb-8">
            <a href="{{ route('usuarios.index') }}" class="text-white/40 hover:text-white transition-colors duration-200">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                </svg>
            </a>
            <h1 class="text-3xl font-bold text-white">Editar Usuario</h1>
        </div>

        <div class="max-w-lg">
            <div class="bg-[#1a1d2e] rounded-2xl p-8 border border-white/5">

                @if($errors->any())
                <div class="mb-6 px-4 py-3 bg-red-500/20 border border-red-500/30 rounded-xl text-red-400 text-sm">
                    @foreach($errors->all() as $error)
                        <p>{{ $error }}</p>
                    @endforeach
                </div>
                @endif

                <form method="POST" action="{{ route('usuarios.update', $usuario->id) }}">
                    @csrf
                    @method('PUT')

                    <div class="flex flex-col gap-6">

                        <div class="flex flex-col gap-2">
                            <label class="text-sm text-white/60">Nombre</label>
                            <input
                                type="text"
                                name="name"
                                value="{{ old('name', $usuario->name) }}"
                                required
                                class="bg-[#13151f] border border-white/10 rounded-xl px-4 py-3 text-sm text-white placeholder-white/20 focus:outline-none focus:border-indigo-500/50 transition-colors duration-200"
                            >
                        </div>

                        <div class="flex flex-col gap-2">
                            <label class="text-sm text-white/60">Correo electrónico</label>
                            <input
                                type="email"
                                name="email"
                                value="{{ old('email', $usuario->email) }}"
                                required
                                class="bg-[#13151f] border border-white/10 rounded-xl px-4 py-3 text-sm text-white placeholder-white/20 focus:outline-none focus:border-indigo-500/50 transition-colors duration-200"
                            >
                        </div>

                        <div class="flex gap-3">
                            <button
                                type="submit"
                                class="flex-1 py-3 rounded-xl bg-indigo-600 hover:bg-indigo-500 text-white text-sm font-medium transition-all duration-200"
                            >
                                Guardar cambios
                            </button>
                            <a
                                href="{{ route('usuarios.index') }}"
                                class="px-6 py-3 rounded-xl border border-white/10 hover:border-white/30 text-white/60 hover:text-white text-sm transition-all duration-200"
                            >
                                Cancelar
                            </a>
                        </div>

                    </div>
                </form>

            </div>
        </div>

    </main>

</body>
</html>