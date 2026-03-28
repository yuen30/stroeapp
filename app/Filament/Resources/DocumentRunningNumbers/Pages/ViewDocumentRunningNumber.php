<?php

namespace App\Filament\Resources\DocumentRunningNumbers\Pages;

use App\Filament\Resources\DocumentRunningNumbers\DocumentRunningNumberResource;
use App\Filament\Resources\DocumentRunningNumbers\Schemas\DocumentRunningNumberInfolist;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;
use Filament\Schemas\Schema;

class ViewDocumentRunningNumber extends ViewRecord
{
    protected static string $resource = DocumentRunningNumberResource::class;

    public function infolist(Schema $schema): Schema
    {
        return DocumentRunningNumberInfolist::configure($schema);
    }

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make()
                ->label('แก้ไข')
                ->icon('heroicon-o-pencil')
                ->color('primary'),
        ];
    }
}
