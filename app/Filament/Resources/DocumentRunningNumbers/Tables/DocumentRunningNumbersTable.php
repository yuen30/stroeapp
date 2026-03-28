<?php

namespace App\Filament\Resources\DocumentRunningNumbers\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Support\Enums\IconSize;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class DocumentRunningNumbersTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('row_id')
                    ->label('#')
                    ->rowIndex()
                    ->alignCenter(),
                TextColumn::make('document_type')
                    ->label('ประเภทเอกสาร')
                    ->searchable()
                    ->sortable()
                    ->weight('medium')
                    ->icon('heroicon-o-document-text')
                    ->iconColor('primary'),
                TextColumn::make('prefix')
                    ->label('คำนำหน้า')
                    ->searchable()
                    ->icon('heroicon-o-hashtag')
                    ->badge()
                    ->color('info'),
                TextColumn::make('date_format')
                    ->label('รูปแบบวันที่')
                    ->searchable()
                    ->placeholder('-')
                    ->icon('heroicon-o-calendar')
                    ->iconColor('gray'),
                TextColumn::make('running_length')
                    ->label('จำนวนหลัก')
                    ->numeric()
                    ->sortable()
                    ->alignCenter()
                    ->badge()
                    ->color('gray'),
                TextColumn::make('current_number')
                    ->label('รอบปัจจุบัน')
                    ->numeric()
                    ->sortable()
                    ->alignCenter()
                    ->badge()
                    ->color('warning'),
                TextColumn::make('company.name')
                    ->label('บริษัท')
                    ->searchable()
                    ->icon('heroicon-o-building-office-2')
                    ->iconColor('primary')
                    ->placeholder('-')
                    ->toggleable()
                    ->toggledHiddenByDefault(),
                TextColumn::make('branch.name')
                    ->label('สาขา')
                    ->searchable()
                    ->icon('heroicon-o-building-storefront')
                    ->iconColor('info')
                    ->placeholder('-')
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
                    ->tooltip(fn ($state) => $state ? 'ใช้งาน' : 'ไม่ใช้งาน'),
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
                    ->tooltip(fn ($record) => $record->updated_at?->format('d/m/Y H:i:s')),
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
                SelectFilter::make('is_active')
                    ->label('สถานะ')
                    ->options([
                        true => 'ใช้งาน',
                        false => 'ไม่ใช้งาน',
                    ])
                    ->placeholder('ทั้งหมด')
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
                        ->modalHeading('ลบรูปแบบเอกสาร')
                        ->modalDescription('คุณแน่ใจหรือไม่ว่าต้องการลบรูปแบบที่เลือก?')
                        ->modalSubmitActionLabel('ลบ')
                        ->successNotificationTitle('ลบรูปแบบสำเร็จ')
                        ->color('danger'),
                ]),
            ])
            ->defaultSort('created_at', 'desc')
            ->striped()
            ->paginated([10, 25, 50, 100])
            ->poll('30s')
            ->emptyStateHeading('ยังไม่มีรูปแบบเอกสาร')
            ->emptyStateDescription('เริ่มต้นโดยการสร้างรูปแบบใหม่')
            ->emptyStateIcon('heroicon-o-document-text');
    }
}
