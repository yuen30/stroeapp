<?php

namespace App\Filament\Resources\GoodsReceipts\Schemas;

use App\Models\GoodsReceipt;
use App\Models\GoodsReceiptItem;
use Filament\Infolists\Components\RepeatableEntry\TableColumn;
use Filament\Infolists\Components\ImageEntry;
use Filament\Infolists\Components\RepeatableEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;

class GoodsReceiptInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('ข้อมูลทั่วไป')
                    ->icon(Heroicon::DocumentText)
                    ->schema([
                        TextEntry::make('receipt_number')
                            ->label('เลขที่ใบรับสินค้า')
                            ->badge()
                            ->color('primary')
                            ->columnSpan(1),
                        TextEntry::make('document_date')
                            ->label('วันที่รับสินค้า')
                            ->date('d/m/Y')
                            ->columnSpan(1),
                        TextEntry::make('supplier_delivery_no')
                            ->label('เลขที่ใบส่งของผู้จำหน่าย')
                            ->placeholder('-')
                            ->columnSpan(1),
                        TextEntry::make('status')
                            ->label('สถานะ')
                            ->badge()
                            ->columnSpan(1),
                        Section::make('ข้อมูลอ้างอิง')
                            ->icon(Heroicon::DocumentDuplicate)
                            ->schema([
                                TextEntry::make('purchaseOrder.order_number')
                                    ->label('ใบสั่งซื้ออ้างอิง')
                                    ->icon(Heroicon::ShoppingCart)
                                    ->color('primary')
                                    ->url(fn($record) => $record->purchase_order_id
                                        ? \App\Filament\Resources\PurchaseOrders\PurchaseOrderResource::getUrl('view', ['record' => $record->purchase_order_id])
                                        : null)
                                    ->columnSpan(2),
                                TextEntry::make('supplier.name')
                                    ->label('ผู้จำหน่าย')
                                    ->icon(Heroicon::BuildingStorefront)
                                    ->columnSpan(2),
                                TextEntry::make('company.name')
                                    ->label('บริษัท')
                                    ->icon(Heroicon::BuildingOffice2)
                                    ->columnSpan(1),
                                TextEntry::make('branch.name')
                                    ->label('สาขา')
                                    ->icon(Heroicon::MapPin)
                                    ->placeholder('-')
                                    ->columnSpan(1),
                            ])
                            ->columns(4)
                            ->columnSpanFull(),
                    ])
                    ->columns(4)
                    ->columnSpanFull(),
                Section::make('รายการสินค้า')
                    ->icon(Heroicon::ShoppingCart)
                    ->columnSpanFull()
                    ->schema([
                        RepeatableEntry::make('items')
                            ->hiddenLabel()
                            ->columnSpanFull()
                            ->visible(fn($record): bool => $record->status->value !== 'draft')
                            ->schema([
                                TextEntry::make('index')
                                    ->label('ลำดับ')
                                    ->weight('semibold'),
                                TextEntry::make('product_name')
                                    ->label('สินค้า'),
                                TextEntry::make('description')
                                    ->label('รายละเอียด')
                                    ->placeholder('-')
                                    ->limit(50),
                                TextEntry::make('quantity')
                                    ->label('จำนวนที่รับ')
                                    ->numeric()
                                    ->alignRight()
                                    ->suffix(' หน่วย'),
                            ])
                            ->state(function ($record) {
                                return $record->items->map(fn($item, $index) => [
                                    'index' => $index + 1,
                                    'item_id' => $item->id,
                                    'product_name' => $item->product->name ?? '-',
                                    'description' => $item->description,
                                    'quantity' => $item->quantity,
                                ]);
                            })
                            ->columns(4)
                            ->table([
                                TableColumn::make('ลำดับ'),
                                TableColumn::make('สินค้า'),
                                TableColumn::make('รายละเอียด'),
                                TableColumn::make('จำนวนที่รับ'),
                            ]),
                        RepeatableEntry::make('items')
                            ->hiddenLabel()
                            ->columnSpanFull()
                            ->visible(fn($record): bool => $record->status->value === 'draft')
                            ->schema([
                                TextEntry::make('index')
                                    ->label('ลำดับ')
                                    ->weight('semibold'),
                                TextEntry::make('product_name')
                                    ->label('สินค้า'),
                                TextEntry::make('description')
                                    ->label('รายละเอียด')
                                    ->placeholder('-')
                                    ->limit(50),
                                TextEntry::make('quantity')
                                    ->label('จำนวนที่รับ')
                                    ->numeric()
                                    ->alignRight()
                                    ->suffix(' หน่วย'),
                                TextEntry::make('item_id')
                                    ->label('การจัดการ')
                                    ->formatStateUsing(fn() => '')
                                    ->suffixAction(
                                        \Filament\Actions\Action::make('edit')
                                            ->icon(Heroicon::PencilSquare)
                                            ->iconButton()
                                            ->tooltip('แก้ไข')
                                            ->color('warning')
                                            ->visible(fn($record): bool => $record->status->value === 'draft')
                                            ->form([
                                                \Filament\Schemas\Components\Grid::make(4)
                                                    ->schema([
                                                        \Filament\Forms\Components\TextInput::make('product_name')
                                                            ->label('สินค้าจากใบสั่งซื้อ')
                                                            ->disabled()
                                                            ->columnSpan(2),
                                                        \Filament\Forms\Components\Textarea::make('description')
                                                            ->label('รายละเอียด')
                                                            ->rows(2)
                                                            ->columnSpan(2),
                                                        \Filament\Forms\Components\TextInput::make('quantity')
                                                            ->label('จำนวนที่รับ')
                                                            ->required()
                                                            ->numeric()
                                                            ->minValue(1)
                                                            ->suffix('หน่วย')
                                                            ->columnSpan(1),
                                                    ]),
                                            ])
                                            ->mountUsing(fn($form, $state) => $form->fill([
                                                'product_name' => GoodsReceiptItem::find($state)?->product->name ?? '-',
                                                'description' => GoodsReceiptItem::find($state)?->description,
                                                'quantity' => GoodsReceiptItem::find($state)?->quantity,
                                            ]))
                                            ->action(function (array $data, $state) {
                                                $item = GoodsReceiptItem::find($state);
                                                if ($item) {
                                                    $item->update([
                                                        'description' => $data['description'] ?? null,
                                                        'quantity' => $data['quantity'],
                                                    ]);

                                                    \Filament\Notifications\Notification::make()
                                                        ->success()
                                                        ->title('แก้ไขสินค้าสำเร็จ')
                                                        ->send();
                                                }
                                            })
                                    )
                                    ->suffixAction(
                                        \Filament\Actions\Action::make('delete')
                                            ->icon(Heroicon::Trash)
                                            ->iconButton()
                                            ->tooltip('ลบ')
                                            ->color('danger')
                                            ->visible(fn($record): bool => $record->status->value === 'draft')
                                            ->requiresConfirmation()
                                            ->modalHeading('ลบสินค้า')
                                            ->modalDescription('คุณแน่ใจหรือไม่ว่าต้องการลบสินค้านี้?')
                                            ->action(function ($state) {
                                                $item = GoodsReceiptItem::find($state);
                                                if ($item) {
                                                    $item->delete();

                                                    \Filament\Notifications\Notification::make()
                                                        ->success()
                                                        ->title('ลบสินค้าสำเร็จ')
                                                        ->send();
                                                }
                                            })
                                    ),
                            ])
                            ->state(function ($record) {
                                return $record->items->map(fn($item, $index) => [
                                    'index' => $index + 1,
                                    'item_id' => $item->id,
                                    'product_name' => $item->product->name ?? '-',
                                    'description' => $item->description,
                                    'quantity' => $item->quantity,
                                ]);
                            })
                            ->columns(5)
                            ->table([
                                TableColumn::make('ลำดับ'),
                                TableColumn::make('สินค้า'),
                                TableColumn::make('รายละเอียด'),
                                TableColumn::make('จำนวนที่รับ'),
                                TableColumn::make('การจัดการ'),
                            ]),
                    ]),
                Section::make('หมายเหตุและข้อมูลเพิ่มเติม')
                    ->icon(Heroicon::InformationCircle)
                    ->collapsible()
                    ->schema([
                        ImageEntry::make('attachments')
                            ->label('ไฟล์แนบ')
                            ->columnSpanFull()
                            ->visible(fn($record) => !empty($record->attachments)),
                        TextEntry::make('notes')
                            ->label('หมายเหตุ')
                            ->placeholder('ไม่มีหมายเหตุ')
                            ->columnSpanFull(),
                        TextEntry::make('creator.name')
                            ->label('ผู้สร้าง')
                            ->icon(Heroicon::User),
                        TextEntry::make('created_at')
                            ->label('วันที่สร้าง')
                            ->dateTime('d/m/Y H:i')
                            ->icon(Heroicon::Clock),
                        TextEntry::make('updated_at')
                            ->label('แก้ไขล่าสุด')
                            ->dateTime('d/m/Y H:i')
                            ->icon(Heroicon::Clock),
                        TextEntry::make('deleted_at')
                            ->label('วันที่ลบ')
                            ->dateTime('d/m/Y H:i')
                            ->icon(Heroicon::Trash)
                            ->color('danger')
                            ->visible(fn(GoodsReceipt $record): bool => $record->trashed()),
                    ])
                    ->columns(4)
                    ->columnSpanFull(),
                Section::make('ประวัติการทำรายการ')
                    ->icon(Heroicon::Clock)
                    ->description('บันทึกการเปลี่ยนแปลงข้อมูล')
                    ->collapsible()
                    ->schema([
                        RepeatableEntry::make('activities')
                            ->label('')
                            ->hiddenLabel()
                            ->columnSpanFull()
                            ->schema([
                                TextEntry::make('created_at')
                                    ->label('เวลา')
                                    ->dateTime('d/m/Y H:i'),
                                TextEntry::make('causer.name')
                                    ->label('ผู้ทำรายการ')
                                    ->default('System'),
                                TextEntry::make('description')
                                    ->label('การกระทำ')
                                    ->badge()
                                    ->color(fn($state) => match ($state) {
                                        'สร้างใบรับสินค้า' => 'success',
                                        'แก้ไขใบรับสินค้า' => 'warning',
                                        'ลบใบรับสินค้า' => 'danger',
                                        default => 'gray',
                                    }),
                                TextEntry::make('properties')
                                    ->label('รายละเอียด')
                                    ->state(function ($record) {
                                        if ($record->description === 'สร้างใบรับสินค้า') {
                                            return 'สร้างรายการใหม่';
                                        }
                                        if ($record->description === 'ลบใบรับสินค้า') {
                                            return 'ลบรายการ';
                                        }

                                        $attributes = $record->properties['attributes'] ?? [];
                                        if (empty($attributes)) {
                                            return '-';
                                        }

                                        return collect($attributes)
                                            ->keys()
                                            ->reject(fn($key) => in_array($key, ['id', 'created_at', 'updated_at', 'deleted_at']))
                                            ->map(fn($key) => str($key)->headline())
                                            ->filter()
                                            ->implode(', ');
                                    }),
                            ])
                            ->columns(4)
                            ->table([
                                TableColumn::make('เวลา'),
                                TableColumn::make('ผู้ทำรายการ'),
                                TableColumn::make('การกระทำ'),
                                TableColumn::make('รายละเอียด'),
                            ]),
                    ])
                    ->columnSpanFull(),
            ]);
    }
}
