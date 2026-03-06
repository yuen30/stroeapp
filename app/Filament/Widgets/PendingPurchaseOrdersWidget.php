<?php

namespace App\Filament\Widgets;

use App\Enums\OrderStatus;
use App\Filament\Resources\PurchaseOrders\PurchaseOrderResource;
use App\Models\PurchaseOrder;
use Filament\Actions\ViewAction;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;
use Filament\Tables;

class PendingPurchaseOrdersWidget extends BaseWidget
{
    protected static ?int $sort = 8;

    protected int|string|array $columnSpan = 1;

    public function table(Table $table): Table
    {
        return $table
            ->heading('📝 ใบสั่งซื้อรอดำเนินการ')
            ->query(
                PurchaseOrder::query()
                    ->with(['supplier'])
                    ->whereIn('status', [OrderStatus::Draft, OrderStatus::Confirmed])
                    ->latest()
                    ->limit(10)
            )
            ->columns([
                Tables\Columns\TextColumn::make('document_no')
                    ->label('เลขที่')
                    ->searchable()
                    ->icon('heroicon-o-document-text')
                    ->limit(15),
                Tables\Columns\TextColumn::make('supplier.name')
                    ->label('ซัพพลายเออร์')
                    ->searchable()
                    ->limit(20)
                    ->tooltip(fn($record) => $record->supplier->name ?? 'N/A'),
                Tables\Columns\TextColumn::make('total_amount')
                    ->label('ยอดรวม')
                    ->money('THB', locale: 'th')
                    ->alignEnd(),
                Tables\Columns\TextColumn::make('status')
                    ->label('สถานะ')
                    ->badge(),
                Tables\Columns\TextColumn::make('order_date')
                    ->label('วันที่')
                    ->date('d M Y')
                    ->sortable(),
            ])
            ->paginated(false);
    }
}
