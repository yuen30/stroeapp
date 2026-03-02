<?php

namespace App\Filament\Resources\Suppliers\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ForceDeleteBulkAction;
use Filament\Actions\RestoreBulkAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;

class SuppliersTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('row_id')->label('รหัสอ้างอิง')
                    ->label('ID')
                    ->rowIndex(),
                TextColumn::make('company.name')->label('บริษัท')
                    ->searchable(),
                TextColumn::make('name')->label('ชื่อ')
                    ->searchable(),
                TextColumn::make('code')->label('รหัส')
                    ->searchable(),
                TextColumn::make('contact_name')->label('ชื่อผู้ติดต่อ')
                    ->searchable(),
                TextColumn::make('address_0')->label('ที่อยู่ 1')
                    ->searchable(),
                TextColumn::make('address_1')->label('ที่อยู่ 2')
                    ->searchable(),
                TextColumn::make('amphoe')->label('เขต/อำเภอ')
                    ->searchable(),
                TextColumn::make('province')->label('จังหวัด')
                    ->searchable(),
                TextColumn::make('postal_code')->label('รหัสไปรษณีย์')
                    ->searchable(),
                TextColumn::make('tel')->label('เบอร์โทรศัพท์')
                    ->searchable(),
                TextColumn::make('fax')->label('แฟกซ์')
                    ->searchable(),
                TextColumn::make('tax_id')->label('เลขประจำตัวผู้เสียภาษี')
                    ->searchable(),
                TextColumn::make('photo_path')->label('รูปภาพ')
                    ->searchable(),
                IconColumn::make('is_active')->label('สถานะใช้งาน')
                    ->boolean(),
                TextColumn::make('deleted_at')->label('วันที่ลบ')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('created_at')->label('วันที่สร้าง')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('updated_at')->label('วันที่แก้ไข')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                TrashedFilter::make(),
            ])
            ->recordActions([
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                    ForceDeleteBulkAction::make(),
                    RestoreBulkAction::make(),
                ]),
            ]);
    }
}
