<?php

namespace App\Filament\Resources\Users\Pages;

use App\Filament\Resources\Users\UserResource;
use Filament\Actions\DeleteAction;
use Filament\Actions\ForceDeleteAction;
use Filament\Actions\RestoreAction;
use Filament\Resources\Pages\EditRecord;

class EditUser extends EditRecord
{
    protected static string $resource = UserResource::class;

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