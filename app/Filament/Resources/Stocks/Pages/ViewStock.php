<?php

namespace App\Filament\Resources\Stocks\Pages;

use App\Filament\Resources\Stocks\Schemas\StockInfolist;
use App\Filament\Resources\Stocks\StockResource;
use Filament\Resources\Pages\ViewRecord;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Actions;

class ViewStock extends ViewRecord
{
    protected static string $resource = StockResource::class;

    public function infolist(Schema $schema): Schema
    {
        return StockInfolist::configure($schema);
    }

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make()
                ->label('แก้ไข')
                ->icon(Heroicon::PencilSquare),
        ];
    }
}
