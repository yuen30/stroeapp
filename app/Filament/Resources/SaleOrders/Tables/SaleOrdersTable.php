<?php

namespace App\Filament\Resources\SaleOrders\Tables;

use App\Enums\OrderStatus;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\RestoreBulkAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;

class SaleOrdersTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('row_id')->label('#')->rowIndex()->alignCenter(),
                TextColumn::make('invoice_number')->label('เลขที่ใบกำกับภาษี')->searchable()->sortable()->weight('medium'),
                TextColumn::make('customer.name')->label('ลูกค้า')->searchable()->sortable()->icon('heroicon-o-user-group')->iconColor('info'),
                TextColumn::make('order_date')->label('วันที่ขาย')->date('d/m/Y')->sortable()->icon('heroicon-o-calendar'),
                TextColumn::make('status')->label('สถานะ')->badge()->sortable()->alignCenter(),
                TextColumn::make('paymentMethod.name')->label('ช่องทางชำระเงิน')->searchable()->sortable()->icon('heroicon-o-credit-card')->iconColor('primary'),
                TextColumn::make('paymentStatus.name')->label('สถานะชำระเงิน')->searchable()->sortable()->icon('heroicon-o-banknotes')->iconColor('warning'),
                TextColumn::make('total_amount')->label('ยอดรวมทั้งสิ้น')->money('THB')->sortable()->alignEnd()->weight('bold'),
                TextColumn::make('creator.name')->label('ผู้สร้าง')->searchable()->toggleable(),
                TextColumn::make('created_at')->label('วันที่สร้าง')->dateTime('d/m/Y H:i')->sortable()->toggleable()->toggledHiddenByDefault(),
            ])
            ->filters([
                SelectFilter::make('customer_id')->label('ลูกค้า')->relationship('customer', 'name')->searchable()->preload()->native(false),
                SelectFilter::make('status')->label('สถานะ')->options(OrderStatus::class)->native(false),
                SelectFilter::make('payment_status_id')->label('สถานะชำระเงิน')->relationship('paymentStatus', 'name')->searchable()->preload()->native(false),
                TrashedFilter::make()->label('รายการที่ถูกลบ')->native(false),
            ])
            ->recordActions([
                ViewAction::make()->label('ดู')->icon('heroicon-o-eye')->color('info'),
                EditAction::make()->label('แก้ไข')->icon('heroicon-o-pencil-square')->color('warning'),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make()->label('ลบรายการที่เลือก')->icon('heroicon-o-trash')->color('danger'),
                    RestoreBulkAction::make()->label('กู้คืนรายการที่เลือก')->icon('heroicon-o-arrow-uturn-left')->color('success'),
                ]),
            ])
            ->defaultSort('created_at', 'desc')
            ->striped()
            ->paginated([10, 25, 50, 100])
            ->emptyStateHeading('ยังไม่มีใบสั่งขายในระบบ')
            ->emptyStateIcon('heroicon-o-document-text');
    }
}
