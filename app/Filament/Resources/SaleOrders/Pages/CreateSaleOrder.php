<?php

namespace App\Filament\Resources\SaleOrders\Pages;

use App\Filament\Resources\SaleOrders\SaleOrderResource;
use Filament\Resources\Pages\CreateRecord;
use Filament\Support\Icons\Heroicon;

class CreateSaleOrder extends CreateRecord
{
    protected static string $resource = SaleOrderResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['created_by'] = auth()->id() ?? 'system';
        $data['status'] = 'draft';  // บังคับให้เป็น draft เสมอ
        return $data;
    }

    protected function getHeaderActions(): array
    {
        return [
            $this->getCreateFormAction()->formId('form')->label('สร้างใบส่งสินค้า')->icon(Heroicon::Check)->color('success'),
            $this->getCancelFormAction()->label('ยกเลิก')->icon(Heroicon::XMark)->color('gray'),
        ];
    }

    protected function getFormActions(): array
    {
        return [];
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('view', ['record' => $this->getRecord()]);
    }
}
