import { expect, Page, test } from '@playwright/test';

const ADMIN_EMAIL = process.env.TEST_ADMIN_EMAIL || 'admin@store.com';
const ADMIN_PASSWORD = process.env.TEST_ADMIN_PASSWORD || 'password';

type ClientErrorTracker = {
    livewireFailures: string[];
    pageErrors: string[];
};

function trackClientErrors(page: Page): ClientErrorTracker {
    const livewireFailures: string[] = [];
    const pageErrors: string[] = [];

    page.on('pageerror', (error) => {
        pageErrors.push(error.message);
    });

    page.on('response', (response) => {
        if (! response.url().includes('/livewire')) {
            return;
        }

        if (response.status() >= 400) {
            livewireFailures.push(`${response.status()} ${response.url()}`);
        }
    });

    return { livewireFailures, pageErrors };
}

async function login(page: Page): Promise<void> {
    await page.goto('/store/login');

    await page.getByLabel(/E-Mail หรือชื่อผู้ใช้งาน/).fill(ADMIN_EMAIL);
    await page.getByRole('textbox', { name: /รหัสผ่าน/ }).fill(ADMIN_PASSWORD);
    await page.getByRole('button', { name: /เข้าสู่ระบบ|sign in/i }).click();

    await expect(page).toHaveURL(/\/store(?:\/?$|\/dashboard)/);
}

async function expectNoClientFailures(tracker: ClientErrorTracker): Promise<void> {
    expect(
        tracker.pageErrors,
        `Unexpected browser errors:\n${tracker.pageErrors.join('\n')}`,
    ).toEqual([]);

    expect(
        tracker.livewireFailures,
        `Unexpected Livewire failures:\n${tracker.livewireFailures.join('\n')}`,
    ).toEqual([]);
}

async function getTableSearchInput(page: Page) {
    return page.getByRole('main').getByRole('searchbox', { name: 'ค้นหา' }).first();
}

test.describe('Filament report page regressions', () => {
    test('goods receipt report survives widget and table interactions', async ({ page }) => {
        const tracker = trackClientErrors(page);

        await login(page);
        await page.goto('/store/goods-receipt-report');

        await expect(page.getByRole('heading', { name: /รายงานการรับสินค้า/i })).toBeVisible();
        await expect(page.getByText('ใบรับสินค้า (ตามช่วงเวลา)')).toBeVisible();

        const searchInput = await getTableSearchInput(page);
        await expect(searchInput).toBeVisible();

        await searchInput.fill('GR');
        await page.waitForLoadState('networkidle');

        await expect(page.getByRole('heading', { name: /รายงานการรับสินค้า/i })).toBeVisible();
        await expectNoClientFailures(tracker);
    });

    test('low stock report survives table search interactions', async ({ page }) => {
        const tracker = trackClientErrors(page);

        await login(page);
        await page.goto('/store/low-stock-report');

        await expect(page.getByRole('heading', { name: /รายงานสินค้า Stock ต่ำ/i })).toBeVisible();
        await expect(page.getByText('จำนวนสินค้าที่ต้องเติม')).toBeVisible();

        const searchInput = await getTableSearchInput(page);
        await expect(searchInput).toBeVisible();

        await searchInput.fill('PROD');
        await page.waitForLoadState('networkidle');

        await expect(page.getByRole('heading', { name: /รายงานสินค้า Stock ต่ำ/i })).toBeVisible();
        await expectNoClientFailures(tracker);
    });
});
