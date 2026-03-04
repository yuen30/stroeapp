<?php

namespace App\Observers;

use App\Models\TaxInvoice;
use App\Services\DocumentNumberService;

class TaxInvoiceObserver
{
    public function __construct(
        private DocumentNumberService $documentNumberService
    ) {}

    /**
     * Handle the TaxInvoice "creating" event.
     */
    public function creating(TaxInvoice $taxInvoice): void
    {
        // สร้างเลขที่เอกสารอัตโนมัติถ้ายังไม่มี
        if (empty($taxInvoice->invoice_number)) {
            $taxInvoice->invoice_number = $this->documentNumberService->generate(
                'INV',
                $taxInvoice->company_id ?? null,
                $taxInvoice->branch_id ?? null
            );
        }
    }
}
