<?php

namespace App\Observers;

use App\Models\Brand;

class BrandObserver
{
    /**
     * Handle the Brand "creating" event.
     * สร้างรหัสยี่ห้ออัตโนมัติถ้าไม่ได้ระบุ
     */
    public function creating(Brand $brand): void
    {
        if (empty($brand->code)) {
            $brand->code = $this->generateBrandCode();
        }
    }

    /**
     * สร้างรหัสยี่ห้ออัตโนมัติ
     * รูปแบบ: BRD-XXXXXX (X = เลข 6 หัก)
     */
    private function generateBrandCode(): string
    {
        do {
            // หาเลขลำดับถัดไป
            $lastBrand = Brand::withTrashed()
                ->where('code', 'like', 'BRD-%')
                ->orderBy('code', 'desc')
                ->first();

            if ($lastBrand && preg_match('/BRD-(\d+)/', $lastBrand->code, $matches)) {
                $nextNumber = intval($matches[1]) + 1;
            } else {
                $nextNumber = 1;
            }

            $code = 'BRD-' . str_pad($nextNumber, 6, '0', STR_PAD_LEFT);

            // ตรวจสอบว่ารหัสนี้ยังไม่มีในระบบ
            $exists = Brand::withTrashed()->where('code', $code)->exists();
        } while ($exists);

        return $code;
    }
}
