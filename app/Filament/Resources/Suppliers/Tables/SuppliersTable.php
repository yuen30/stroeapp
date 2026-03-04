<?php

namespace App\Filament\Resources\Suppliers\Tables;

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

class SuppliersTable
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
                    ->defaultImageUrl('/images/placeholder-supplier.png')
                    ->size(40),
                TextColumn::make('name')
                    ->label('ชื่อผู้จัดจำหน่าย')
                    ->searchable()
                    ->sortable()
                    ->weight('medium')
                    ->icon('heroicon-o-truck')
                    ->iconColor('primary')
                    ->description(fn($record) => "รหัส: {$record->code}"),
                TextColumn::make('code')
                    ->label('รหัสผู้จัดจำหน่าย')
                    ->searchable()
                    ->sortable()
                    ->copyable()
                    ->copyMessage('คัดลอกรหัสแล้ว')
                    ->badge()
                    ->color('info')
                    ->icon('heroicon-o-hashtag')
                    ->alignCenter(),
                TextColumn::make('contact_name')
                    ->label('ผู้ติดต่อ')
                    ->searchable()
                    ->icon('heroicon-o-user')
                    ->iconColor('gray')
                    ->placeholder('ไม่ระบุ')
                    ->toggleable(),
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
                    ->toggleable(),
                TextColumn::make('purchaseOrders_count')
                    ->label('ใบสั่งซื้อ')
                    ->counts('purchaseOrders')
                    ->badge()
                    ->color('success')
                    ->icon('heroicon-o-document-text')
                    ->alignCenter()
                    ->sortable()
                    ->tooltip('จำนวนใบสั่งซื้อ')
                    ->toggleable(),
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
                    ->options(fn() => \App\Models\Supplier::query()
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
                        ->modalHeading('ลบผู้จัดจำหน่าย')
                        ->modalDescription('คุณแน่ใจหรือไม่ว่าต้องการลบผู้จัดจำหน่ายที่เลือก? รายการจะถูกย้ายไปยังถังขยะ')
                        ->modalSubmitActionLabel('ลบ')
                        ->successNotificationTitle('ลบผู้จัดจำหน่ายสำเร็จ')
                        ->color('danger'),
                    ForceDeleteBulkAction::make()
                        ->label('ลบถาวร')
                        ->icon('heroicon-o-trash')
                        ->requiresConfirmation()
                        ->modalHeading('ลบผู้จัดจำหน่ายถาวร')
                        ->modalDescription('คุณแน่ใจหรือไม่? การลบถาวรไม่สามารถกู้คืนได้! ข้อมูลทั้งหมดจะถูกลบอย่างถาวร')
                        ->modalSubmitActionLabel('ลบถาวร')
                        ->successNotificationTitle('ลบผู้จัดจำหน่ายถาวรสำเร็จ')
                        ->color('danger'),
                    RestoreBulkAction::make()
                        ->label('กู้คืนรายการที่เลือก')
                        ->icon('heroicon-o-arrow-uturn-left')
                        ->successNotificationTitle('กู้คืนผู้จัดจำหน่ายสำเร็จ')
                        ->color('success'),
                ]),
            ])
            ->defaultSort('created_at', 'desc')
            ->striped()
            ->paginated([10, 25, 50, 100])
            ->poll('30s')
            ->emptyStateHeading('ยังไม่มีผู้จัดจำหน่ายในระบบ')
            ->emptyStateDescription('เริ่มต้นโดยการสร้างผู้จัดจำหน่ายใหม่')
            ->emptyStateIcon('heroicon-o-truck');
    }
}
