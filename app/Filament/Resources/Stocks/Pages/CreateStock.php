<?php

namespace App\Filament\Resources\Stocks\Pages;

use App\Filament\Resources\Stocks\StockResource;
use Filament\Resources\Pages\CreateRecord;

class CreateStock extends CreateRecord
{
    protected static string $resource = StockResource::class;

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