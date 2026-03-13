<nav x-data="{ menuAbierto: false, categoriasAbiertas: false }">

    {{-- ===== SIDEBAR ESCRITORIO ===== --}}
    <aside class="hidden lg:flex flex-col fixed top-0 left-0 w-64 h-full bg-[#0f1117] border-r border-white/5 px-4 py-6 gap-6 z-40">

        {{-- Logo --}}
        <div class="flex items-center gap-3 px-2 mb-2">
            <div class="w-9 h-9 rounded-lg bg-indigo-600 flex items-center justify-center">
                <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.746 0 3.332.477 4.5 1.253v13C19.832 18.477 18.246 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/>
                </svg>
            </div>
            <span class="text-white font-semibold text-sm leading-tight">Biblioteca<br>Digital</span>
        </div>

        {{-- Sección Principal --}}
        <div class="flex flex-col gap-1">
            <span class="text-[10px] font-semibold tracking-widest text-white/30 uppercase px-3 mb-1">Principal</span>

            <a href="{{ route('catalogo') }}"
                x-on:mouseenter="$el.classList.add('bg-white/5', 'text-white')"
                x-on:mouseleave="$el.classList.remove('bg-white/5', 'text-white')"
                class="flex items-center gap-3 px-3 py-2 rounded-lg text-sm text-white/60 transition-all duration-200">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/>
                </svg>
                Catálogo
            </a>

            <a href="{{ route('open-library.index') }}"
                x-on:mouseenter="$el.classList.add('bg-white/5', 'text-white')"
                x-on:mouseleave="$el.classList.remove('bg-white/5', 'text-white')"
                class="flex items-center gap-3 px-3 py-2 rounded-lg text-sm text-white/60 transition-all duration-200">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                </svg>
                Buscar
            </a>

            <a href="#"
                x-on:mouseenter="$el.classList.add('bg-white/5', 'text-white')"
                x-on:mouseleave="$el.classList.remove('bg-white/5', 'text-white')"
                class="flex items-center gap-3 px-3 py-2 rounded-lg text-sm text-white/60 transition-all duration-200">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"/>
                </svg>
                Favoritos
            </a>

            <a href="#"
                x-on:mouseenter="$el.classList.add('bg-white/5', 'text-white')"
                x-on:mouseleave="$el.classList.remove('bg-white/5', 'text-white')"
                class="flex items-center gap-3 px-3 py-2 rounded-lg text-sm text-white/60 transition-all duration-200">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.746 0 3.332.477 4.5 1.253v13C19.832 18.477 18.246 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/>
                </svg>
                Mis lecturas
            </a>
        </div>

        {{-- Sección Categorías con dropdown animado --}}
        <div class="flex flex-col gap-1" x-on:mouseleave="categoriasAbiertas = false">
            <button
                x-on:mouseenter="categoriasAbiertas = true"
                class="flex items-center justify-between text-[10px] font-semibold tracking-widest text-white/30 uppercase px-3 mb-1 hover:text-white/50 transition-colors duration-200 w-full"
            >
                <span>Categorías</span>
                <svg x-bind:class="categoriasAbiertas ? 'rotate-180' : ''" class="w-3 h-3 transition-transform duration-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                </svg>
            </button>

            <div
                x-show="categoriasAbiertas"
                x-transition:enter="transition ease-out duration-200"
                x-transition:enter-start="opacity-0 -translate-y-2"
                x-transition:enter-end="opacity-100 translate-y-0"
                x-transition:leave="transition ease-in duration-150"
                x-transition:leave-start="opacity-100 translate-y-0"
                x-transition:leave-end="opacity-0 -translate-y-2"
                class="flex flex-col gap-1"
            >
                <a href="#" x-on:mouseenter="$el.classList.add('bg-white/5','text-white')" x-on:mouseleave="$el.classList.remove('bg-white/5','text-white')" class="flex items-center gap-3 px-3 py-2 rounded-lg text-sm text-white/60 transition-all duration-200"><span>🔬</span> Ciencias</a>
                <a href="#" x-on:mouseenter="$el.classList.add('bg-white/5','text-white')" x-on:mouseleave="$el.classList.remove('bg-white/5','text-white')" class="flex items-center gap-3 px-3 py-2 rounded-lg text-sm text-white/60 transition-all duration-200"><span>📐</span> Matemáticas</a>
                <a href="#" x-on:mouseenter="$el.classList.add('bg-white/5','text-white')" x-on:mouseleave="$el.classList.remove('bg-white/5','text-white')" class="flex items-center gap-3 px-3 py-2 rounded-lg text-sm text-white/60 transition-all duration-200"><span>📜</span> Historia</a>
                <a href="#" x-on:mouseenter="$el.classList.add('bg-white/5','text-white')" x-on:mouseleave="$el.classList.remove('bg-white/5','text-white')" class="flex items-center gap-3 px-3 py-2 rounded-lg text-sm text-white/60 transition-all duration-200"><span>💻</span> Tecnología</a>
                <a href="#" x-on:mouseenter="$el.classList.add('bg-white/5','text-white')" x-on:mouseleave="$el.classList.remove('bg-white/5','text-white')" class="flex items-center gap-3 px-3 py-2 rounded-lg text-sm text-white/60 transition-all duration-200"><span>📖</span> Literatura</a>
            </div>
        </div>

        {{-- Spacer --}}
        <div class="flex-1"></div>

        {{-- Sección Cuenta --}}
        <div class="flex flex-col gap-1">
            <span class="text-[10px] font-semibold tracking-widest text-white/30 uppercase px-3 mb-1">Cuenta</span>
            @auth
                <div class="flex items-center gap-3 px-3 py-2 mb-1">
                    <div class="w-8 h-8 rounded-full bg-indigo-500 flex items-center justify-center text-white text-xs font-bold">
                        {{ strtoupper(substr(auth()->user()->name, 0, 1)) }}
                    </div>
                    <span class="text-sm text-white/80 truncate">{{ auth()->user()->name }}</span>
                </div>
                <a href="#" x-on:mouseenter="$el.classList.add('bg-white/5','text-white')" x-on:mouseleave="$el.classList.remove('bg-white/5','text-white')" class="flex items-center gap-3 px-3 py-2 rounded-lg text-sm text-white/60 transition-all duration-200">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                    Mi perfil
                </a>
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" x-on:mouseenter="$el.classList.add('bg-red-500/10','text-red-400')" x-on:mouseleave="$el.classList.remove('bg-red-500/10','text-red-400')" class="w-full flex items-center gap-3 px-3 py-2 rounded-lg text-sm text-white/60 transition-all duration-200">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/></svg>
                        Cerrar sesión
                    </button>
                </form>
            @else
                <a href="{{ route('login') }}" x-on:mouseenter="$el.classList.add('bg-white/5','text-white')" x-on:mouseleave="$el.classList.remove('bg-white/5','text-white')" class="flex items-center gap-3 px-3 py-2 rounded-lg text-sm text-white/60 transition-all duration-200">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/></svg>
                    Iniciar sesión
                </a>
                <a href="{{ route('register') }}" x-on:mouseenter="$el.classList.add('bg-indigo-500/20','text-indigo-300')" x-on:mouseleave="$el.classList.remove('bg-indigo-500/20','text-indigo-300')" class="flex items-center gap-3 px-3 py-2 rounded-lg text-sm text-indigo-400 transition-all duration-200">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"/></svg>
                    Registrarse
                </a>
            @endauth
        </div>
    </aside>

    {{-- ===== BOTÓN HAMBURGUESA MÓVIL ===== --}}
    <button
        x-on:click="menuAbierto = !menuAbierto"
        class="lg:hidden fixed top-4 left-4 z-50 w-10 h-10 bg-[#0f1117] border border-white/10 rounded-lg flex items-center justify-center text-white/70 hover:text-white transition-colors duration-200"
    >
        <svg x-show="!menuAbierto" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/>
        </svg>
        <svg x-show="menuAbierto" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
        </svg>
    </button>

    {{-- ===== OVERLAY MÓVIL ===== --}}
    <div
        x-show="menuAbierto"
        x-on:click="menuAbierto = false"
        x-transition:enter="transition ease-out duration-200"
        x-transition:enter-start="opacity-0"
        x-transition:enter-end="opacity-100"
        x-transition:leave="transition ease-in duration-150"
        x-transition:leave-start="opacity-100"
        x-transition:leave-end="opacity-0"
        class="lg:hidden fixed inset-0 bg-black/60 backdrop-blur-sm z-40"
    ></div>

    {{-- ===== SIDEBAR MÓVIL ===== --}}
    <aside
        x-show="menuAbierto"
        x-transition:enter="transition ease-out duration-300"
        x-transition:enter-start="-translate-x-full"
        x-transition:enter-end="translate-x-0"
        x-transition:leave="transition ease-in duration-200"
        x-transition:leave-start="translate-x-0"
        x-transition:leave-end="-translate-x-full"
        class="lg:hidden fixed top-0 left-0 h-full w-72 bg-[#0f1117] border-r border-white/5 px-4 py-6 flex flex-col gap-6 z-50 overflow-y-auto"
    >
        <div class="flex items-center gap-3 px-2 mt-8">
            <div class="w-9 h-9 rounded-lg bg-indigo-600 flex items-center justify-center">
                <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.746 0 3.332.477 4.5 1.253v13C19.832 18.477 18.246 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/>
                </svg>
            </div>
            <span class="text-white font-semibold text-sm leading-tight">Biblioteca<br>Digital</span>
        </div>

        <div class="flex flex-col gap-1">
            <span class="text-[10px] font-semibold tracking-widest text-white/30 uppercase px-3 mb-1">Principal</span>
            <a href="{{ route('catalogo') }}" x-on:click="menuAbierto = false" class="flex items-center gap-3 px-3 py-2 rounded-lg text-sm text-white/60 hover:text-white hover:bg-white/5 transition-all duration-200">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/></svg>
                Catálogo
            </a>
            <a href="{{ route('open-library.index') }}" x-on:click="menuAbierto = false" class="flex items-center gap-3 px-3 py-2 rounded-lg text-sm text-white/60 hover:text-white hover:bg-white/5 transition-all duration-200">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                Buscar
            </a>
            <a href="#" x-on:click="menuAbierto = false" class="flex items-center gap-3 px-3 py-2 rounded-lg text-sm text-white/60 hover:text-white hover:bg-white/5 transition-all duration-200">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"/></svg>
                Favoritos
            </a>
            <a href="#" x-on:click="menuAbierto = false" class="flex items-center gap-3 px-3 py-2 rounded-lg text-sm text-white/60 hover:text-white hover:bg-white/5 transition-all duration-200">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.746 0 3.332.477 4.5 1.253v13C19.832 18.477 18.246 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/></svg>
                Mis lecturas
            </a>
        </div>

        <div class="flex-1"></div>

        @auth
        <div class="flex flex-col gap-1">
            <span class="text-[10px] font-semibold tracking-widest text-white/30 uppercase px-3 mb-1">Cuenta</span>
            <div class="flex items-center gap-3 px-3 py-2">
                <div class="w-8 h-8 rounded-full bg-indigo-500 flex items-center justify-center text-white text-xs font-bold">
                    {{ strtoupper(substr(auth()->user()->name, 0, 1)) }}
                </div>
                <span class="text-sm text-white/80 truncate">{{ auth()->user()->name }}</span>
            </div>
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit" class="w-full flex items-center gap-3 px-3 py-2 rounded-lg text-sm text-white/60 hover:text-red-400 hover:bg-red-500/10 transition-all duration-200">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/></svg>
                    Cerrar sesión
                </button>
            </form>
        </div>
        @else
        <div class="flex flex-col gap-2 px-3">
            <a href="{{ route('login') }}" class="text-center py-2 rounded-lg text-sm text-white/60 border border-white/10 hover:border-white/30 hover:text-white transition-all duration-200">Iniciar sesión</a>
            <a href="{{ route('register') }}" class="text-center py-2 rounded-lg text-sm text-white bg-indigo-600 hover:bg-indigo-500 transition-all duration-200">Registrarse</a>
        </div>
        @endauth
    </aside>

</nav>
