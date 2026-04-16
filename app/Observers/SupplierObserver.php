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
        // company_id auto-fill is handled by DocumentObserver

        // กำหนดสถานะเริ่มต้น
        if (!isset($supplier->is_active)) {
            $supplier->is_active = true;
        }
    }
}
