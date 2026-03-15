import { test, expect } from '@playwright/test';

test.describe('Catalogo', () => {
  test('muestra la página del catálogo correctamente', async ({ page }) => {
    await page.goto('/catalogo');
    await expect(page).toHaveURL(/catalogo/);
    await expect(page.locator('h1')).toBeVisible();
  });

  test('muestra la página de detalle de un libro', async ({ page }) => {
    await page.goto('/catalogo');
    const primerLibro = page.locator('a').filter({ hasText: /ver|detalle|más/i }).first();
    if (await primerLibro.count() > 0) {
      await primerLibro.click();
      await expect(page).toHaveURL(/libros/);
    }
  });

  test('muestra el buscador dinámico', async ({ page }) => {
    await page.goto('/buscar');
    await expect(page.getByPlaceholder('Buscar por título o autor...')).toBeVisible();
  });
});