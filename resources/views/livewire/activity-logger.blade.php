<div>
    {{-- Filtros --}}
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-3 mb-6">
        <input
            wire:model.live.debounce.400ms="filtroUsuario"
            type="text"
            placeholder="Filtrar por usuario..."
            class="bg-[#13151f] border border-[#2e3250] rounded-lg px-4 py-2 text-sm text-white placeholder-gray-500 focus:outline-none focus:border-indigo-500 transition-colors"
        >
        <input
            wire:model.live.debounce.400ms="filtroAccion"
            type="text"
            placeholder="Filtrar por acción..."
            class="bg-[#13151f] border border-[#2e3250] rounded-lg px-4 py-2 text-sm text-white placeholder-gray-500 focus:outline-none focus:border-indigo-500 transition-colors"
        >
        {{-- Datepicker Desde --}}
        <div
            x-data="{
                open: false,
                value: '',
                display: '',
                viewYear: new Date().getFullYear(),
                viewMonth: new Date().getMonth(),
                meses: ['Enero','Febrero','Marzo','Abril','Mayo','Junio','Julio','Agosto','Septiembre','Octubre','Noviembre','Diciembre'],
                dias: ['Lu','Ma','Mi','Ju','Vi','Sa','Do'],
                get label() { return this.display || 'Desde' },
                getDays() {
                    let days = [];
                    let first = new Date(this.viewYear, this.viewMonth, 1).getDay();
                    first = first === 0 ? 6 : first - 1;
                    let total = new Date(this.viewYear, this.viewMonth + 1, 0).getDate();
                    for (let i = 0; i < first; i++) days.push(null);
                    for (let d = 1; d <= total; d++) days.push(d);
                    return days;
                },
                select(day) {
                    if (!day) return;
                    let m = String(this.viewMonth + 1).padStart(2,'0');
                    let d = String(day).padStart(2,'0');
                    this.value = this.viewYear + '-' + m + '-' + d;
                    this.display = d + '/' + m + '/' + this.viewYear;
                    $wire.set('fechaDesde', this.value);
                    this.open = false;
                },
                clear() { this.value = ''; this.display = ''; $wire.set('fechaDesde', ''); this.open = false; },
                isSelected(day) {
                    if (!day || !this.value) return false;
                    let m = String(this.viewMonth + 1).padStart(2,'0');
                    let d = String(day).padStart(2,'0');
                    return this.value === this.viewYear + '-' + m + '-' + d;
                },
                isToday(day) {
                    if (!day) return false;
                    let t = new Date();
                    return t.getFullYear() === this.viewYear && t.getMonth() === this.viewMonth && t.getDate() === day;
                }
            }"
            x-on:click.outside="open = false"
            class="relative"
        >
            <button type="button" x-on:click="open = !open"
                class="w-full flex items-center gap-2 bg-[#13151f] border border-[#2e3250] rounded-lg px-3 py-2 text-sm transition-colors hover:border-indigo-500 focus:outline-none"
                :class="value ? 'text-white' : 'text-gray-500'"
            >
                <svg class="w-4 h-4 text-gray-500 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                </svg>
                <span x-text="label" class="flex-1 text-left"></span>
                <svg x-show="value" x-on:click.stop="clear()" class="w-3 h-3 text-gray-500 hover:text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                </svg>
            </button>
            <div x-show="open" x-transition:enter="transition ease-out duration-150" x-transition:enter-start="opacity-0 -translate-y-1" x-transition:enter-end="opacity-100 translate-y-0"
                class="absolute top-full mt-1 left-0 z-50 bg-[#1e2130] border border-[#2e3250] rounded-xl shadow-2xl p-3 w-64">
                <div class="flex items-center justify-between mb-2">
                    <button type="button" x-on:click="viewMonth--; if(viewMonth<0){viewMonth=11;viewYear--}" class="p-1 rounded hover:bg-white/10 text-gray-400 hover:text-white transition-colors">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
                    </button>
                    <span class="text-sm font-medium text-white" x-text="meses[viewMonth] + ' ' + viewYear"></span>
                    <button type="button" x-on:click="viewMonth++; if(viewMonth>11){viewMonth=0;viewYear++}" class="p-1 rounded hover:bg-white/10 text-gray-400 hover:text-white transition-colors">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                    </button>
                </div>
                <div class="grid grid-cols-7 mb-1">
                    <template x-for="d in dias"><span class="text-center text-[10px] text-gray-500 font-medium py-1" x-text="d"></span></template>
                </div>
                <div class="grid grid-cols-7 gap-0.5">
                    <template x-for="(day, i) in getDays()" :key="i">
                        <button type="button" x-on:click="select(day)"
                            :disabled="!day"
                            :class="{
                                'invisible': !day,
                                'bg-indigo-600 text-white font-semibold': isSelected(day),
                                'text-indigo-400 font-semibold': isToday(day) && !isSelected(day),
                                'text-gray-300 hover:bg-white/10': day && !isSelected(day)
                            }"
                            class="h-7 w-full rounded-md text-xs transition-colors">
                            <span x-text="day"></span>
                        </button>
                    </template>
                </div>
                <div class="flex justify-between mt-2 pt-2 border-t border-[#2e3250]">
                    <button type="button" x-on:click="clear()" class="text-xs text-gray-500 hover:text-white transition-colors">Borrar</button>
                    <button type="button" x-on:click="let t=new Date();viewYear=t.getFullYear();viewMonth=t.getMonth()" class="text-xs text-indigo-400 hover:text-indigo-300 transition-colors">Hoy</button>
                </div>
            </div>
        </div>

        {{-- Datepicker Hasta --}}
        <div
            x-data="{
                open: false,
                value: '',
                display: '',
                viewYear: new Date().getFullYear(),
                viewMonth: new Date().getMonth(),
                meses: ['Enero','Febrero','Marzo','Abril','Mayo','Junio','Julio','Agosto','Septiembre','Octubre','Noviembre','Diciembre'],
                dias: ['Lu','Ma','Mi','Ju','Vi','Sa','Do'],
                get label() { return this.display || 'Hasta' },
                getDays() {
                    let days = [];
                    let first = new Date(this.viewYear, this.viewMonth, 1).getDay();
                    first = first === 0 ? 6 : first - 1;
                    let total = new Date(this.viewYear, this.viewMonth + 1, 0).getDate();
                    for (let i = 0; i < first; i++) days.push(null);
                    for (let d = 1; d <= total; d++) days.push(d);
                    return days;
                },
                select(day) {
                    if (!day) return;
                    let m = String(this.viewMonth + 1).padStart(2,'0');
                    let d = String(day).padStart(2,'0');
                    this.value = this.viewYear + '-' + m + '-' + d;
                    this.display = d + '/' + m + '/' + this.viewYear;
                    $wire.set('fechaHasta', this.value);
                    this.open = false;
                },
                clear() { this.value = ''; this.display = ''; $wire.set('fechaHasta', ''); this.open = false; },
                isSelected(day) {
                    if (!day || !this.value) return false;
                    let m = String(this.viewMonth + 1).padStart(2,'0');
                    let d = String(day).padStart(2,'0');
                    return this.value === this.viewYear + '-' + m + '-' + d;
                },
                isToday(day) {
                    if (!day) return false;
                    let t = new Date();
                    return t.getFullYear() === this.viewYear && t.getMonth() === this.viewMonth && t.getDate() === day;
                }
            }"
            x-on:click.outside="open = false"
            class="relative"
        >
            <button type="button" x-on:click="open = !open"
                class="w-full flex items-center gap-2 bg-[#13151f] border border-[#2e3250] rounded-lg px-3 py-2 text-sm transition-colors hover:border-indigo-500 focus:outline-none"
                :class="value ? 'text-white' : 'text-gray-500'"
            >
                <svg class="w-4 h-4 text-gray-500 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                </svg>
                <span x-text="label" class="flex-1 text-left"></span>
                <svg x-show="value" x-on:click.stop="clear()" class="w-3 h-3 text-gray-500 hover:text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                </svg>
            </button>
            <div x-show="open" x-transition:enter="transition ease-out duration-150" x-transition:enter-start="opacity-0 -translate-y-1" x-transition:enter-end="opacity-100 translate-y-0"
                class="absolute top-full mt-1 right-0 z-50 bg-[#1e2130] border border-[#2e3250] rounded-xl shadow-2xl p-3 w-64">
                <div class="flex items-center justify-between mb-2">
                    <button type="button" x-on:click="viewMonth--; if(viewMonth<0){viewMonth=11;viewYear--}" class="p-1 rounded hover:bg-white/10 text-gray-400 hover:text-white transition-colors">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
                    </button>
                    <span class="text-sm font-medium text-white" x-text="meses[viewMonth] + ' ' + viewYear"></span>
                    <button type="button" x-on:click="viewMonth++; if(viewMonth>11){viewMonth=0;viewYear++}" class="p-1 rounded hover:bg-white/10 text-gray-400 hover:text-white transition-colors">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                    </button>
                </div>
                <div class="grid grid-cols-7 mb-1">
                    <template x-for="d in dias"><span class="text-center text-[10px] text-gray-500 font-medium py-1" x-text="d"></span></template>
                </div>
                <div class="grid grid-cols-7 gap-0.5">
                    <template x-for="(day, i) in getDays()" :key="i">
                        <button type="button" x-on:click="select(day)"
                            :disabled="!day"
                            :class="{
                                'invisible': !day,
                                'bg-indigo-600 text-white font-semibold': isSelected(day),
                                'text-indigo-400 font-semibold': isToday(day) && !isSelected(day),
                                'text-gray-300 hover:bg-white/10': day && !isSelected(day)
                            }"
                            class="h-7 w-full rounded-md text-xs transition-colors">
                            <span x-text="day"></span>
                        </button>
                    </template>
                </div>
                <div class="flex justify-between mt-2 pt-2 border-t border-[#2e3250]">
                    <button type="button" x-on:click="clear()" class="text-xs text-gray-500 hover:text-white transition-colors">Borrar</button>
                    <button type="button" x-on:click="let t=new Date();viewYear=t.getFullYear();viewMonth=t.getMonth()" class="text-xs text-indigo-400 hover:text-indigo-300 transition-colors">Hoy</button>
                </div>
            </div>
        </div>
    </div>

    @if($filtroUsuario || $filtroAccion || $fechaDesde || $fechaHasta)
        <div class="mb-4">
            <button wire:click="limpiarFiltros" class="text-xs text-indigo-400 hover:text-indigo-300 transition-colors">
                ✕ Limpiar filtros
            </button>
        </div>
    @endif

    {{-- Tabla --}}
    <div class="overflow-x-auto rounded-xl border border-[#2e3250]">
        <table class="w-full text-sm text-left text-gray-300">
            <thead class="bg-[#2a2f45] text-xs text-gray-400 uppercase">
                <tr>
                    <th class="px-4 py-3">Usuario</th>
                    <th class="px-4 py-3">Acción</th>
                    <th class="px-4 py-3">Descripción</th>
                    <th class="px-4 py-3">IP</th>
                    <th class="px-4 py-3">Fecha</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-[#2e3250]">
                @forelse ($logs as $log)
                    <tr class="bg-[#1e2130] hover:bg-[#252a3e] transition-colors">
                        <td class="px-4 py-3 font-medium">{{ $log->user?->name ?? 'Anónimo' }}</td>
                        <td class="px-4 py-3">
                            <span class="px-2 py-1 rounded-full text-xs font-medium
                                @if(str_contains($log->accion, 'login')) bg-green-500/20 text-green-400
                                @elseif(str_contains($log->accion, 'logout')) bg-red-500/20 text-red-400
                                @elseif(str_contains($log->accion, 'busqueda')) bg-blue-500/20 text-blue-400
                                @elseif(str_contains($log->accion, 'prestamo')) bg-yellow-500/20 text-yellow-400
                                @else bg-gray-500/20 text-gray-400 @endif">
                                {{ $log->accion }}
                            </span>
                        </td>
                        <td class="px-4 py-3 text-gray-400">{{ $log->descripcion ?? '—' }}</td>
                        <td class="px-4 py-3 text-gray-500 font-mono text-xs">{{ $log->ip }}</td>
                        <td class="px-4 py-3 text-gray-500 text-xs">
                            {{ $log->created_at->timezone('America/Hermosillo')->format('d/m/Y H:i') }}
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="px-4 py-8 text-center text-gray-500">
                            No hay registros con los filtros aplicados.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-4">
        {{ $logs->links() }}
    </div>
</div>
