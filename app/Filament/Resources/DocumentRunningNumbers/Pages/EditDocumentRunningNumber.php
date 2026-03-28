<?php

namespace App\Filament\Resources\DocumentRunningNumbers\Pages;

use App\Filament\Resources\DocumentRunningNumbers\DocumentRunningNumberResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;
use Filament\Support\Icons\Heroicon;

class EditDocumentRunningNumber extends EditRecord
{
    protected static string $resource = DocumentRunningNumberResource::class;

    protected function getHeaderActions(): array
    {
        return [
            $this
                ->getSaveFormAction()
                ->formId('form')
                ->label('บันทึกการเปลี่ยนแปลง')
                ->icon(Heroicon::Check)
                ->color('success'),
            $this
                ->getCancelFormAction()
                ->label('ยกเลิก')
                ->icon(Heroicon::XMark)
                ->color('gray'),
            DeleteAction::make()
                ->label('ลบ')
                ->icon(Heroicon::Trash)
                ->color('danger')
                ->requiresConfirmation()
                ->modalHeading('ลบรูปแบบเอกสาร')
                ->modalDescription('คุณแน่ใจหรือไม่ว่าต้องการลบรูปแบบนี้?')
                ->modalSubmitActionLabel('ลบ'),
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
