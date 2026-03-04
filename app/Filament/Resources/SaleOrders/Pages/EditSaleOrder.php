<?php

namespace App\Filament\Resources\SaleOrders\Pages;

use App\Filament\Resources\SaleOrders\SaleOrderResource;
use Filament\Actions\DeleteAction;
use Filament\Actions\ForceDeleteAction;
use Filament\Actions\RestoreAction;
use Filament\Resources\Pages\EditRecord;
use Filament\Support\Icons\Heroicon;

class EditSaleOrder extends EditRecord
{
    protected static string $resource = SaleOrderResource::class;

    protected function getHeaderActions(): array
    {
        return [
            $this->getSaveFormAction()->formId('form')->label('บันทึกการเปลี่ยนแปลง')->icon(Heroicon::Check)->color('success'),
            $this->getCancelFormAction()->label('ยกเลิก')->icon(Heroicon::XMark)->color('gray'),
            DeleteAction::make()->label('ลบ')->icon(Heroicon::Trash)->color('danger'),
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
