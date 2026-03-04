<?php

namespace App\Filament\Resources\Users\Tables;

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

class UsersTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('row_id')
                    ->label('#')
                    ->rowIndex()
                    ->alignCenter(),
                ImageColumn::make('profile_photo_path')
                    ->label('รูปโปรไฟล์')
                    ->circular()
                    ->defaultImageUrl(fn($record) => $record->getFilamentAvatarUrl())
                    ->size(40),
                TextColumn::make('name')
                    ->label('ชื่อ-นามสกุล')
                    ->searchable()
                    ->sortable()
                    ->weight('medium')
                    ->description(fn($record) => $record->username ? "@{$record->username}" : null)
                    ->icon('heroicon-o-user')
                    ->iconColor('primary'),
                TextColumn::make('email')
                    ->label('อีเมล')
                    ->searchable()
                    ->sortable()
                    ->copyable()
                    ->copyMessage('คัดลอกอีเมลแล้ว')
                    ->icon('heroicon-o-envelope')
                    ->iconColor('gray')
                    ->description(fn($record) => $record->email_verified_at
                        ? '✓ ยืนยันแล้ว'
                        : '⚠ ยังไม่ยืนยัน'),
                TextColumn::make('company.name')
                    ->label('บริษัท')
                    ->searchable()
                    ->sortable()
                    ->icon('heroicon-o-building-office-2')
                    ->iconColor('primary')
                    ->placeholder('ไม่ระบุ')
                    ->toggleable(),
                TextColumn::make('branch.name')
                    ->label('สาขา')
                    ->searchable()
                    ->sortable()
                    ->icon('heroicon-o-building-storefront')
                    ->iconColor('info')
                    ->placeholder('ไม่ระบุ')
                    ->toggleable(),
                TextColumn::make('role')
                    ->label('สิทธิ์การใช้งาน')
                    ->badge()
                    ->searchable()
                    ->sortable()
                    ->alignCenter()
                    ->tooltip('สิทธิ์การเข้าถึงระบบ'),
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
                TextColumn::make('email_verified_at')
                    ->label('ยืนยันอีเมลเมื่อ')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->icon('heroicon-o-shield-check')
                    ->iconColor('success')
                    ->placeholder('ยังไม่ยืนยัน')
                    ->toggleable()
                    ->toggledHiddenByDefault(),
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
                SelectFilter::make('role')
                    ->label('สิทธิ์การใช้งาน')
                    ->options(\App\Enums\Roles::class)
                    ->placeholder('ทุกสิทธิ์')
                    ->native(false),
                SelectFilter::make('is_active')
                    ->label('สถานะ')
                    ->options([
                        true => 'ใช้งาน',
                        false => 'ไม่ใช้งาน',
                    ])
                    ->placeholder('ทั้งหมด')
                    ->native(false),
                SelectFilter::make('email_verified')
                    ->label('การยืนยันอีเมล')
                    ->options([
                        'verified' => 'ยืนยันแล้ว',
                        'unverified' => 'ยังไม่ยืนยัน',
                    ])
                    ->query(function ($query, $state) {
                        if ($state['value'] === 'verified') {
                            return $query->whereNotNull('email_verified_at');
                        }
                        if ($state['value'] === 'unverified') {
                            return $query->whereNull('email_verified_at');
                        }
                    })
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
                        ->modalHeading('ลบผู้ใช้')
                        ->modalDescription('คุณแน่ใจหรือไม่ว่าต้องการลบผู้ใช้ที่เลือก? รายการจะถูกย้ายไปยังถังขยะ')
                        ->modalSubmitActionLabel('ลบ')
                        ->successNotificationTitle('ลบผู้ใช้สำเร็จ')
                        ->color('danger'),
                    ForceDeleteBulkAction::make()
                        ->label('ลบถาวร')
                        ->icon('heroicon-o-trash')
                        ->requiresConfirmation()
                        ->modalHeading('ลบผู้ใช้ถาวร')
                        ->modalDescription('คุณแน่ใจหรือไม่? การลบถาวรไม่สามารถกู้คืนได้! ข้อมูลทั้งหมดจะถูกลบอย่างถาวร')
                        ->modalSubmitActionLabel('ลบถาวร')
                        ->successNotificationTitle('ลบผู้ใช้ถาวรสำเร็จ')
                        ->color('danger'),
                    RestoreBulkAction::make()
                        ->label('กู้คืนรายการที่เลือก')
                        ->icon('heroicon-o-arrow-uturn-left')
                        ->successNotificationTitle('กู้คืนผู้ใช้สำเร็จ')
                        ->color('success'),
                ]),
            ])
            ->defaultSort('created_at', 'desc')
            ->striped()
            ->paginated([10, 25, 50, 100])
            ->poll('30s')
            ->emptyStateHeading('ยังไม่มีผู้ใช้ในระบบ')
            ->emptyStateDescription('เริ่มต้นโดยการสร้างผู้ใช้ใหม่')
            ->emptyStateIcon('heroicon-o-users');
    }
}
