<?php

namespace App\Observers;

use App\Models\Category;

class CategoryObserver
{
    /**
     * Handle the Category "creating" event.
     * สร้างรหัสหมวดหมู่อัตโนมัติถ้าไม่ได้ระบุ
     */
    public function creating(Category $category): void
    {
        if (empty($category->code)) {
            $category->code = $this->generateCategoryCode();
        }
    }

    /**
     * สร้างรหัสหมวดหมู่อัตโนมัติ
     * รูปแบบ: CAT-XXXXXX (X = เลข 6 หัก)
     */
    private function generateCategoryCode(): string
    {
        do {
            // หาเลขลำดับถัดไป
            $lastCategory = Category::withTrashed()
                ->where('code', 'like', 'CAT-%')
                ->orderBy('code', 'desc')
                ->first();

            if ($lastCategory && preg_match('/CAT-(\d+)/', $lastCategory->code, $matches)) {
                $nextNumber = intval($matches[1]) + 1;
            } else {
                $nextNumber = 1;
            }

            $code = 'CAT-' . str_pad($nextNumber, 6, '0', STR_PAD_LEFT);

            // ตรวจสอบว่ารหัสนี้ยังไม่มีในระบบ
            $exists = Category::withTrashed()->where('code', $code)->exists();
        } while ($exists);

        return $code;
    }
}
