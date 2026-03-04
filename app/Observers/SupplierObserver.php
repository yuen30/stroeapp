<?php

namespace App\Observers;

use App\Models\Supplier;

class SupplierObserver
{
    /**
     * Handle the Supplier "creating" event.
     */
    public function creating(Supplier $supplier): void
    {
        // สร้างรหัสผู้จำหน่ายอัตโนมัติถ้ายังไม่มี
        if (empty($supplier->code)) {
            $latestSupplier = Supplier::withTrashed()
                ->where('code', 'like', 'SUP-%')
                ->orderBy('code', 'desc')
                ->first();

            if ($latestSupplier && preg_match('/SUP-(\d+)/', $latestSupplier->code, $matches)) {
                $nextNumber = intval($matches[1]) + 1;
            } else {
                $nextNumber = 1;
            }

            $supplier->code = 'SUP-' . str_pad($nextNumber, 6, '0', STR_PAD_LEFT);
        }

        // กำหนด company_id ตาม user ที่ login ถ้ายังไม่มี
        if (empty($supplier->company_id)) {
            $supplier->company_id = auth()->user()?->company_id;
        }

        // กำหนดสถานะเริ่มต้น
        if (!isset($supplier->is_active)) {
            $supplier->is_active = true;
        }
    }
}
