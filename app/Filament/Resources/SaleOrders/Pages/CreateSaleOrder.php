<?php

namespace App\Filament\Resources\SaleOrders\Pages;

use App\Filament\Resources\SaleOrders\SaleOrderResource;
use Filament\Resources\Pages\CreateRecord;

class CreateSaleOrder extends CreateRecord
{
    protected static string $resource = SaleOrderResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['created_by'] = auth()->id() ?? 'system';

        return $data;
    }

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