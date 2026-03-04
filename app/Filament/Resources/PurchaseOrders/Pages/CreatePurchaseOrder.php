<?php

namespace App\Filament\Resources\PurchaseOrders\Pages;

use App\Filament\Resources\PurchaseOrders\PurchaseOrderResource;
use Filament\Resources\Pages\CreateRecord;
use Filament\Support\Icons\Heroicon;

class CreatePurchaseOrder extends CreateRecord
{
    protected static string $resource = PurchaseOrderResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        // กำหนดค่าเริ่มต้น
        $data['created_by'] = auth()->id() ?? 'system';
        $data['status'] = 'draft';  // บันทึกสถานะเป็น draft อัตโนมัติ
        $data['subtotal'] = 0;
        $data['discount_amount'] = 0;
        $data['vat_amount'] = 0;
        $data['total_amount'] = 0;

        return $data;
    }

    protected function getHeaderActions(): array
    {
        return [
            $this->getCreateFormAction()->formId('form')->label('สร้างใบสั่งซื้อ')->icon(Heroicon::Check)->color('success'),
            $this->getCancelFormAction()->label('ยกเลิก')->icon(Heroicon::XMark)->color('gray'),
        ];
    }

    protected function getFormActions(): array
    {
        return [];
    }

    protected function getCreatedNotificationTitle(): ?string
    {
        return 'สร้างใบสั่งซื้อสำเร็จ';
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('view', ['record' => $this->getRecord()]);
    }
}
