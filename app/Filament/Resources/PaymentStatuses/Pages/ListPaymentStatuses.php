<?php

namespace App\Filament\Resources\PaymentStatuses\Pages;

use App\Filament\Resources\PaymentStatuses\PaymentStatusResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
use Filament\Support\Icons\Heroicon;

class ListPaymentStatuses extends ListRecords
{
    protected static string $resource = PaymentStatusResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()
                ->label('เพิ่มข้อมูล')
                ->icon(Heroicon::PlusCircle),
        ];
    }
}
