import { expect, Locator, Page, test } from '@playwright/test';

// =========================================================================
// ⚠️ โปรดตั้งค่าอีเมลและรหัสผ่านสำหรับการเข้าระบบแอดมิน ที่ใช้ทดสอบ ⚠️
// สามารถแก้ในไฟล์นี้ หรือจะสร้างตัวแปรในไฟล์ .env ก็ได้
// =========================================================================
const ADMIN_EMAIL = process.env.TEST_ADMIN_EMAIL || 'admin@store.com';
const ADMIN_PASSWORD = process.env.TEST_ADMIN_PASSWORD || 'password';

async function selectFirstFilamentOption(trigger: Locator, page: Page) {
    await trigger.click();

    const firstOption = page.getByRole('option').first();
    await expect(firstOption).toBeVisible();
    await firstOption.click();
}

test('🤖 บอททดสอบ E2E: สร้าง Sale Order และเปิดหน้าจัดการรายการสินค้า', async ({ page }) => {

    await test.step('1. ไปที่หน้าล็อกอินและนำทางเข้าระบบ', async () => {
        await page.goto('/store/login');

        // กรอกอีเมลและรหัสผ่าน
        await page.getByLabel(/E-Mail หรือชื่อผู้ใช้งาน/).fill(ADMIN_EMAIL);
        await page.getByRole('textbox', { name: /รหัสผ่าน/ }).fill(ADMIN_PASSWORD);

        // กดปุ่ม Submit (เข้าสู่ระบบ)
        await page.getByRole('button', { name: /เข้าสู่ระบบ|Sign in/i }).click();

        // รอจนกว่าจะ Redirect ทะลุเข้ามาในระบบสำเร็จ
        await page.waitForURL(/\/store(?:\/?$|\/dashboard)/);
        await page.waitForLoadState('networkidle');
    });

    await test.step('2. นำทางเข้าสู่หน้า Sale Orders และสร้างออเดอร์ใหม่', async () => {
        await page.goto('/store/sale-orders/create', { waitUntil: 'networkidle' });
        await expect(page.getByRole('heading', { name: /เพิ่มใบส่งสินค้า|create sale order/i })).toBeVisible();
    });

    await test.step('3. เลือกข้อมูลที่จำเป็นสำหรับการสร้าง Sale Order', async () => {
        await selectFirstFilamentOption(page.getByRole('button', { name: 'เลือกลูกค้า' }), page);
        await selectFirstFilamentOption(page.getByRole('button', { name: 'เลือกช่องทางชำระเงิน' }), page);
        await selectFirstFilamentOption(page.getByRole('button', { name: 'เลือกสถานะการชำระเงิน' }), page);
    });

    await test.step('4. สร้างใบส่งสินค้าและถูกพาไปหน้า View', async () => {
        await page.getByRole('button', { name: 'สร้างใบส่งสินค้า' }).click();

        await page.waitForURL(/\/store\/sale-orders\/[^/]+$/);
        await page.waitForLoadState('networkidle');

        await expect(page.getByRole('button', { name: 'เพิ่มสินค้า' })).toBeVisible();
    });

    await test.step('5. เปิด modal เพิ่มสินค้าได้จากหน้า View', async () => {
        await page.getByRole('button', { name: 'เพิ่มสินค้า' }).click();

        await expect(page.getByText('เพิ่มสินค้าในใบส่งสินค้า')).toBeVisible();
    });

});
