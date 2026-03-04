<?php

namespace App\Filament\Resources\GoodsReceipts\Pages;

use App\Filament\Resources\GoodsReceipts\GoodsReceiptResource;
use Filament\Resources\Pages\CreateRecord;

class CreateGoodsReceipt extends CreateRecord
{
    protected static string $resource = GoodsReceiptResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['created_by'] = auth()->id() ?? 'system';
        $data['status'] = \App\Enums\OrderStatus::Draft;

        return $data;
    }

    protected function getHeaderActions(): array
    {
        return [
            $this
                ->getCreateFormAction()
                ->formId('form')
                ->label('สร้างใบรับสินค้า')
                ->icon('heroicon-o-check')
                ->requiresConfirmation()
                ->modalHeading('ยืนยันการสร้างใบรับสินค้า')
                ->modalDescription('คุณแน่ใจหรือไม่ว่าต้องการสร้างใบรับสินค้านี้?')
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
        return $this->getResource()::getUrl('view', ['record' => $this->record]);
    }

    protected function getCreatedNotificationTitle(): ?string
    {
        return 'สร้างใบรับสินค้าสำเร็จ';
    }
}
