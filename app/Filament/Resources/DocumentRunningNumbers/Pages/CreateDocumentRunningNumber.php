<?php

namespace App\Filament\Resources\DocumentRunningNumbers\Pages;

use App\Filament\Resources\DocumentRunningNumbers\DocumentRunningNumberResource;
use Filament\Resources\Pages\CreateRecord;
use Filament\Support\Icons\Heroicon;

class CreateDocumentRunningNumber extends CreateRecord
{
    protected static string $resource = DocumentRunningNumberResource::class;

    protected function getHeaderActions(): array
    {
        return [
            $this
                ->getCreateFormAction()
                ->formId('form')
                ->label('สร้างรูปแบบ')
                ->icon(Heroicon::Check)
                ->color('success'),
            $this
                ->getCancelFormAction()
                ->label('ยกเลิก')
                ->icon(Heroicon::XMark)
                ->color('gray'),
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
