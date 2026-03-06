<?php

namespace App\Filament\Resources\TaxInvoices\Pages;

use App\Filament\Resources\TaxInvoices\TaxInvoiceResource;
use Filament\Actions\DeleteAction;
use Filament\Actions\ForceDeleteAction;
use Filament\Actions\RestoreAction;
use Filament\Resources\Pages\EditRecord;

class EditTaxInvoice extends EditRecord
{
    protected static string $resource = TaxInvoiceResource::class;

    protected function getHeaderActions(): array
    {
        return [
            $this
                ->getSaveFormAction()
                ->formId('form')
                ->label('บันทึก')
                ->icon('heroicon-o-check')
                ->requiresConfirmation()
                ->modalHeading('ยืนยันการบันทึก')
                ->modalDescription('คุณแน่ใจหรือไม่ว่าต้องการบันทึกการเปลี่ยนแปลง?')
                ->modalSubmitActionLabel('ยืนยัน'),
            $this
                ->getCancelFormAction()
                ->label('ยกเลิก')
                ->icon('heroicon-o-x-mark'),
            DeleteAction::make()
                ->label('ลบ')
                ->icon('heroicon-o-trash')
                ->requiresConfirmation()
                ->modalHeading('ลบใบกำกับภาษี')
                ->modalDescription('คุณแน่ใจหรือไม่ว่าต้องการลบใบกำกับภาษีนี้?')
                ->modalSubmitActionLabel('ยืนยันการลบ'),
            ForceDeleteAction::make()
                ->label('ลบถาวร')
                ->icon('heroicon-o-trash')
                ->requiresConfirmation()
                ->modalHeading('ลบใบกำกับภาษีถาวร')
                ->modalDescription('คุณแน่ใจหรือไม่ว่าต้องการลบใบกำกับภาษีถาวร? การกระทำนี้ไม่สามารถย้อนกลับได้')
                ->modalSubmitActionLabel('ยืนยันการลบถาวร'),
            RestoreAction::make()
                ->label('กู้คืน')
                ->icon('heroicon-o-arrow-uturn-left')
                ->requiresConfirmation()
                ->modalHeading('กู้คืนใบกำกับภาษี')
                ->modalDescription('คุณแน่ใจหรือไม่ว่าต้องการกู้คืนใบกำกับภาษีนี้?')
                ->modalSubmitActionLabel('ยืนยันการกู้คืน'),
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
