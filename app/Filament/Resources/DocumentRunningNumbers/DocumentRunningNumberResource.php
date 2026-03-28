<?php

namespace App\Filament\Resources\DocumentRunningNumbers;

use App\Filament\Resources\DocumentRunningNumbers\Pages\CreateDocumentRunningNumber;
use App\Filament\Resources\DocumentRunningNumbers\Pages\EditDocumentRunningNumber;
use App\Filament\Resources\DocumentRunningNumbers\Pages\ListDocumentRunningNumbers;
use App\Filament\Resources\DocumentRunningNumbers\Pages\ViewDocumentRunningNumber;
use App\Filament\Resources\DocumentRunningNumbers\Schemas\DocumentRunningNumberForm;
use App\Filament\Resources\DocumentRunningNumbers\Schemas\DocumentRunningNumberInfolist;
use App\Filament\Resources\DocumentRunningNumbers\Tables\DocumentRunningNumbersTable;
use App\Models\DocumentRunningNumber;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class DocumentRunningNumberResource extends Resource
{
    protected static ?string $model = DocumentRunningNumber::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedHashtag;

    protected static string|\UnitEnum|null $navigationGroup = '6. ตั้งค่าระบบ (Settings)';

    protected static ?int $navigationSort = 13;

    protected static ?string $modelLabel = 'เลขที่เอกสาร';

    protected static ?string $pluralModelLabel = 'เลขที่เอกสาร';

    protected static ?string $recordTitleAttribute = 'document_type';

    public static function form(Schema $schema): Schema
    {
        return DocumentRunningNumberForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return DocumentRunningNumberInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return DocumentRunningNumbersTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListDocumentRunningNumbers::route('/'),
            'create' => CreateDocumentRunningNumber::route('/create'),
            'view' => ViewDocumentRunningNumber::route('/{record}'),
            'edit' => EditDocumentRunningNumber::route('/{record}/edit'),
        ];
    }
}
