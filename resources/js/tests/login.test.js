import { describe, it, expect } from 'vitest'

function validarLogin(email, password) {
  const errores = []
  if (!email || email.trim() === '') {
    errores.push('El correo es requerido')
  } else if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email)) {
    errores.push('El correo no tiene formato válido')
  }
  if (!password || password.trim() === '') {
    errores.push('La contraseña es requerida')
  }
  return errores
}

describe('validarLogin', () => {
  it('no retorna errores con credenciales válidas', () => {
    const errores = validarLogin('superadmin@biblioteca.com', 'Superadmin123!')
    expect(errores).toHaveLength(0)
  })

  it('retorna error si el email está vacío', () => {
    const errores = validarLogin('', 'Superadmin123!')
    expect(errores).toContain('El correo es requerido')
  })

  it('retorna error si el email no tiene formato válido', () => {
    const errores = validarLogin('correo-invalido', 'Superadmin123!')
    expect(errores).toContain('El correo no tiene formato válido')
  })

  it('retorna error si la contraseña está vacía', () => {
    const errores = validarLogin('superadmin@biblioteca.com', '')
    expect(errores).toContain('La contraseña es requerida')
  })
})
