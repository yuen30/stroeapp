<?php

namespace App\Filament\Widgets;

use App\Filament\Resources\SaleOrders\SaleOrderResource;
use App\Models\SaleOrder;
use Filament\Actions\ViewAction;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;
use Filament\Tables;
use Illuminate\Support\Number;

class RecentSaleOrdersWidget extends BaseWidget
{
    protected static ?int $sort = 6;

    protected int|string|array $columnSpan = 'full';

    public function table(Table $table): Table
    {
        return $table
            ->heading('📋 ใบส่งสินค้าล่าสุด')
            ->query(
                SaleOrder::query()
                    ->with(['customer', 'creator'])
                    ->latest()
                    ->limit(10)
            )
            ->columns([
                Tables\Columns\TextColumn::make('invoice_number')
                    ->label('เลขที่')
                    ->searchable()
                    ->sortable()
                    ->icon('heroicon-o-document-text')
                    ->copyable(),
                Tables\Columns\TextColumn::make('customer.name')
                    ->label('ลูกค้า')
                    ->searchable()
                    ->sortable()
                    ->icon('heroicon-o-user'),
                Tables\Columns\TextColumn::make('total_amount')
                    ->label('ยอดรวม')
                    ->money('THB', locale: 'th')
                    ->sortable()
                    ->alignEnd(),
                Tables\Columns\TextColumn::make('status')
                    ->label('สถานะ')
                    ->badge()
                    ->sortable(),
                Tables\Columns\TextColumn::make('payment_status')
                    ->label('การชำระเงิน')
                    ->badge()
                    ->sortable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('วันที่สร้าง')
                    ->dateTime('d M Y H:i')
                    ->sortable()
                    ->since()
                    ->description(fn($record) => $record->created_at->format('d M Y H:i')),
            ])
            ->paginated(false);
    }
}
