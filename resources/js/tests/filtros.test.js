import { describe, it, expect } from 'vitest'

// Lógica de filtros del panel de logs y catálogo
function filtrarPorTexto(items, campo, termino) {
    if (!termino || termino.trim() === '') return items
    return items.filter(item =>
        item[campo].toLowerCase().includes(termino.toLowerCase())
    )
}

function filtrarPorFecha(items, fechaDesde, fechaHasta) {
    return items.filter(item => {
        const fecha = new Date(item.fecha)
        if (fechaDesde && fecha < new Date(fechaDesde)) return false
        if (fechaHasta && fecha > new Date(fechaHasta)) return false
        return true
    })
}

const logsEjemplo = [
    { accion: 'login', usuario: 'Admin', fecha: '2026-03-10' },
    { accion: 'logout', usuario: 'Superadmin', fecha: '2026-03-12' },
    { accion: 'login', usuario: 'Usuario', fecha: '2026-03-14' },
]

describe('panel-logs - filtros', () => {
    it('filtra logs por acción', () => {
        const resultado = filtrarPorTexto(logsEjemplo, 'accion', 'login')
        expect(resultado).toHaveLength(2)
    })

    it('retorna todos los logs si el filtro está vacío', () => {
        const resultado = filtrarPorTexto(logsEjemplo, 'accion', '')
        expect(resultado).toHaveLength(3)
    })

    it('filtra logs por rango de fechas', () => {
        const resultado = filtrarPorFecha(logsEjemplo, '2026-03-11', '2026-03-13')
        expect(resultado).toHaveLength(1)
        expect(resultado[0].accion).toBe('logout')
    })

    it('retorna array vacío si ningún log coincide', () => {
        const resultado = filtrarPorTexto(logsEjemplo, 'accion', 'eliminar')
        expect(resultado).toHaveLength(0)
    })
})
