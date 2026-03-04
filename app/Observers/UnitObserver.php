<?php

namespace App\Observers;

use App\Models\Unit;

class UnitObserver
{
    /**
     * Handle the Unit "creating" event.
     * สร้างรหัสหน่วยนับอัตโนมัติถ้าไม่ได้ระบุ
     */
    public function creating(Unit $unit): void
    {
        if (empty($unit->code)) {
            $unit->code = $this->generateUnitCode();
        }
    }

    /**
     * สร้างรหัสหน่วยนับอัตโนมัติ
     * รูปแบบ: UNIT-XXXXXX (X = เลข 6 หัก)
     */
    private function generateUnitCode(): string
    {
        do {
            // หาเลขลำดับถัดไป
            $lastUnit = Unit::withTrashed()
                ->where('code', 'like', 'UNIT-%')
                ->orderBy('code', 'desc')
                ->first();

            if ($lastUnit && preg_match('/UNIT-(\d+)/', $lastUnit->code, $matches)) {
                $nextNumber = intval($matches[1]) + 1;
            } else {
                $nextNumber = 1;
            }

            $code = 'UNIT-' . str_pad($nextNumber, 6, '0', STR_PAD_LEFT);

            // ตรวจสอบว่ารหัสนี้ยังไม่มีในระบบ
            $exists = Unit::withTrashed()->where('code', $code)->exists();
        } while ($exists);

        return $code;
    }
}
