<?php

namespace App\Filament\Resources\Companies\Pages;

use App\Filament\Resources\Companies\CompanyResource;
use Filament\Actions\DeleteAction;
use Filament\Actions\ForceDeleteAction;
use Filament\Actions\RestoreAction;
use Filament\Resources\Pages\EditRecord;

class EditCompany extends EditRecord
{
    protected static string $resource = CompanyResource::class;

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