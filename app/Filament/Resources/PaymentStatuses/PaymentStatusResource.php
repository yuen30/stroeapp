<?php

namespace App\Filament\Resources\PaymentStatuses;

use App\Filament\Resources\PaymentStatuses\Pages\CreatePaymentStatus;
use App\Filament\Resources\PaymentStatuses\Pages\EditPaymentStatus;
use App\Filament\Resources\PaymentStatuses\Pages\ListPaymentStatuses;
use App\Filament\Resources\PaymentStatuses\Schemas\PaymentStatusForm;
use App\Filament\Resources\PaymentStatuses\Tables\PaymentStatusesTable;
use App\Models\PaymentStatus;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class PaymentStatusResource extends Resource
{
    protected static ?string $model = PaymentStatus::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedCheckCircle;

    protected static string|\UnitEnum|null $navigationGroup = '6. ตั้งค่าระบบ (Settings)';

    protected static ?int $navigationSort = 21;

    protected static ?string $modelLabel = 'สถานะการชำระเงิน';

    protected static ?string $pluralModelLabel = 'สถานะการชำระเงิน';

    protected static ?string $recordTitleAttribute = 'name';

    public static function form(Schema $schema): Schema
    {
        return PaymentStatusForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return PaymentStatusesTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListPaymentStatuses::route('/'),
            'create' => CreatePaymentStatus::route('/create'),
            'edit' => EditPaymentStatus::route('/{record}/edit'),
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
