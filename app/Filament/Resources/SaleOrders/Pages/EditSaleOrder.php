<?php

namespace App\Filament\Resources\SaleOrders\Pages;

use App\Filament\Resources\SaleOrders\SaleOrderResource;
use Filament\Actions\DeleteAction;
use Filament\Actions\ForceDeleteAction;
use Filament\Actions\RestoreAction;
use Filament\Resources\Pages\EditRecord;

class EditSaleOrder extends EditRecord
{
    protected static string $resource = SaleOrderResource::class;

    protected function getHeaderActions(): array
    {
        return [
            $this->getSaveFormAction()->formId('form'),
            $this->getCancelFormAction(),
            DeleteAction::make()->label('ลบ')->icon('heroicon-o-trash'),
            ForceDeleteAction::make()->label('ลบถาวร')->icon('heroicon-o-trash'),
            RestoreAction::make()->label('กู้คืน')->icon('heroicon-o-arrow-uturn-left'),
        ];
    }

    protected function getFormActions(): array
    {
        return [];
    }

}