<?php

namespace App\Filament\Resources\TaxInvoices;

use App\Filament\Resources\TaxInvoices\Pages\CreateTaxInvoice;
use App\Filament\Resources\TaxInvoices\Pages\EditTaxInvoice;
use App\Filament\Resources\TaxInvoices\Pages\ListTaxInvoices;
use App\Filament\Resources\TaxInvoices\Pages\ViewTaxInvoice;
use App\Filament\Resources\TaxInvoices\Schemas\TaxInvoiceForm;
use App\Filament\Resources\TaxInvoices\Tables\TaxInvoicesTable;
use App\Models\TaxInvoice;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use App\Filament\Resources\TaxInvoices\Schemas\TaxInvoiceInfolist;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class TaxInvoiceResource extends Resource
{
    protected static ?string $model = TaxInvoice::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedDocumentText;

    protected static string|\UnitEnum|null $navigationGroup = '1. ระบบขาย (Sales)';

    protected static ?int $navigationSort = 2;

    protected static ?string $modelLabel = 'ใบกำกับภาษี';

    protected static ?string $pluralModelLabel = 'ใบกำกับภาษี';

    protected static ?string $recordTitleAttribute = 'document_no';

    public static function form(Schema $schema): Schema
    {
        return TaxInvoiceForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return TaxInvoicesTable::configure($table);
    }

    public static function infolist(Schema $schema): Schema
    {
        return TaxInvoiceInfolist::configure($schema);
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
            'index' => ListTaxInvoices::route('/'),
            'create' => CreateTaxInvoice::route('/create'),
            'view' => ViewTaxInvoice::route('/{record}'),
            'edit' => EditTaxInvoice::route('/{record}/edit'),
        ];
    }

    public static function getRecordRouteBindingEloquentQuery(): Builder
    {
        return parent::getRecordRouteBindingEloquentQuery()
            ->withoutGlobalScopes([
                SoftDeletingScope::class,
            ]);
    }
}
