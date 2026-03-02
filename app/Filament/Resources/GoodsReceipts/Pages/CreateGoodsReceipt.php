<?php

namespace App\Filament\Resources\GoodsReceipts\Pages;

use App\Filament\Resources\GoodsReceipts\GoodsReceiptResource;
use Filament\Resources\Pages\CreateRecord;

class CreateGoodsReceipt extends CreateRecord
{
    protected static string $resource = GoodsReceiptResource::class;

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