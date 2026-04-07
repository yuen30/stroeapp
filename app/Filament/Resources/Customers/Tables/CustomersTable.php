<?php

namespace App\Filament\Resources\Customers\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ForceDeleteBulkAction;
use Filament\Actions\RestoreBulkAction;
use Filament\Actions\ViewAction;
use Filament\Support\Enums\IconSize;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;

class CustomersTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('row_id')
                    ->label('#')
                    ->rowIndex()
                    ->alignCenter(),
                ImageColumn::make('photo_path')
                    ->label('รูปภาพ')
                    ->circular()
                    ->defaultImageUrl('/images/placeholder-customer.png')
                    ->size(40),
                TextColumn::make('name')
                    ->label('ชื่อลูกค้า')
                    ->searchable()
                    ->sortable()
                    ->weight('medium')
                    ->icon('heroicon-o-user-group')
                    ->iconColor('primary')
                    ->description(fn($record) => "รหัส: {$record->code}"),
                TextColumn::make('code')
                    ->label('รหัสลูกค้า')
                    ->searchable()
                    ->sortable()
                    ->copyable()
                    ->copyMessage('คัดลอกรหัสแล้ว')
                    ->badge()
                    ->color('info')
                    ->icon('heroicon-o-hashtag')
                    ->alignCenter(),
                TextColumn::make('company.name')
                    ->label('บริษัท')
                    ->searchable()
                    ->sortable()
                    ->icon('heroicon-o-building-office-2')
                    ->iconColor('primary')
                    ->placeholder('ไม่ระบุ')
                    ->toggleable(),
                TextColumn::make('tel')
                    ->label('เบอร์โทรศัพท์')
                    ->searchable()
                    ->icon('heroicon-o-phone')
                    ->iconColor('success')
                    ->placeholder('ไม่ระบุ')
                    ->toggleable(),
                TextColumn::make('province')
                    ->label('จังหวัด')
                    ->searchable()
                    ->icon('heroicon-o-map-pin')
                    ->iconColor('gray')
                    ->placeholder('ไม่ระบุ')
                    ->toggleable(true),
                TextColumn::make('credit_limit')
                    ->label('วงเงินเครดิต')
                    ->numeric()
                    ->money('THB')
                    ->sortable()
                    ->icon('heroicon-o-credit-card')
                    ->iconColor('warning')
                    ->alignEnd()
                    ->toggleable(),
                TextColumn::make('credit_days')
                    ->label('เครดิต (วัน)')
                    ->numeric()
                    ->sortable()
                    ->suffix(' วัน')
                    ->badge()
                    ->color('info')
                    ->alignCenter()
                    ->toggleable(),
                TextColumn::make('saleOrders_count')
                    ->label('ใบส่งสินค้า')
                    ->counts('saleOrders')
                    ->badge()
                    ->color('success')
                    ->icon('heroicon-o-document-text')
                    ->alignCenter()
                    ->sortable()
                    ->tooltip('จำนวนใบส่งสินค้า')
                    ->toggleable(isToggledHiddenByDefault: false),
                IconColumn::make('is_active')
                    ->label('สถานะ')
                    ->boolean()
                    ->trueIcon('heroicon-o-check-circle')
                    ->falseIcon('heroicon-o-x-circle')
                    ->trueColor('success')
                    ->falseColor('danger')
                    ->size(IconSize::Large)
                    ->alignCenter()
                    ->sortable()
                    ->tooltip(fn($state) => $state ? 'ใช้งาน' : 'ไม่ใช้งาน'),
                TextColumn::make('created_at')
                    ->label('วันที่สร้าง')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->icon('heroicon-o-calendar')
                    ->iconColor('gray')
                    ->toggleable()
                    ->toggledHiddenByDefault(),
                TextColumn::make('updated_at')
                    ->label('แก้ไขล่าสุด')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->since()
                    ->icon('heroicon-o-clock')
                    ->iconColor('gray')
                    ->toggleable()
                    ->tooltip(fn($record) => $record->updated_at?->format('d/m/Y H:i:s')),
                TextColumn::make('deleted_at')
                    ->label('วันที่ลบ')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->icon('heroicon-o-trash')
                    ->iconColor('danger')
                    ->toggleable()
                    ->toggledHiddenByDefault(),
            ])
            ->filters([
                SelectFilter::make('company_id')
                    ->label('บริษัท')
                    ->relationship('company', 'name')
                    ->searchable()
                    ->preload()
                    ->placeholder('ทุกบริษัท')
                    ->native(false),
                SelectFilter::make('province')
                    ->label('จังหวัด')
                    ->options(fn() => \App\Models\Customer::query()
                        ->whereNotNull('province')
                        ->distinct()
                        ->pluck('province', 'province')
                        ->toArray())
                    ->searchable()
                    ->placeholder('ทุกจังหวัด')
                    ->native(false),
                SelectFilter::make('is_active')
                    ->label('สถานะ')
                    ->options([
                        true => 'ใช้งาน',
                        false => 'ไม่ใช้งาน',
                    ])
                    ->placeholder('ทั้งหมด')
                    ->native(false),
                TrashedFilter::make()
                    ->label('รายการที่ถูกลบ')
                    ->placeholder('ไม่รวมรายการที่ลบ')
                    ->trueLabel('เฉพาะรายการที่ลบ')
                    ->falseLabel('ไม่รวมรายการที่ลบ')
                    ->native(false),
            ])
            ->recordActions([
                ViewAction::make()
                    ->label('ดู')
                    ->icon('heroicon-o-eye')
                    ->color('info')
                    ->tooltip('ดูรายละเอียด'),
                EditAction::make()
                    ->label('แก้ไข')
                    ->icon('heroicon-o-pencil-square')
                    ->color('warning')
                    ->tooltip('แก้ไขข้อมูล'),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make()
                        ->label('ลบรายการที่เลือก')
                        ->icon('heroicon-o-trash')
                        ->requiresConfirmation()
                        ->modalHeading('ลบลูกค้า')
                        ->modalDescription('คุณแน่ใจหรือไม่ว่าต้องการลบลูกค้าที่เลือก? รายการจะถูกย้ายไปยังถังขยะ')
                        ->modalSubmitActionLabel('ลบ')
                        ->successNotificationTitle('ลบลูกค้าสำเร็จ')
                        ->color('danger'),
                    ForceDeleteBulkAction::make()
                        ->label('ลบถาวร')
                        ->icon('heroicon-o-trash')
                        ->requiresConfirmation()
                        ->modalHeading('ลบลูกค้าถาวร')
                        ->modalDescription('คุณแน่ใจหรือไม่? การลบถาวรไม่สามารถกู้คืนได้! ข้อมูลทั้งหมดจะถูกลบอย่างถาวร')
                        ->modalSubmitActionLabel('ลบถาวร')
                        ->successNotificationTitle('ลบลูกค้าถาวรสำเร็จ')
                        ->color('danger'),
                    RestoreBulkAction::make()
                        ->label('กู้คืนรายการที่เลือก')
                        ->icon('heroicon-o-arrow-uturn-left')
                        ->successNotificationTitle('กู้คืนลูกค้าสำเร็จ')
                        ->color('success'),
                ]),
            ])
            ->defaultSort('created_at', 'desc')
            ->striped()
            ->paginated([10, 25, 50, 100])
            ->poll('30s')
            ->emptyStateHeading('ยังไม่มีลูกค้าในระบบ')
            ->emptyStateDescription('เริ่มต้นโดยการสร้างลูกค้าใหม่')
            ->emptyStateIcon('heroicon-o-user-group');
    }
}
