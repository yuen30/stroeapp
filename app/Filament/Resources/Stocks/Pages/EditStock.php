<?php

namespace App\Filament\Resources\Stocks\Pages;

use App\Filament\Resources\Stocks\StockResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;
use Filament\Schemas\Components\Callout;

class EditStock extends EditRecord
{
    protected static string $resource = StockResource::class;

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
                ->modalHeading('ลบสต็อกสินค้า')
                ->modalDescription('คุณแน่ใจหรือไม่ว่าต้องการลบสต็อกสินค้านี้?')
                ->modalSubmitActionLabel('ยืนยันการลบ'),
        ];
    }

    protected function getFormActions(): array
    {
        return [];
    }

    public function getHeaderWidgets(): array
    {
        return [
            Callout::make('⚠️ คำเตือน')
                ->description('การแก้ไขสต็อกสินค้าโดยตรงอาจส่งผลต่อความถูกต้องของข้อมูล แนะนำให้ใช้ระบบรับ-จ่ายสินค้าแทน')
                ->warning()
                ->columnSpanFull(),
        ];
    }

    protected function getRedirectUrl(): string
    {
        return $this->previousUrl ?? $this->getResource()::getUrl('index');
    }
}
