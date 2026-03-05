<?php

namespace App\Filament\Resources\TaxInvoices\Pages;

use App\Filament\Resources\TaxInvoices\TaxInvoiceResource;
use App\Models\SaleOrder;
use Filament\Resources\Pages\CreateRecord;

class CreateTaxInvoice extends CreateRecord
{
    protected static string $resource = TaxInvoiceResource::class;

    protected function afterFill(): void
    {
        // ดึง sale_order_id จาก URL query parameter
        $saleOrderId = request()->query('sale_order_id');

        if ($saleOrderId) {
            $saleOrder = SaleOrder::with(['customer', 'company', 'branch'])
                ->find($saleOrderId);

            if ($saleOrder) {
                // Auto-fill ข้อมูลจาก Sale Order
                $this->form->fill([
                    'sale_order_id' => $saleOrder->id,
                    'company_id' => $saleOrder->company_id,
                    'branch_id' => $saleOrder->branch_id,
                    'customer_id' => $saleOrder->customer_id,
                    // ข้อมูลลูกค้า
                    'customer_name' => $saleOrder->customer?->name,
                    'customer_tax_id' => $saleOrder->customer?->tax_id,
                    'customer_address_line1' => $saleOrder->customer?->address_0,
                    'customer_address_line2' => $saleOrder->customer?->address_1,
                    'customer_amphoe' => $saleOrder->customer?->amphoe,
                    'customer_province' => $saleOrder->customer?->province,
                    'customer_postal_code' => $saleOrder->customer?->postal_code,
                    'customer_is_head_office' => $saleOrder->customer?->is_head_office ?? true,
                    'customer_branch_no' => $saleOrder->customer?->branch_no,
                    // ข้อมูลการเงิน
                    'subtotal' => $saleOrder->subtotal,
                    'discount_amount' => $saleOrder->discount_amount,
                    'vat_rate' => 7,
                    'vat_amount' => $saleOrder->vat_amount,
                    'total_amount' => $saleOrder->total_amount,
                    'payment_status' => $saleOrder->payment_status->value,
                    // วันที่เอกสาร
                    'document_date' => now(),
                ]);
            }
        }
    }

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['created_by'] = auth()->id() ?? 'system';

        return $data;
    }

    protected function getHeaderActions(): array
    {
        return [
            $this
                ->getCreateFormAction()
                ->formId('form')
                ->label('สร้างใบกำกับภาษี')
                ->icon('heroicon-o-check')
                ->requiresConfirmation()
                ->modalHeading('ยืนยันการสร้างใบกำกับภาษี')
                ->modalDescription('คุณแน่ใจหรือไม่ว่าต้องการสร้างใบกำกับภาษีนี้?')
                ->modalSubmitActionLabel('ยืนยัน'),
            $this
                ->getCancelFormAction()
                ->label('ยกเลิก')
                ->icon('heroicon-o-x-mark'),
        ];
    }

    protected function getFormActions(): array
    {
        return [];
    }

    protected function getRedirectUrl(): string
    {
        return $this->previousUrl ?? $this->getResource()::getUrl('index');
    }
}
