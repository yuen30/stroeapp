<?php

namespace App\Filament\Resources\Products\Pages;

use App\Filament\Resources\Products\ProductResource;
use Filament\Actions\DeleteAction;
use Filament\Actions\ForceDeleteAction;
use Filament\Actions\RestoreAction;
use Filament\Resources\Pages\EditRecord;

class EditProduct extends EditRecord
{
    protected static string $resource = ProductResource::class;

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