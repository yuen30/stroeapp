import { test, expect } from '@playwright/test';

// =========================================================================
// ⚠️ โปรดตั้งค่าอีเมลและรหัสผ่านสำหรับการเข้าระบบแอดมิน ที่ใช้ทดสอบ ⚠️
// สามารถแก้ในไฟล์นี้ หรือจะสร้างตัวแปรในไฟล์ .env ก็ได้
// =========================================================================
const ADMIN_EMAIL = process.env.TEST_ADMIN_EMAIL || 'admin@store.com';
const ADMIN_PASSWORD = process.env.TEST_ADMIN_PASSWORD || 'password';
const SEARCH_CUSTOMER = 'บริษัท'; // คำค้นหาในช่องรหัส/ชื่อลูกค้า

test('🤖 บอททดสอบ E2E: สร้างและยืนยัน Sale Order', async ({ page }) => {

    await test.step('1. ไปที่หน้าล็อกอินและนำทางเข้าระบบ', async () => {
        await page.goto('/store/login');

        // กรอกอีเมลและรหัสผ่าน
        await page.locator('input[type="email"]').fill(ADMIN_EMAIL);
        await page.locator('input[type="password"]').fill(ADMIN_PASSWORD);

        // กดปุ่ม Submit (เข้าสู่ระบบ)
        await page.getByRole('button', { name: /เข้าสู่ระบบ|Sign in/i }).click();

        // รอจนกว่าจะ Redirect ทะลุเข้ามาในระบบสำเร็จ
        await expect(page).toHaveURL(/.*\/store/);
    });

    await test.step('2. นำทางเข้าสู่หน้า Sale Orders และสร้างออเดอร์ใหม่', async () => {
        await page.goto('/store/sale-orders/create');
        await page.waitForLoadState('networkidle');
        await expect(page.getByRole('heading', { name: /สร้าง ใบส่งสินค้า|Create Sale order/i })).toBeVisible();
    });

    await test.step('3. เลือกลูกค้าและเพิ่มสินค้าลงตะกร้า', async () => {
        // คลิกลงในช่องค้นหาลูกค้า (Filament Searchable Select)
        const customerSelect = page.locator('label').filter({ hasText: 'ลูกค้า' }).locator('..').getByRole('combobox');
        await customerSelect.click();
        await customerSelect.fill(SEARCH_CUSTOMER);

        // รอผลการค้นหาและคลิกอันแรก
        const firstCustomerOption = page.locator('div[role="option"]').first();
        await firstCustomerOption.waitFor({ state: 'visible' });
        await firstCustomerOption.click();

        // เลื่อนหน้าจอลงมากดปุ่ม Add item (เพิ่มรายการสินค้า)
        const addItemBtn = page.getByRole('button', { name: /Add item|เพิ่มรายการ/i });
        await addItemBtn.click();

        // เสิร์ชและเลือกสินค้าชิ้นแรก
        const productCombobox = page.locator('.fi-repeater-item').first().getByRole('combobox').first();
        await productCombobox.click();
        const firstProductOption = page.locator('div[role="option"]').first();
        await firstProductOption.waitFor({ state: 'visible' });
        await firstProductOption.click();

        // ระบุจำนวน Qty
        // Filament ใช้ input name="data.items.xxx.quantity" แต่เพื่อความชัวร์จะหาจาก input type number ตัวแรก
        const qtyInput = page.locator('.fi-repeater-item').first().locator('input[type="number"]');
        await qtyInput.fill('5');
    });

    await test.step('4. ทำการบันทึกออเดอร์', async () => {
        // กดปุ่ม Create/สร้าง (Filament ยึดปุ่มหลักเป็น type="submit")
        const createBtn = page.locator('button[type="submit"]');
        await createBtn.click();

        // รอข้อความนอติแจ้งเตือนว่าสำเร็จ
        await expect(page.locator('.fi-no-notification-title').filter({ hasText: /Created|สร้าง/i })).toBeVisible({ timeout: 10000 });
    });

});
