import { describe, it, expect, vi, beforeEach } from 'vitest'

async function toggleFavorito(libroId, esFavoritoActual) {
    const response = await fetch(`/favoritos/${libroId}/toggle`, {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': 'test-token',
            'Content-Type': 'application/json',
        },
    })
    const data = await response.json()
    return data.esFavorito
}

describe('toggleFavorito', () => {
    beforeEach(() => {
        vi.resetAllMocks()
    })

    it('devuelve true al agregar un libro a favoritos', async () => {
        global.fetch = vi.fn().mockResolvedValue({
            json: () => Promise.resolve({ esFavorito: true }),
        })

        const resultado = await toggleFavorito(1, false)
        expect(resultado).toBe(true)
    })

    it('devuelve false al quitar un libro de favoritos', async () => {
        global.fetch = vi.fn().mockResolvedValue({
            json: () => Promise.resolve({ esFavorito: false }),
        })

        const resultado = await toggleFavorito(1, true)
        expect(resultado).toBe(false)
    })

    it('llama al endpoint correcto con el id del libro', async () => {
        global.fetch = vi.fn().mockResolvedValue({
            json: () => Promise.resolve({ esFavorito: true }),
        })

        await toggleFavorito(5, false)
        expect(fetch).toHaveBeenCalledWith('/favoritos/5/toggle', expect.any(Object))
    })

    it('usa el método POST', async () => {
        global.fetch = vi.fn().mockResolvedValue({
            json: () => Promise.resolve({ esFavorito: true }),
        })

        await toggleFavorito(1, false)
        const opciones = fetch.mock.calls[0][1]
        expect(opciones.method).toBe('POST')
    })
})
