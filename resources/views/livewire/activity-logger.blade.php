<div>
    <div class="mb-4">
        <input
            wire:model.live="filtro"
            type="text"
            placeholder="Filtrar por acción o descripción..."
            class="w-full bg-[#13151f] border border-[#2e3250] rounded-lg px-4 py-2 text-sm text-white placeholder-gray-500 focus:outline-none focus:border-indigo-500 transition-colors"
        >
    </div>

    <div class="overflow-x-auto rounded-xl border border-[#2e3250]">
        <table class="w-full text-sm text-left text-gray-300">
            <thead class="bg-[#2a2f45] text-xs text-gray-400 uppercase">
                <tr>
                    <th class="px-4 py-3">Acción</th>
                    <th class="px-4 py-3">Descripción</th>
                    <th class="px-4 py-3">Usuario</th>
                    <th class="px-4 py-3">IP</th>
                    <th class="px-4 py-3">Fecha</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-[#2e3250]">
                @forelse ($logs as $log)
                    <tr class="bg-[#1e2130] hover:bg-[#252a3e] transition-colors">
                        <td class="px-4 py-3">
                            <span class="px-2 py-1 rounded-full text-xs font-medium
                                @if(str_contains($log->accion, 'login')) bg-green-500/20 text-green-400
                                @elseif(str_contains($log->accion, 'busqueda')) bg-blue-500/20 text-blue-400
                                @elseif(str_contains($log->accion, 'prestamo')) bg-yellow-500/20 text-yellow-400
                                @else bg-gray-500/20 text-gray-400 @endif">
                                {{ $log->accion }}
                            </span>
                        </td>
                        <td class="px-4 py-3 text-gray-400">{{ $log->descripcion ?? '—' }}</td>
                        <td class="px-4 py-3">{{ $log->user?->name ?? 'Anónimo' }}</td>
                        <td class="px-4 py-3 text-gray-500 font-mono text-xs">{{ $log->ip }}</td>
                        <td class="px-4 py-3 text-gray-500 text-xs">{{ $log->created_at->timezone('America/Hermosillo')->format('d/m/Y H:i') }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="px-4 py-8 text-center text-gray-500">No hay registros aún.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-4">
        {{ $logs->links() }}
    </div>
</div>
