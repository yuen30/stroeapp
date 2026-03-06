<?php

namespace App\Filament\Resources\TaxInvoices\Pages;

use App\Filament\Resources\TaxInvoices\TaxInvoiceResource;
use App\Models\SaleOrder;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\CreateRecord;

class CreateTaxInvoice extends CreateRecord
{
    protected static string $resource = TaxInvoiceResource::class;

    protected function mutateFormDataBeforeFill(array $data): array
    {
        // ดึง sale_order_id จาก URL query parameter
        $saleOrderId = request()->query('sale_order_id');

        if ($saleOrderId) {
            $saleOrder = SaleOrder::with(['customer', 'company', 'branch'])
                ->find($saleOrderId);

            if ($saleOrder) {
                // ตรวจสอบว่า Sale Order ต้องเป็นสถานะ Confirmed
                if ($saleOrder->status->value !== 'confirmed') {
                    Notification::make()
                        ->warning()
                        ->title('ไม่สามารถสร้างใบกำกับภาษีได้')
                        ->body('ใบสั่งขายต้องอยู่ในสถานะ "ยืนยันแล้ว" เท่านั้น')
                        ->persistent()
                        ->send();

                    $this->redirect($this->getResource()::getUrl('index'));
                    return $data;
                }

                // ตรวจสอบว่ามีใบกำกับภาษีสำหรับ Sale Order นี้แล้วหรือไม่
                if ($saleOrder->taxInvoices()->exists()) {
                    $existingInvoice = $saleOrder->taxInvoices()->first();

                    Notification::make()
                        ->warning()
                        ->title('มีใบกำกับภาษีอยู่แล้ว')
                        ->body("ใบสั่งขายนี้มีใบกำกับภาษีเลขที่ {$existingInvoice->tax_invoice_number} อยู่แล้ว")
                        ->persistent()
                        ->send();

                    $this->redirect(route('filament.store.resources.tax-invoices.view', ['record' => $existingInvoice->id]));
                    return $data;
                }

                // Auto-fill ข้อมูลจาก Sale Order
                $data = [
                    ...$data,
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
                ];
            }
        }

        return $data;
    }

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['created_by'] = auth()->id() ?? 'system';

        return $data;
    }

    protected function afterCreate(): void
    {
        Notification::make()
            ->success()
            ->title('สร้างใบกำกับภาษีสำเร็จ')
            ->body("ใบกำกับภาษีเลขที่ {$this->record->tax_invoice_number} ถูกสร้างเรียบร้อยแล้ว")
            ->send();
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
