<?php

namespace App\Filament\Resources\Categories\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ForceDeleteBulkAction;
use Filament\Actions\RestoreBulkAction;
use Filament\Actions\ViewAction;
use Filament\Support\Enums\IconSize;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;

class CategoriesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('row_id')
                    ->label('#')
                    ->rowIndex()
                    ->alignCenter(),
                TextColumn::make('name')
                    ->label('ชื่อหมวดหมู่')
                    ->searchable()
                    ->sortable()
                    ->weight('medium')
                    ->icon('heroicon-o-squares-2x2')
                    ->iconColor('primary')
                    ->description(fn($record) => "รหัส: {$record->code}"),
                TextColumn::make('parent.name')
                    ->label('หมวดหมู่หลัก')
                    ->searchable()
                    ->sortable()
                    ->icon('heroicon-o-folder')
                    ->iconColor('info')
                    ->placeholder('หมวดหมู่หลัก')
                    ->badge()
                    ->color('gray'),
                TextColumn::make('code')
                    ->label('รหัสหมวดหมู่')
                    ->searchable()
                    ->sortable()
                    ->copyable()
                    ->copyMessage('คัดลอกรหัสแล้ว')
                    ->badge()
                    ->color('info')
                    ->icon('heroicon-o-hashtag')
                    ->alignCenter(),
                TextColumn::make('children_count')
                    ->label('หมวดหมู่ย่อย')
                    ->counts('children')
                    ->badge()
                    ->color('warning')
                    ->icon('heroicon-o-folder-open')
                    ->alignCenter()
                    ->sortable()
                    ->tooltip('จำนวนหมวดหมู่ย่อย'),
                TextColumn::make('products_count')
                    ->label('จำนวนสินค้า')
                    ->counts('products')
                    ->badge()
                    ->color('success')
                    ->icon('heroicon-o-cube')
                    ->alignCenter()
                    ->sortable()
                    ->tooltip('จำนวนสินค้าในหมวดหมู่นี้'),
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
                SelectFilter::make('parent_id')
                    ->label('หมวดหมู่หลัก')
                    ->relationship('parent', 'name')
                    ->searchable()
                    ->preload()
                    ->placeholder('ทุกหมวดหมู่')
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
                        ->modalHeading('ลบหมวดหมู่')
                        ->modalDescription('คุณแน่ใจหรือไม่ว่าต้องการลบหมวดหมู่ที่เลือก? รายการจะถูกย้ายไปยังถังขยะ')
                        ->modalSubmitActionLabel('ลบ')
                        ->successNotificationTitle('ลบหมวดหมู่สำเร็จ')
                        ->color('danger'),
                    ForceDeleteBulkAction::make()
                        ->label('ลบถาวร')
                        ->icon('heroicon-o-trash')
                        ->requiresConfirmation()
                        ->modalHeading('ลบหมวดหมู่ถาวร')
                        ->modalDescription('คุณแน่ใจหรือไม่? การลบถาวรไม่สามารถกู้คืนได้! ข้อมูลทั้งหมดจะถูกลบอย่างถาวร')
                        ->modalSubmitActionLabel('ลบถาวร')
                        ->successNotificationTitle('ลบหมวดหมู่ถาวรสำเร็จ')
                        ->color('danger'),
                    RestoreBulkAction::make()
                        ->label('กู้คืนรายการที่เลือก')
                        ->icon('heroicon-o-arrow-uturn-left')
                        ->successNotificationTitle('กู้คืนหมวดหมู่สำเร็จ')
                        ->color('success'),
                ]),
            ])
            ->defaultSort('created_at', 'desc')
            ->striped()
            ->paginated([10, 25, 50, 100])
            ->poll('30s')
            ->emptyStateHeading('ยังไม่มีหมวดหมู่ในระบบ')
            ->emptyStateDescription('เริ่มต้นโดยการสร้างหมวดหมู่ใหม่')
            ->emptyStateIcon('heroicon-o-squares-2x2');
    }
}
