<?php

namespace App\Filament\Widgets;

use App\Enums\OrderStatus;
use App\Models\PaymentStatus;
use App\Models\SaleOrder;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;

class OutstandingPaymentsWidget extends BaseWidget
{
    protected static ?int $sort = 9;

    protected int|string|array $columnSpan = 'full';

    public function table(Table $table): Table
    {
        $pendingStatusIds = PaymentStatus::whereIn('code', ['PENDING', 'PARTIAL'])->pluck('id');

        return $table
            ->heading('ลูกค้าค้างชำระ')
            ->query(
                SaleOrder::query()
                    ->with(['customer'])
                    ->whereIn('status', [OrderStatus::Confirmed, OrderStatus::PartiallyReceived])
                    ->whereIn('payment_status_id', $pendingStatusIds)
                    ->orderBy('created_at', 'asc')
                    ->limit(10)
            )
            ->columns([
                Tables\Columns\TextColumn::make('invoice_number')
                    ->label('เลขที่')
                    ->searchable()
                    ->icon('heroicon-o-document-text')
                    ->copyable(),
                Tables\Columns\TextColumn::make('customer.name')
                    ->label('ลูกค้า')
                    ->searchable()
                    ->icon('heroicon-o-user')
                    ->limit(30),
                Tables\Columns\TextColumn::make('total_amount')
                    ->label('ยอดค้าง')
                    ->money('THB', locale: 'th')
                    ->sortable()
                    ->alignEnd()
                    ->color('danger'),
                Tables\Columns\TextColumn::make('paymentStatus.name')
                    ->label('สถานะ')
                    ->badge(),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('ค้างมาแล้ว')
                    ->since()
                    ->sortable()
                    ->description(fn ($record) => $record->created_at->format('d M Y')),
                Tables\Columns\TextColumn::make('days_outstanding')
                    ->label('จำนวนวัน')
                    ->badge()
                    ->color(fn ($state) => match (true) {
                        $state > 30 => 'danger',
                        $state > 15 => 'warning',
                        default => 'info',
                    })
                    ->suffix(' วัน')
                    ->alignCenter()
                    ->getStateUsing(fn ($record) => now()->diffInDays($record->created_at)),
            ])
            ->paginated(false);
    }
}
