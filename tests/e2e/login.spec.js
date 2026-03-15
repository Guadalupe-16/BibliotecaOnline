import { test, expect } from '@playwright/test';

test.describe('Login', () => {
  test('muestra la página de login correctamente', async ({ page }) => {
    await page.goto('/login');
    await expect(page).toHaveTitle(/Biblioteca/);
    await expect(page.locator('input[type="email"]')).toBeVisible();
    await expect(page.locator('input[type="password"]')).toBeVisible();
  });

  test('muestra error con credenciales incorrectas', async ({ page }) => {
    await page.goto('/login');
    await page.fill('input[type="email"]', 'noexiste@test.com');
    await page.fill('input[type="password"]', 'wrongpassword');
    await page.click('button[type="submit"]');
    await expect(page.locator('body')).toContainText(/credenciales|invalid|incorrect/i);
  });

  test('redirige al login si no está autenticado', async ({ page }) => {
    await page.goto('/favoritos');
    await expect(page).toHaveURL(/login/);
  });
});