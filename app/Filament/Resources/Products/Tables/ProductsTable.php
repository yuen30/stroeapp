<?php

namespace App\Filament\Resources\Products\Tables;

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

class ProductsTable
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
                    ->defaultImageUrl('/images/placeholder-product.png')
                    ->size(40),
                TextColumn::make('name')
                    ->label('ชื่อสินค้า')
                    ->searchable()
                    ->sortable()
                    ->weight('medium')
                    ->icon('heroicon-o-cube')
                    ->iconColor('primary')
                    ->description(fn($record) => "รหัส: {$record->code}"),
                TextColumn::make('code')
                    ->label('รหัสสินค้า')
                    ->searchable()
                    ->sortable()
                    ->copyable()
                    ->copyMessage('คัดลอกรหัสแล้ว')
                    ->badge()
                    ->color('info')
                    ->icon('heroicon-o-hashtag')
                    ->alignCenter()
                    ->toggleable(),
                TextColumn::make('category.name')
                    ->label('หมวดหมู่')
                    ->searchable()
                    ->sortable()
                    ->icon('heroicon-o-squares-2x2')
                    ->iconColor('warning')
                    ->placeholder('ไม่ระบุ')
                    ->toggleable(),
                TextColumn::make('brand.name')
                    ->label('ยี่ห้อ')
                    ->searchable()
                    ->sortable()
                    ->icon('heroicon-o-tag')
                    ->iconColor('info')
                    ->placeholder('ไม่ระบุ')
                    ->toggleable(),
                TextColumn::make('unit.name')
                    ->label('หน่วยนับ')
                    ->searchable()
                    ->badge()
                    ->color('gray')
                    ->alignCenter()
                    ->toggleable(),
                TextColumn::make('cost_price')
                    ->label('ราคาต้นทุน')
                    ->money('THB')
                    ->sortable()
                    ->icon('heroicon-o-currency-dollar')
                    ->iconColor('gray')
                    ->alignEnd()
                    ->toggleable(),
                TextColumn::make('selling_price')
                    ->label('ราคาขาย')
                    ->money('THB')
                    ->sortable()
                    ->icon('heroicon-o-currency-dollar')
                    ->iconColor('success')
                    ->alignEnd(),
                TextColumn::make('stock_quantity')
                    ->label('สต๊อก')
                    ->numeric()
                    ->sortable()
                    ->badge()
                    ->color(fn($state) => match (true) {
                        $state <= 0 => 'danger',
                        $state <= 10 => 'warning',
                        default => 'success',
                    })
                    ->icon('heroicon-o-cube')
                    ->alignCenter()
                    ->tooltip('จำนวนสินค้าคงเหลือ'),
                TextColumn::make('company.name')
                    ->label('บริษัท')
                    ->searchable()
                    ->icon('heroicon-o-building-office-2')
                    ->iconColor('primary')
                    ->toggleable()
                    ->toggledHiddenByDefault(),
                TextColumn::make('branch.name')
                    ->label('สาขา')
                    ->searchable()
                    ->icon('heroicon-o-building-storefront')
                    ->iconColor('info')
                    ->toggleable()
                    ->toggledHiddenByDefault(),
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
                SelectFilter::make('branch_id')
                    ->label('สาขา')
                    ->relationship('branch', 'name')
                    ->searchable()
                    ->preload()
                    ->placeholder('ทุกสาขา')
                    ->native(false),
                SelectFilter::make('category_id')
                    ->label('หมวดหมู่')
                    ->relationship('category', 'name')
                    ->searchable()
                    ->preload()
                    ->placeholder('ทุกหมวดหมู่')
                    ->native(false),
                SelectFilter::make('brand_id')
                    ->label('ยี่ห้อ')
                    ->relationship('brand', 'name')
                    ->searchable()
                    ->preload()
                    ->placeholder('ทุกยี่ห้อ')
                    ->native(false),
                SelectFilter::make('stock_status')
                    ->label('สถานะสต๊อก')
                    ->options([
                        'out' => 'สินค้าหมด (0)',
                        'low' => 'สต๊อกต่ำ (≤10)',
                        'normal' => 'สต๊อกปกติ (>10)',
                    ])
                    ->query(function ($query, $state) {
                        if ($state['value'] === 'out') {
                            return $query->where('stock_quantity', '<=', 0);
                        }
                        if ($state['value'] === 'low') {
                            return $query->where('stock_quantity', '>', 0)->where('stock_quantity', '<=', 10);
                        }
                        if ($state['value'] === 'normal') {
                            return $query->where('stock_quantity', '>', 10);
                        }
                    })
                    ->placeholder('ทุกสถานะ')
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
                        ->modalHeading('ลบสินค้า')
                        ->modalDescription('คุณแน่ใจหรือไม่ว่าต้องการลบสินค้าที่เลือก? รายการจะถูกย้ายไปยังถังขยะ')
                        ->modalSubmitActionLabel('ลบ')
                        ->successNotificationTitle('ลบสินค้าสำเร็จ')
                        ->color('danger'),
                    ForceDeleteBulkAction::make()
                        ->label('ลบถาวร')
                        ->icon('heroicon-o-trash')
                        ->requiresConfirmation()
                        ->modalHeading('ลบสินค้าถาวร')
                        ->modalDescription('คุณแน่ใจหรือไม่? การลบถาวรไม่สามารถกู้คืนได้! ข้อมูลทั้งหมดจะถูกลบอย่างถาวร')
                        ->modalSubmitActionLabel('ลบถาวร')
                        ->successNotificationTitle('ลบสินค้าถาวรสำเร็จ')
                        ->color('danger'),
                    RestoreBulkAction::make()
                        ->label('กู้คืนรายการที่เลือก')
                        ->icon('heroicon-o-arrow-uturn-left')
                        ->successNotificationTitle('กู้คืนสินค้าสำเร็จ')
                        ->color('success'),
                ]),
            ])
            ->defaultSort('created_at', 'desc')
            ->striped()
            ->paginated([10, 25, 50, 100])
            ->poll('30s')
            ->emptyStateHeading('ยังไม่มีสินค้าในระบบ')
            ->emptyStateDescription('เริ่มต้นโดยการสร้างสินค้าใหม่')
            ->emptyStateIcon('heroicon-o-cube');
    }
}
