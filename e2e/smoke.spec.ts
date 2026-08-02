import { test, expect } from '@playwright/test';

test.describe('Homepage', () => {
  test('loads with main landmark and search', async ({ page }) => {
    await page.goto('/');
    await expect(page.locator('#main-content')).toBeVisible();
    const search = page.locator('#adp-search-input, #adp-hero-search').first();
    await expect(search).toBeVisible();
  });

  test('skip link targets main content', async ({ page }) => {
    await page.goto('/');
    const skip = page.locator('.adp-skip-link');
    await expect(skip).toHaveAttribute('href', '#main-content');
  });
});

test.describe('App archive', () => {
  test('archive page loads', async ({ page }) => {
    await page.goto('/apps/');
    await expect(page.locator('h1, .adp-page-title').first()).toBeVisible();
  });
});

test.describe('Search', () => {
  test('search form accepts input', async ({ page }) => {
    await page.goto('/');
    const input = page.locator('#adp-search-input, #adp-hero-search').first();
    await input.fill('test');
    await expect(input).toHaveValue('test');
  });
});

test.describe('404', () => {
  test('returns not found page', async ({ page }) => {
    const res = await page.goto('/nonexistent-page-xyz-404/');
    expect(res?.status()).toBe(404);
    await expect(page.locator('#main-content')).toBeVisible();
  });
});
