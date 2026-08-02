import { test, expect } from '@playwright/test';

const ADMIN_USER = process.env.WP_ADMIN_USER || 'admin';
const ADMIN_PASS = process.env.WP_ADMIN_PASS || 'adminpass';

async function loginAsAdmin(page) {
  await page.goto('/wp-login.php');
  await page.fill('#user_login', ADMIN_USER);
  await page.fill('#user_pass', ADMIN_PASS);
  await page.click('#wp-submit');
  await expect(page).toHaveURL(/wp-admin/);
}

test.describe('WordPress admin', () => {
  test('dashboard loads after login', async ({ page }) => {
    await loginAsAdmin(page);
    await expect(page.locator('#wpbody-content')).toBeVisible();
    await expect(page.locator('#wpbody-content h1').first()).toBeVisible();
  });

  test('apps list screen loads', async ({ page }) => {
    await loginAsAdmin(page);
    await page.goto('/wp-admin/edit.php?post_type=adp_app');
    await expect(page.locator('h1.wp-heading-inline')).toContainText(/Apps/i);
  });
});

test.describe('Gutenberg app editor', () => {
  test('new app editor loads ADP sidebar panels', async ({ page }) => {
    await loginAsAdmin(page);
    await page.goto('/wp-admin/post-new.php?post_type=adp_app');

    await expect(page.locator('.edit-post-header')).toBeVisible();

    const hasEditorScript = await page.evaluate(() => {
      return Array.from(document.querySelectorAll('script[src]')).some((s) => s.src.includes('editor.js'));
    });
    expect(hasEditorScript).toBe(true);

    const settingsToggle = page.locator(
      'button[aria-label="Settings"], button[aria-label="Post"], button[aria-label="Toggle settings panel"]'
    ).first();
    if (await settingsToggle.isVisible()) {
      await settingsToggle.click();
    }

    await expect(page.getByText('App Details', { exact: true })).toBeVisible({ timeout: 20000 });
    await expect(page.getByText('Pricing & URLs', { exact: true })).toBeVisible();
    await expect(page.getByText('Safety & Display', { exact: true })).toBeVisible();
  });
});

test.describe('Appyn migration admin', () => {
  test('migration page loads and dry-run form is available', async ({ page }) => {
    await loginAsAdmin(page);
    await page.goto('/wp-admin/edit.php?post_type=adp_app&page=adp-migration');

    await expect(page.locator('h1')).toContainText(/Appyn Migration/i);
    await expect(page.locator('input[name="confirm_backup"]').first()).toBeVisible();
    await expect(page.getByRole('button', { name: /Dry Run Preview/i })).toBeVisible();
    await expect(page.getByRole('button', { name: /Run Full Migration/i })).toBeVisible();
    await expect(page.getByRole('button', { name: /Rollback Migration/i })).toBeVisible();
  });
});

test.describe('Version manager admin', () => {
  test('version manager metabox on existing app', async ({ page }) => {
    await loginAsAdmin(page);
    await page.goto('/wp-admin/edit.php?post_type=adp_app');

    const editLink = page.locator('table.wp-list-table tbody tr').first().locator('a.row-title');
    await expect(editLink).toBeVisible();
    await editLink.click();

    await expect(page.locator('#adp-add-version')).toBeVisible({ timeout: 15000 });
    await expect(page.locator('#adp-version-table')).toBeVisible();
  });
});
