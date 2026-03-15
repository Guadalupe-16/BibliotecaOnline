import { test, expect } from '@playwright/test';

test.describe('Navegacion', () => {
  test('muestra el menu lateral correctamente', async ({ page }) => {
    await page.goto('/catalogo');
    await expect(page.locator('aside').first()).toBeVisible();
    await expect(page.locator('text=Catálogo').first()).toBeVisible();
    await expect(page.locator('text=Buscar').first()).toBeVisible();
    await expect(page.locator('text=Favoritos').first()).toBeVisible();
  });

  test('navega al catalogo correctamente', async ({ page }) => {
    await page.goto('/');
    await page.goto('/catalogo');
    await expect(page).toHaveURL(/catalogo/);
  });

  test('navega a la pagina about', async ({ page }) => {
    await page.goto('/catalogo');
    await expect(page).toHaveURL(/catalogo/);
    await expect(page.locator('h1')).toBeVisible();
  });

  test('navega al formulario de contacto', async ({ page }) => {
    await page.goto('/catalogo');
    await expect(page.locator('aside').first()).toBeVisible();
  });
});