import { test, expect } from '@playwright/test';

test('single app page loads when apps exist', async ({ page }) => {
  await page.goto('/apps/');
  const link = page.locator('.adp-app-card__link').first();
  if (await link.count() === 0) {
    test.skip();
    return;
  }
  await link.click();
  await expect(page.locator('.adp-app-hero__title, h1').first()).toBeVisible();
  await expect(page.locator('.adp-report-btn')).toBeVisible();
});
