<?php

namespace App\Filament\Resources\PurchaseOrders\Schemas;

use App\Models\PurchaseOrder;
use App\Models\PurchaseOrderItem;
use Filament\Infolists\Components\RepeatableEntry\TableColumn;
use Filament\Infolists\Components\ImageEntry;
use Filament\Infolists\Components\RepeatableEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;

class PurchaseOrderInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('ข้อมูลทั่วไป')
                    ->icon(Heroicon::DocumentText)
                    ->schema([
                        TextEntry::make('order_number')
                            ->label('เลขที่ใบสั่งซื้อ')
                            ->badge()
                            ->color('primary')
                            ->columnSpan(1),
                        TextEntry::make('order_date')
                            ->label('วันที่สั่งซื้อ')
                            ->date('d/m/Y')
                            ->columnSpan(1),
                        TextEntry::make('expected_date')
                            ->label('วันที่คาดว่าจะได้รับ')
                            ->date('d/m/Y')
                            ->placeholder('-')
                            ->columnSpan(1),
                        TextEntry::make('status')
                            ->label('สถานะ')
                            ->badge()
                            ->columnSpan(1),
                        Section::make('ผู้จำหน่ายและสาขา')
                            ->icon(Heroicon::BuildingStorefront)
                            ->schema([
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
                                    ->label('จำนวน')
                                    ->numeric()
                                    ->alignRight(),
                                TextEntry::make('unit_price')
                                    ->label('ราคาต่อหน่วย')
                                    ->money('THB')
                                    ->alignRight(),
                                TextEntry::make('discount')
                                    ->label('ส่วนลด')
                                    ->money('THB')
                                    ->alignRight(),
                                TextEntry::make('total_price')
                                    ->label('ยอดรวม')
                                    ->money('THB')
                                    ->weight('bold')
                                    ->color('success')
                                    ->alignRight(),
                            ])
                            ->state(function ($record) {
                                return $record->items->map(fn($item, $index) => [
                                    'index' => $index + 1,
                                    'item_id' => $item->id,
                                    'product_name' => $item->product->name ?? '-',
                                    'description' => $item->description,
                                    'quantity' => $item->quantity,
                                    'unit_price' => $item->unit_price,
                                    'discount' => $item->discount,
                                    'total_price' => $item->total_price,
                                ]);
                            })
                            ->columns(8)
                            ->table([
                                TableColumn::make('ลำดับ'),
                                TableColumn::make('สินค้า'),
                                TableColumn::make('รายละเอียด'),
                                TableColumn::make('จำนวน'),
                                TableColumn::make('ราคาต่อหน่วย'),
                                TableColumn::make('ส่วนลด'),
                                TableColumn::make('ยอดรวม'),
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
                                    ->label('จำนวน')
                                    ->numeric()
                                    ->alignRight(),
                                TextEntry::make('unit_price')
                                    ->label('ราคาต่อหน่วย')
                                    ->money('THB')
                                    ->alignRight(),
                                TextEntry::make('discount')
                                    ->label('ส่วนลด')
                                    ->money('THB')
                                    ->alignRight(),
                                TextEntry::make('total_price')
                                    ->label('ยอดรวม')
                                    ->money('THB')
                                    ->weight('bold')
                                    ->color('success')
                                    ->alignRight(),
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
                                            ->schema([
                                                \Filament\Schemas\Components\Grid::make(3)
                                                    ->schema([
                                                        \Filament\Forms\Components\Select::make('product_id')
                                                            ->label('สินค้า')
                                                            ->options(\App\Models\Product::pluck('name', 'id'))
                                                            ->required()
                                                            ->searchable()
                                                            ->preload()
                                                            ->native(false)
                                                            ->disabled()
                                                            ->columnSpanFull(),
                                                        \Filament\Forms\Components\Textarea::make('description')
                                                            ->label('รายละเอียด')
                                                            ->rows(2)
                                                            ->columnSpanFull()
                                                            ->columnStart(1),
                                                        \Filament\Forms\Components\TextInput::make('quantity')
                                                            ->label('จำนวน')
                                                            ->required()
                                                            ->numeric()
                                                            ->minValue(1)
                                                            ->suffix('หน่วย')
                                                            ->columnSpan(1),
                                                        \Filament\Forms\Components\TextInput::make('unit_price')
                                                            ->label('ราคาต่อหน่วย')
                                                            ->required()
                                                            ->numeric()
                                                            ->prefix('฿')
                                                            ->columnSpan(1),
                                                        \Filament\Forms\Components\TextInput::make('discount')
                                                            ->label('ส่วนลด')
                                                            ->numeric()
                                                            ->default(0)
                                                            ->prefix('฿')
                                                            ->columnSpan(1),
                                                    ]),
                                            ])
                                            ->mountUsing(fn($form, $state) => $form->fill([
                                                'product_id' => PurchaseOrderItem::find($state)?->product_id,
                                                'description' => PurchaseOrderItem::find($state)?->description,
                                                'quantity' => PurchaseOrderItem::find($state)?->quantity,
                                                'unit_price' => PurchaseOrderItem::find($state)?->unit_price,
                                                'discount' => PurchaseOrderItem::find($state)?->discount,
                                            ]))
                                            ->action(function (array $data, $state, $livewire) {
                                                $item = PurchaseOrderItem::find($state);
                                                if ($item) {
                                                    $totalPrice = ($data['unit_price'] * $data['quantity']) - ($data['discount'] ?? 0);

                                                    $item->update([
                                                        'description' => $data['description'] ?? null,
                                                        'quantity' => $data['quantity'],
                                                        'unit_price' => $data['unit_price'],
                                                        'discount' => $data['discount'] ?? 0,
                                                        'total_price' => $totalPrice,
                                                    ]);

                                                    \Filament\Notifications\Notification::make()
                                                        ->success()
                                                        ->title('แก้ไขสินค้าสำเร็จ')
                                                        ->send();

                                                    // Refresh the record
                                                    $livewire->record->refresh();
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
                                            ->action(function ($state, $livewire) {
                                                $item = PurchaseOrderItem::find($state);
                                                if ($item) {
                                                    $item->delete();

                                                    \Filament\Notifications\Notification::make()
                                                        ->success()
                                                        ->title('ลบสินค้าสำเร็จ')
                                                        ->send();

                                                    // Refresh the record
                                                    $livewire->record->refresh();
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
                                    'unit_price' => $item->unit_price,
                                    'discount' => $item->discount,
                                    'total_price' => $item->total_price,
                                ]);
                            })
                            ->columns(8)
                            ->table([
                                TableColumn::make('ลำดับ'),
                                TableColumn::make('สินค้า'),
                                TableColumn::make('รายละเอียด'),
                                TableColumn::make('จำนวน'),
                                TableColumn::make('ราคาต่อหน่วย'),
                                TableColumn::make('ส่วนลด'),
                                TableColumn::make('ยอดรวม'),
                                TableColumn::make('การจัดการ'),
                            ]),
                    ]),
                Section::make('ยอดเงิน')
                    ->icon(Heroicon::CurrencyDollar)
                    ->schema([
                        TextEntry::make('subtotal')
                            ->label('ยอดรวมก่อนหัก')
                            ->money('THB')
                            ->columnSpan(1),
                        TextEntry::make('discount_amount')
                            ->label('ส่วนลด')
                            ->money('THB')
                            ->columnSpan(1),
                        TextEntry::make('vat_amount')
                            ->label('ภาษีมูลค่าเพิ่ม (VAT)')
                            ->money('THB')
                            ->columnSpan(1),
                        TextEntry::make('total_amount')
                            ->label('ยอดรวมสุทธิ')
                            ->money('THB')
                            ->weight('bold')
                            ->size('lg')
                            ->color('success')
                            ->columnSpan(1),
                    ])
                    ->columns(4)
                    ->columnSpanFull(),
                Section::make('ใบรับสินค้า')
                    ->icon(Heroicon::ArrowDownTray)
                    ->description('รายการใบรับสินค้าที่เกี่ยวข้อง')
                    ->collapsible()
                    ->collapsed()
                    ->visible(fn($record): bool => $record->goodsReceipts()->count() > 0)
                    ->schema([
                        RepeatableEntry::make('goodsReceipts')
                            ->label('รายการใบรับสินค้า')
                            ->hiddenLabel()
                            ->columnSpanFull()
                            ->schema([
                                TextEntry::make('receipt_number')
                                    ->label('เลขที่ใบรับสินค้า')
                                    ->weight('bold')
                                    ->color('primary')
                                    ->url(fn($record) => \App\Filament\Resources\GoodsReceipts\GoodsReceiptResource::getUrl('view', ['record' => $record->id])),
                                TextEntry::make('document_date')
                                    ->label('วันที่รับสินค้า')
                                    ->date('d/m/Y'),
                                TextEntry::make('supplier_delivery_no')
                                    ->label('เลขที่ใบส่งของผู้จำหน่าย')
                                    ->placeholder('-'),
                                TextEntry::make('status')
                                    ->label('สถานะ')
                                    ->badge(),
                                TextEntry::make('items_count')
                                    ->label('จำนวนรายการ')
                                    ->state(fn($record) => $record->items()->count() . ' รายการ'),
                            ])
                            ->columns(5)
                            ->table([
                                TableColumn::make('เลขที่ใบรับสินค้า'),
                                TableColumn::make('วันที่รับสินค้า'),
                                TableColumn::make('เลขที่ใบส่งของผู้จำหน่าย'),
                                TableColumn::make('สถานะ'),
                                TableColumn::make('จำนวนรายการ'),
                            ]),
                    ])
                    ->columnSpanFull(),
                Section::make('หมายเหตุและข้อมูลเพิ่มเติม')
                    ->icon(Heroicon::InformationCircle)
                    ->collapsible()
                    ->schema([
                        TextEntry::make('payment_terms')
                            ->label('เงื่อนไขการชำระเงิน')
                            ->placeholder('-'),
                        TextEntry::make('reference_number')
                            ->label('เลขที่อ้างอิง')
                            ->placeholder('-'),
                        TextEntry::make('contact_person')
                            ->label('ผู้ติดต่อ')
                            ->placeholder('-')
                            ->columnStart(1),
                        TextEntry::make('contact_phone')
                            ->label('เบอร์โทรผู้ติดต่อ')
                            ->placeholder('-'),
                        TextEntry::make('delivery_address')
                            ->label('ที่อยู่จัดส่ง')
                            ->placeholder('-'),
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
                            ->visible(fn(PurchaseOrder $record): bool => $record->trashed()),
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
                                        'สร้างใบสั่งซื้อ' => 'success',
                                        'แก้ไขใบสั่งซื้อ' => 'warning',
                                        'ลบใบสั่งซื้อ' => 'danger',
                                        default => 'gray',
                                    }),
                                TextEntry::make('properties')
                                    ->label('รายละเอียด')
                                    ->state(function ($record) {
                                        if ($record->description === 'สร้างใบสั่งซื้อ') {
                                            return 'สร้างรายการใหม่';
                                        }
                                        if ($record->description === 'ลบใบสั่งซื้อ') {
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
