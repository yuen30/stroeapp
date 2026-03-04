<?php

namespace App\Filament\Resources\Companies\Tables;

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

class CompaniesTable
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
                    ->label('โลโก้')
                    ->circular()
                    ->defaultImageUrl(url('/images/default-company.png'))
                    ->size(40),
                TextColumn::make('code')
                    ->label('รหัสบริษัท')
                    ->searchable()
                    ->sortable()
                    ->copyable()
                    ->copyMessage('คัดลอกรหัสบริษัทแล้ว')
                    ->weight('bold')
                    ->color('primary')
                    ->icon('heroicon-o-hashtag')
                    ->iconColor('primary'),
                TextColumn::make('name')
                    ->label('ชื่อบริษัท')
                    ->searchable()
                    ->sortable()
                    ->weight('medium')
                    ->wrap()
                    ->description(fn($record) => $record->tax_id ? "เลขประจำตัวผู้เสียภาษี: {$record->tax_id}" : null),
                TextColumn::make('address')
                    ->label('ที่อยู่')
                    ->getStateUsing(function ($record) {
                        $parts = array_filter([
                            $record->address_0,
                            $record->address_1,
                            $record->amphoe,
                            $record->province,
                            $record->postal_code,
                        ]);
                        return implode(' ', $parts) ?: 'ไม่ระบุ';
                    })
                    ->wrap()
                    ->limit(50)
                    ->tooltip(function ($record) {
                        $parts = array_filter([
                            $record->address_0,
                            $record->address_1,
                            $record->amphoe,
                            $record->province,
                            $record->postal_code,
                        ]);
                        return implode(' ', $parts);
                    })
                    ->icon('heroicon-o-map-pin')
                    ->iconColor('gray')
                    ->toggleable(),
                TextColumn::make('tel')
                    ->label('เบอร์โทรศัพท์')
                    ->searchable()
                    ->icon('heroicon-o-phone')
                    ->iconColor('success')
                    ->copyable()
                    ->copyMessage('คัดลอกเบอร์โทรศัพท์แล้ว')
                    ->placeholder('ไม่ระบุ')
                    ->toggleable(),
                TextColumn::make('fax')
                    ->label('แฟกซ์')
                    ->searchable()
                    ->icon('heroicon-o-printer')
                    ->iconColor('gray')
                    ->placeholder('ไม่ระบุ')
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
                TextColumn::make('branches_count')
                    ->label('สาขา')
                    ->counts('branches')
                    ->badge()
                    ->color('info')
                    ->icon('heroicon-o-building-storefront')
                    ->alignCenter()
                    ->sortable()
                    ->toggleable()
                    ->tooltip('จำนวนสาขาทั้งหมด'),
                TextColumn::make('users_count')
                    ->label('ผู้ใช้')
                    ->counts('users')
                    ->badge()
                    ->color('warning')
                    ->icon('heroicon-o-users')
                    ->alignCenter()
                    ->sortable()
                    ->toggleable()
                    ->tooltip('จำนวนผู้ใช้ทั้งหมด'),
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
                SelectFilter::make('is_active')
                    ->label('สถานะ')
                    ->options([
                        true => 'ใช้งาน',
                        false => 'ไม่ใช้งาน',
                    ])
                    ->placeholder('ทั้งหมด')
                    ->native(false),
                SelectFilter::make('province')
                    ->label('จังหวัด')
                    ->options(function () {
                        return \App\Models\Company::query()
                            ->whereNotNull('province')
                            ->distinct()
                            ->orderBy('province')
                            ->pluck('province', 'province')
                            ->toArray();
                    })
                    ->searchable()
                    ->placeholder('ทุกจังหวัด')
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
                        ->modalHeading('ลบบริษัท')
                        ->modalDescription('คุณแน่ใจหรือไม่ว่าต้องการลบบริษัทที่เลือก? รายการจะถูกย้ายไปยังถังขยะ')
                        ->modalSubmitActionLabel('ลบ')
                        ->successNotificationTitle('ลบบริษัทสำเร็จ')
                        ->color('danger'),
                    ForceDeleteBulkAction::make()
                        ->label('ลบถาวร')
                        ->icon('heroicon-o-trash')
                        ->requiresConfirmation()
                        ->modalHeading('ลบบริษัทถาวร')
                        ->modalDescription('คุณแน่ใจหรือไม่? การลบถาวรไม่สามารถกู้คืนได้! ข้อมูลทั้งหมดจะถูกลบอย่างถาวร')
                        ->modalSubmitActionLabel('ลบถาวร')
                        ->successNotificationTitle('ลบบริษัทถาวรสำเร็จ')
                        ->color('danger'),
                    RestoreBulkAction::make()
                        ->label('กู้คืนรายการที่เลือก')
                        ->icon('heroicon-o-arrow-uturn-left')
                        ->successNotificationTitle('กู้คืนบริษัทสำเร็จ')
                        ->color('success'),
                ]),
            ])
            ->defaultSort('created_at', 'desc')
            ->striped()
            ->paginated([10, 25, 50, 100])
            ->poll('30s')
            ->emptyStateHeading('ยังไม่มีบริษัทในระบบ')
            ->emptyStateDescription('เริ่มต้นโดยการสร้างบริษัทใหม่')
            ->emptyStateIcon('heroicon-o-building-office-2');
    }
}
