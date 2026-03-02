<?php

namespace App\Filament\Resources\Users\Pages;

use App\Filament\Resources\Users\UserResource;
use Filament\Resources\Pages\CreateRecord;

class CreateUser extends CreateRecord
{
    protected static string $resource = UserResource::class;

    protected function getHeaderActions(): array
    {
        return [
            $this->getCreateFormAction()->formId('form'),
            $this->getCancelFormAction(),
        ];
    }


    protected function getFormActions(): array
    {
        return [];
    }

}