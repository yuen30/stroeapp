<?php

namespace App\Filament\Widgets;

use App\Filament\Resources\Products\ProductResource;
use App\Models\Product;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;
use Filament\Tables;

class LowStockProductsWidget extends BaseWidget
{
    protected static ?int $sort = 7;

    protected int|string|array $columnSpan = 1;

    public function table(Table $table): Table
    {
        return $table
            ->heading('⚠️ สินค้าใกล้หมด')
            ->query(
                Product::where('stock_quantity', '>', 0)
                    ->where('stock_quantity', '<', 10)
                    ->orderBy('stock_quantity', 'asc')
                    ->limit(10)
            )
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label('ชื่อสินค้า')
                    ->searchable()
                    ->limit(30)
                    ->tooltip(fn($record) => $record->name),
                Tables\Columns\TextColumn::make('stock_quantity')
                    ->label('สต็อก')
                    ->badge()
                    ->color(fn($state) => match (true) {
                        $state === 0 => 'danger',
                        $state < 5 => 'danger',
                        $state < 10 => 'warning',
                        default => 'success',
                    })
                    ->suffix(' หน่วย')
                    ->alignCenter(),
                Tables\Columns\TextColumn::make('reserved_quantity')
                    ->label('ถูกจอง')
                    ->badge()
                    ->color('info')
                    ->suffix(' หน่วย')
                    ->alignCenter()
                    ->default(0),
                Tables\Columns\TextColumn::make('available_stock')
                    ->label('พร้อมใช้')
                    ->badge()
                    ->color(fn($state) => match (true) {
                        $state === 0 => 'danger',
                        $state < 5 => 'warning',
                        default => 'success',
                    })
                    ->suffix(' หน่วย')
                    ->alignCenter(),
            ])
            ->paginated(false);
    }
}
