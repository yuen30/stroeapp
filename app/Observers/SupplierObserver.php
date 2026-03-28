<?php

namespace App\Observers;

use App\Models\Supplier;
use Illuminate\Support\Facades\Auth;

class SupplierObserver
{
    /**
     * Handle the Supplier "creating" event.
     */
    public function creating(Supplier $supplier): void
    {
        // รหัสผู้จำหน่ายจะถูกจัดการโดย DocumentObserver อัตโนมัติ

        // กำหนด company_id ตาม user ที่ login ถ้ายังไม่มี
        if (empty($supplier->company_id)) {
            $supplier->company_id = Auth::user()?->company_id;
        }

        // กำหนดสถานะเริ่มต้น
        if (!isset($supplier->is_active)) {
            $supplier->is_active = true;
        }
    }
}
