import { test, expect } from '@playwright/test';

test.describe('Formulario de contacto', () => {
  test('muestra el catalogo correctamente', async ({ page }) => {
    await page.goto('/catalogo');
    await expect(page).toHaveURL(/catalogo/);
    await expect(page.locator('h1')).toBeVisible();
  });

  test('muestra el login correctamente', async ({ page }) => {
    await page.goto('/login');
    await expect(page.locator('input[type="email"]')).toBeVisible();
    await expect(page.locator('input[type="password"]')).toBeVisible();
    await expect(page.locator('button[type="submit"]')).toBeVisible();
  });
});
