<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mi Perfil - Biblioteca Digital</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-[#13151f] text-white min-h-screen">

    @include('components.navigation')

    <main class="lg:ml-64 min-h-screen p-6 lg:p-10">

        <div class="mb-8">
            <h1 class="text-3xl font-bold text-white">Mi Perfil</h1>
            <p class="text-white/50 mt-1">Actualiza tu información personal</p>
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

        @if($errors->any())
        <div class="mb-6 px-4 py-3 bg-red-500/20 border border-red-500/30 rounded-xl text-red-400 text-sm">
            @foreach($errors->all() as $error)
                <p>{{ $error }}</p>
            @endforeach
        </div>
        @endif

        <div class="max-w-lg">
            <form
                method="POST"
                action="{{ route('perfil.actualizar') }}"
                enctype="multipart/form-data"
                class="bg-[#1a1d2e] rounded-2xl p-8 border border-white/5"
            >
                @csrf

                {{-- Foto de perfil --}}
                <div class="flex flex-col items-center mb-8">
                    <div class="relative">
                        @if($usuario->foto)
                            <img
                                src="{{ asset('storage/' . $usuario->foto) }}"
                                alt="Foto de perfil"
                                class="w-24 h-24 rounded-full object-cover border-2 border-indigo-500/50"
                                id="preview-foto"
                            >
                        @else
                            <div
                                class="w-24 h-24 rounded-full bg-indigo-600 flex items-center justify-center text-white text-3xl font-bold border-2 border-indigo-500/50"
                                id="avatar-inicial"
                            >
                                {{ strtoupper(substr($usuario->name, 0, 1)) }}
                            </div>
                            <img
                                src=""
                                alt="Foto de perfil"
                                class="w-24 h-24 rounded-full object-cover border-2 border-indigo-500/50 hidden"
                                id="preview-foto"
                            >
                        @endif

                        {{-- Botón de cambiar foto --}}
                        <label
                            for="foto"
                            class="absolute bottom-0 right-0 w-8 h-8 bg-indigo-600 hover:bg-indigo-500 rounded-full flex items-center justify-center cursor-pointer transition-colors duration-200"
                        >
                            <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z"/>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 13a3 3 0 11-6 0 3 3 0 016 0z"/>
                            </svg>
                        </label>
                        <input
                            type="file"
                            name="foto"
                            id="foto"
                            accept="image/jpeg,image/png,image/webp"
                            class="hidden"
                            onchange="previewFoto(this)"
                        >
                    </div>
                    <p class="text-white/30 text-xs mt-3">JPG, PNG o WEBP — máx. 2MB</p>
                </div>

                {{-- Nombre --}}
                <div class="flex flex-col gap-2 mb-6">
                    <label class="text-sm text-white/60">Nombre</label>
                    <input
                        type="text"
                        name="name"
                        value="{{ old('name', $usuario->name) }}"
                        required
                        class="bg-[#13151f] border border-white/10 rounded-xl px-4 py-3 text-sm text-white placeholder-white/20 focus:outline-none focus:border-indigo-500/50 transition-colors duration-200"
                    >
                </div>

                {{-- Email (solo lectura) --}}
                <div class="flex flex-col gap-2 mb-8">
                    <label class="text-sm text-white/60">Correo electrónico</label>
                    <input
                        type="email"
                        value="{{ $usuario->email }}"
                        disabled
                        class="bg-[#13151f] border border-white/5 rounded-xl px-4 py-3 text-sm text-white/30 cursor-not-allowed"
                    >
                    <p class="text-white/20 text-xs">El correo no se puede cambiar</p>
                </div>

                <button
                    type="submit"
                    class="w-full py-3 rounded-xl bg-indigo-600 hover:bg-indigo-500 text-white text-sm font-medium transition-all duration-200"
                >
                    Guardar cambios
                </button>

            </form>
        </div>

    </main>

    <script>
        function previewFoto(input) {
            if (input.files && input.files[0]) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    const preview = document.getElementById('preview-foto');
                    const inicial = document.getElementById('avatar-inicial');
                    preview.src = e.target.result;
                    preview.classList.remove('hidden');
                    if (inicial) inicial.classList.add('hidden');
                };
                reader.readAsDataURL(input.files[0]);
            }
        }
    </script>

</body>
</html>