<?php

namespace App\Filament\Resources\PurchaseOrders\Pages;

use App\Filament\Resources\PurchaseOrders\PurchaseOrderResource;
use Filament\Actions\DeleteAction;
use Filament\Actions\ForceDeleteAction;
use Filament\Actions\RestoreAction;
use Filament\Resources\Pages\EditRecord;
use Filament\Support\Icons\Heroicon;

class EditPurchaseOrder extends EditRecord
{
    protected static string $resource = PurchaseOrderResource::class;

    protected function getHeaderActions(): array
    {
        return [
            $this->getSaveFormAction()->formId('form')->label('บันทึกการเปลี่ยนแปลง')->icon(Heroicon::Check)->color('success'),
            $this->getCancelFormAction()->label('ยกเลิก')->icon(Heroicon::XMark)->color('gray'),
            DeleteAction::make()->label('ลบ')->icon(Heroicon::Trash)->color('danger'),
            ForceDeleteAction::make()->label('ลบถาวร')->icon(Heroicon::Trash)->color('danger'),
            RestoreAction::make()->label('กู้คืน')->icon(Heroicon::ArrowUturnLeft)->color('success'),
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
