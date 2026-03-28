import { defineConfig, devices } from '@playwright/test';
import dotenv from 'dotenv';

/**
 * โหลดไฟล์ .env เข้ามาเพื่อดึงตัวแปรระบบ
 * เช่น APP_URL, ADMIN_EMAIL, ADMIN_PASSWORD
 */
dotenv.config();

export default defineConfig({
  testDir: './tests-e2e',
  fullyParallel: false, // รันทีละอันก่อนเพื่อความเสถียรสำหรับบอทสร้างข้อมูล
  forbidOnly: !!process.env.CI,
  retries: process.env.CI ? 2 : 0,
  workers: 1, // กำหนด worker เดียวป้องกันปัญหา State ชนกันระหว่างสร้างออเดอร์
  reporter: 'html',
  
  use: {
    // ให้ Base URL ดึงจากไฟล์ .env หรือถ้าไม่มีให้ใช้ Default
    baseURL: process.env.APP_URL || 'http://127.0.0.1:8000',
    
    // ตั้งค่าแคปภาพ/ถ่ายคลิปเมื่อเจอบั๊ก
    trace: 'on-first-retry',
    screenshot: 'only-on-failure',
    video: 'retain-on-failure',
  },

  projects: [
    {
      name: 'chromium',
      use: { ...devices['Desktop Chrome'], viewport: { width: 1366, height: 768 } },
    },
  ],
});
