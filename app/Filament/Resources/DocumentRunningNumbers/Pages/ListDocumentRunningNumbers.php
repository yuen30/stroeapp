<?php

namespace App\Filament\Resources\DocumentRunningNumbers\Pages;

use App\Filament\Resources\DocumentRunningNumbers\DocumentRunningNumberResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
use Filament\Support\Icons\Heroicon;

class ListDocumentRunningNumbers extends ListRecords
{
    protected static string $resource = DocumentRunningNumberResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()
                ->label('เพิ่มรูปแบบ')
                ->icon(Heroicon::PlusCircle),
        ];
    }
}
