<?php

namespace App\Filament\Resources\Brands\Pages;

use App\Filament\Resources\Brands\BrandResource;
use Filament\Actions\DeleteAction;
use Filament\Actions\ForceDeleteAction;
use Filament\Actions\RestoreAction;
use Filament\Resources\Pages\EditRecord;
use Filament\Support\Icons\Heroicon;

class EditBrand extends EditRecord
{
    protected static string $resource = BrandResource::class;

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
                ->modalHeading('ลบยี่ห้อ')
                ->modalDescription('คุณแน่ใจหรือไม่ว่าต้องการลบยี่ห้อนี้?')
                ->modalSubmitActionLabel('ลบ'),
            ForceDeleteAction::make()
                ->label('ลบถาวร')
                ->icon(Heroicon::Trash)
                ->color('danger')
                ->requiresConfirmation()
                ->modalHeading('ลบยี่ห้อถาวร')
                ->modalDescription('คุณแน่ใจหรือไม่? การลบถาวรไม่สามารถกู้คืนได้!')
                ->modalSubmitActionLabel('ลบถาวร'),
            RestoreAction::make()
                ->label('กู้คืน')
                ->icon(Heroicon::ArrowUturnLeft)
                ->color('success')
                ->successNotificationTitle('กู้คืนยี่ห้อสำเร็จ'),
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
