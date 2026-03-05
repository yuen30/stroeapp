<?php

namespace App\Filament\Pages\Reports;

use App\Models\Product;
use Filament\Pages\Page;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class LowStockReport extends Page implements HasTable
{
    use InteractsWithTable;

    protected static string|\BackedEnum|null $navigationIcon = Heroicon::OutlinedExclamationTriangle;

    protected string $view = 'filament.pages.reports.low-stock-report';

    protected static string|\UnitEnum|null $navigationGroup = '3. คลังสินค้า (Inventory)';

    protected static ?int $navigationSort = 8;

    protected static ?string $title = 'รายงานสินค้า Stock ต่ำ';

    protected static ?string $navigationLabel = 'สินค้า Stock ต่ำ';

    protected function getHeaderWidgets(): array
    {
        return [
            \App\Filament\Pages\Reports\Widgets\LowStockOverview::class,
        ];
    }

    public function table(Table $table): Table
    {
        return $table
            ->query(
                Product::query()
                    ->where('is_active', true)
                    ->where('min_stock', '>', 0)
                    ->whereColumn('stock_quantity', '<=', 'min_stock')
                    ->with(['unit', 'category', 'brand', 'branch'])
            )
            ->columns([
                TextColumn::make('row_id')
                    ->label('#')
                    ->rowIndex()
                    ->alignCenter(),
                TextColumn::make('code')
                    ->label('รหัสสินค้า')
                    ->icon(Heroicon::Hashtag)
                    ->searchable()
                    ->sortable(),
                TextColumn::make('name')
                    ->label('ชื่อสินค้า')
                    ->icon(Heroicon::Cube)
                    ->searchable()
                    ->sortable()
                    ->weight('bold')
                    ->description(fn (Product $record): string => $record->category?->name ?? 'ไม่มีหมวดหมู่'),
                TextColumn::make('brand.name')
                    ->label('แบรนด์')
                    ->icon(Heroicon::BuildingStorefront)
                    ->sortable()
                    ->toggleable(),
                TextColumn::make('branch.name')
                    ->label('สาขา')
                    ->icon(Heroicon::BuildingOffice)
                    ->sortable()
                    ->toggleable(),
                TextColumn::make('stock_quantity')
                    ->label('จำนวนคงเหลือ')
                    ->icon(Heroicon::ArchiveBox)
                    ->formatStateUsing(fn($state, $record) => number_format($state) . ' ' . $record->unit->name)
                    ->badge()
                    ->color(fn ($state) => $state <= 0 ? 'danger' : 'warning')
                    ->sortable(),
                TextColumn::make('min_stock')
                    ->label('Stock ขั้นต่ำ')
                    ->icon(Heroicon::ArrowTrendingDown)
                    ->formatStateUsing(fn($state, $record) => number_format($state) . ' ' . $record->unit->name)
                    ->badge()
                    ->color('gray')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('shortage')
                    ->label('ขาด (ชิ้น)')
                    ->icon(Heroicon::ExclamationCircle)
                    ->getStateUsing(fn($record) => max($record->min_stock - $record->stock_quantity, 0))
                    ->formatStateUsing(fn($state, $record) => number_format($state) . ' ' . $record->unit->name)
                    ->badge()
                    ->color('danger')
                    ->sortable(query: function (Builder $query, string $direction): Builder {
                        return $query->orderByRaw("(min_stock - stock_quantity) {$direction}");
                    }),
                TextColumn::make('restock_cost')
                    ->label('มูลค่าเติมสต็อก (ประเมิน)')
                    ->icon(Heroicon::CurrencyDollar)
                    ->getStateUsing(fn($record) => max($record->min_stock - $record->stock_quantity, 0) * $record->cost_price)
                    ->money('THB')
                    ->color('primary')
                    ->weight('bold')
                    ->sortable(query: function (Builder $query, string $direction): Builder {
                        return $query->orderByRaw("((min_stock - stock_quantity) * cost_price) {$direction}");
                    }),
            ])
            ->defaultSort('shortage', 'desc')
            ->filters([
                \Filament\Tables\Filters\SelectFilter::make('branch_id')
                    ->label('สาขา')
                    ->relationship('branch', 'name')
                    ->searchable()
                    ->preload()
                    ->native(false),
                \Filament\Tables\Filters\SelectFilter::make('category_id')
                    ->label('หมวดหมู่')
                    ->relationship('category', 'name')
                    ->searchable()
                    ->preload()
                    ->native(false),
                \Filament\Tables\Filters\SelectFilter::make('brand_id')
                    ->label('แบรนด์')
                    ->relationship('brand', 'name')
                    ->searchable()
                    ->preload()
                    ->native(false),
                \Filament\Tables\Filters\TernaryFilter::make('is_out_of_stock')
                    ->label('สถานะสต็อก')
                    ->placeholder('ทั้งหมด')
                    ->trueLabel('สินค้าหมดสต็อก (0)')
                    ->falseLabel('ยังมีสต็อกแต่ต่ำกว่าเกณฑ์')
                    ->queries(
                        true: fn (Builder $query) => $query->where('stock_quantity', '<=', 0),
                        false: fn (Builder $query) => $query->where('stock_quantity', '>', 0),
                        blank: fn (Builder $query) => $query,
                    )
                    ->native(false),
            ])
            ->striped()
            ->paginated([10, 25, 50, 100])
            ->poll('60s')
            ->emptyStateHeading('ไม่มีสินค้า Stock ต่ำ')
            ->emptyStateDescription('ยินดีด้วย! ปัจจุบันไม่มีสินค้าชิ้นไหนที่ปริมาณสต็อกต่ำกว่าเกณฑ์ครับ')
            ->emptyStateIcon(Heroicon::CheckCircle);
    }

    public function getHeading(): string
    {
        return 'รายงานสินค้า Stock ต่ำ (Reorder Report)';
    }
}
