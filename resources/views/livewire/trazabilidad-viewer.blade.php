<div>
    {{-- Filtros --}}
    <div class="grid grid-cols-1 md:grid-cols-3 lg:grid-cols-6 gap-3 mb-4">
        <input
            wire:model.live.debounce.400ms="filtroTraceId"
            type="text"
            placeholder="Trace ID..."
            class="bg-[#13151f] border border-[#2e3250] rounded-lg px-4 py-2 text-sm text-white placeholder-gray-500 focus:outline-none focus:border-indigo-500 transition-colors font-mono"
        >

        <select
            wire:model.live="filtroMetodo"
            class="bg-[#13151f] border border-[#2e3250] rounded-lg px-4 py-2 text-sm text-white focus:outline-none focus:border-indigo-500 transition-colors"
        >
            <option value="">Método (todos)</option>
            <option value="GET">GET</option>
            <option value="POST">POST</option>
            <option value="PUT">PUT</option>
            <option value="PATCH">PATCH</option>
            <option value="DELETE">DELETE</option>
        </select>

        <select
            wire:model.live="filtroStatus"
            class="bg-[#13151f] border border-[#2e3250] rounded-lg px-4 py-2 text-sm text-white focus:outline-none focus:border-indigo-500 transition-colors"
        >
            <option value="">Status (todos)</option>
            <option value="2xx">2xx — Exitosa</option>
            <option value="3xx">3xx — Redirección</option>
            <option value="4xx">4xx — Error cliente</option>
            <option value="5xx">5xx — Error servidor</option>
        </select>

        <input
            wire:model.live.debounce.400ms="filtroRuta"
            type="text"
            placeholder="Filtrar por ruta..."
            class="bg-[#13151f] border border-[#2e3250] rounded-lg px-4 py-2 text-sm text-white placeholder-gray-500 focus:outline-none focus:border-indigo-500 transition-colors"
        >

        <input
            wire:model.live.debounce.400ms="filtroUsuario"
            type="text"
            placeholder="Filtrar por usuario..."
            class="bg-[#13151f] border border-[#2e3250] rounded-lg px-4 py-2 text-sm text-white placeholder-gray-500 focus:outline-none focus:border-indigo-500 transition-colors"
        >

        <div class="grid grid-cols-2 gap-2">
            <input wire:model.live="fechaDesde" type="date"
                class="bg-[#13151f] border border-[#2e3250] rounded-lg px-2 py-2 text-xs text-white focus:outline-none focus:border-indigo-500 transition-colors">
            <input wire:model.live="fechaHasta" type="date"
                class="bg-[#13151f] border border-[#2e3250] rounded-lg px-2 py-2 text-xs text-white focus:outline-none focus:border-indigo-500 transition-colors">
        </div>
    </div>

    @if($filtroTraceId || $filtroMetodo || $filtroStatus || $filtroRuta || $filtroUsuario || $fechaDesde || $fechaHasta)
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
                    <th class="px-4 py-3">Trace ID</th>
                    <th class="px-4 py-3">Fecha</th>
                    <th class="px-4 py-3">Método</th>
                    <th class="px-4 py-3">Ruta</th>
                    <th class="px-4 py-3">Usuario</th>
                    <th class="px-4 py-3">IP</th>
                    <th class="px-4 py-3">Status</th>
                    <th class="px-4 py-3">Duración</th>
                    <th class="px-4 py-3"></th>
                </tr>
            </thead>
            <tbody class="divide-y divide-[#2e3250]">
                @forelse ($trazas as $traza)
                    <tr class="bg-[#1e2130] hover:bg-[#252a3e] transition-colors cursor-pointer" wire:click="verDetalle({{ $traza->id }})">
                        <td class="px-4 py-3 font-mono text-xs text-gray-400">{{ Str::limit($traza->trace_id, 8, '') }}</td>
                        <td class="px-4 py-3 text-gray-500 text-xs">
                            {{ $traza->created_at->timezone('America/Hermosillo')->format('d/m/Y H:i:s') }}
                        </td>
                        <td class="px-4 py-3">
                            <span class="px-2 py-1 rounded-full text-xs font-medium bg-indigo-500/20 text-indigo-300">
                                {{ $traza->metodo }}
                            </span>
                        </td>
                        <td class="px-4 py-3 font-mono text-xs">{{ $traza->ruta }}</td>
                        <td class="px-4 py-3 font-medium">{{ $traza->user?->name ?? 'Anónimo' }}</td>
                        <td class="px-4 py-3 text-gray-500 font-mono text-xs">{{ $traza->ip }}</td>
                        <td class="px-4 py-3">
                            <span class="px-2 py-1 rounded-full text-xs font-medium
                                @if($traza->categoria === 'exitosa') bg-green-500/20 text-green-400
                                @elseif($traza->categoria === 'error_cliente') bg-yellow-500/20 text-yellow-400
                                @else bg-red-500/20 text-red-400 @endif">
                                {{ $traza->status_http }}
                            </span>
                        </td>
                        <td class="px-4 py-3 text-gray-400 text-xs">{{ $traza->duracion_ms }} ms</td>
                        <td class="px-4 py-3 text-gray-500 text-xs">
                            {{ $trazaSeleccionada === $traza->id ? '▲' : '▼' }}
                        </td>
                    </tr>
                    @if($trazaSeleccionada === $traza->id)
                        <tr class="bg-[#171a26]">
                            <td colspan="9" class="px-4 py-4">
                                <dl class="grid grid-cols-1 md:grid-cols-3 gap-x-6 gap-y-2 text-xs">
                                    <div><dt class="text-gray-500 uppercase">Trace ID completo</dt><dd class="font-mono text-gray-300">{{ $traza->trace_id }}</dd></div>
                                    <div><dt class="text-gray-500 uppercase">Resultado</dt><dd class="text-gray-300">{{ $traza->resultado }}</dd></div>
                                    <div><dt class="text-gray-500 uppercase">User agent</dt><dd class="text-gray-300 break-all">{{ $traza->user_agent ?? '—' }}</dd></div>
                                    <div class="md:col-span-3">
                                        <dt class="text-gray-500 uppercase">Referencia de error</dt>
                                        <dd class="text-gray-300 break-all">{{ $traza->error_referencia ?? 'Sin error registrado.' }}</dd>
                                    </div>
                                </dl>
                            </td>
                        </tr>
                    @endif
                @empty
                    <tr>
                        <td colspan="9" class="px-4 py-8 text-center text-gray-500">
                            No hay trazas con los filtros aplicados.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-4">
        {{ $trazas->links() }}
    </div>
</div>
