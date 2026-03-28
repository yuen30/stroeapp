<?php

namespace App\Filament\Resources\PaymentStatuses\Pages;

use App\Filament\Resources\PaymentStatuses\PaymentStatusResource;
use Filament\Resources\Pages\CreateRecord;
use Filament\Support\Icons\Heroicon;

class CreatePaymentStatus extends CreateRecord
{
    protected static string $resource = PaymentStatusResource::class;

    protected function getHeaderActions(): array
    {
        return [
            $this
                ->getCreateFormAction()
                ->formId('form')
                ->label('สร้างสถานะการชำระเงิน')
                ->icon(Heroicon::Check)
                ->color('success'),
            $this
                ->getCancelFormAction()
                ->label('ยกเลิก')
                ->icon(Heroicon::XMark)
                ->color('gray'),
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
