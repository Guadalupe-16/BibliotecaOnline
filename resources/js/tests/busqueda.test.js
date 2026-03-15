import { describe, it, expect } from 'vitest'

function construirUrlBusqueda(termino, base = '/buscar') {
    if (!termino || termino.trim() === '') return null
    return `${base}?q=${encodeURIComponent(termino.trim())}`
}

function construirUrlOpenLibrary(termino) {
    return construirUrlBusqueda(termino, '/open-library/buscar')
}

function normalizarTermino(termino) {
    return termino.trim().toLowerCase()
}

describe('busqueda-libro', () => {
    it('construye la URL correcta con el término de búsqueda', () => {
        const url = construirUrlBusqueda('clean code')
        expect(url).toBe('/buscar?q=clean%20code')
    })

    it('retorna null si el término está vacío', () => {
        expect(construirUrlBusqueda('')).toBeNull()
        expect(construirUrlBusqueda('   ')).toBeNull()
    })

    it('normaliza el término eliminando espacios extra', () => {
        expect(normalizarTermino('  geor  ')).toBe('geor')
    })
})

describe('buscar-open-library', () => {
    it('construye la URL de Open Library correctamente', () => {
        const url = construirUrlOpenLibrary('clean code')
        expect(url).toBe('/open-library/buscar?q=clean%20code')
    })

    it('retorna null si el término está vacío', () => {
        expect(construirUrlOpenLibrary('')).toBeNull()
    })

    it('codifica correctamente caracteres especiales', () => {
        const url = construirUrlOpenLibrary('García Márquez')
        expect(url).toContain('Garc%C3%ADa')
    })
})
