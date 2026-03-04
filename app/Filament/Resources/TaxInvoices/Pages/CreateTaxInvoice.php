<?php

namespace App\Filament\Resources\TaxInvoices\Pages;

use App\Filament\Resources\TaxInvoices\TaxInvoiceResource;
use Filament\Resources\Pages\CreateRecord;

class CreateTaxInvoice extends CreateRecord
{
    protected static string $resource = TaxInvoiceResource::class;

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
