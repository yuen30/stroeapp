<?php

namespace App\Filament\Resources\Customers\Pages;

use App\Filament\Resources\Customers\CustomerResource;
use Filament\Actions\DeleteAction;
use Filament\Actions\ForceDeleteAction;
use Filament\Actions\RestoreAction;
use Filament\Resources\Pages\EditRecord;
use Filament\Support\Icons\Heroicon;

class EditCustomer extends EditRecord
{
    protected static string $resource = CustomerResource::class;

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
                ->modalHeading('ลบลูกค้า')
                ->modalDescription('คุณแน่ใจหรือไม่ว่าต้องการลบลูกค้านี้?')
                ->modalSubmitActionLabel('ลบ'),
            ForceDeleteAction::make()
                ->label('ลบถาวร')
                ->icon(Heroicon::Trash)
                ->color('danger')
                ->requiresConfirmation()
                ->modalHeading('ลบลูกค้าถาวร')
                ->modalDescription('คุณแน่ใจหรือไม่? การลบถาวรไม่สามารถกู้คืนได้!')
                ->modalSubmitActionLabel('ลบถาวร'),
            RestoreAction::make()
                ->label('กู้คืน')
                ->icon(Heroicon::ArrowUturnLeft)
                ->color('success')
                ->successNotificationTitle('กู้คืนลูกค้าสำเร็จ'),
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
