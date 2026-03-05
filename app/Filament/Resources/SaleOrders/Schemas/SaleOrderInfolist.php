<?php

namespace App\Filament\Resources\SaleOrders\Schemas;

use Filament\Infolists\Components\RepeatableEntry\TableColumn;
use Filament\Infolists\Components\ImageEntry;
use Filament\Infolists\Components\RepeatableEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;

class SaleOrderInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('ข้อมูลทั่วไป')
                    ->icon(Heroicon::DocumentText)
                    ->schema([
                        TextEntry::make('invoice_number')
                            ->label('เลขที่เอกสาร')
                            ->badge()
                            ->color('primary')
                            ->columnSpan(1),
                        TextEntry::make('order_date')
                            ->label('วันที่สั่งซื้อ')
                            ->date('d/m/Y')
                            ->columnSpan(1),
                        TextEntry::make('due_date')
                            ->label('วันครบกำหนด')
                            ->date('d/m/Y')
                            ->placeholder('-')
                            ->columnSpan(1),
                        TextEntry::make('status')
                            ->label('สถานะ')
                            ->badge()
                            ->columnSpan(1),
                        Section::make('ลูกค้าและสาขา')
                            ->icon(Heroicon::UserGroup)
                            ->schema([
                                TextEntry::make('customer.name')
                                    ->label('ลูกค้า')
                                    ->icon(Heroicon::User)
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
                                            ->form([
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
                                                        \Filament\Forms\Components\Placeholder::make('stock_info')
                                                            ->label('สต็อกคงเหลือ')
                                                            ->content(function (callable $get) {
                                                                $itemId = $get('../../item_id');
                                                                if (!$itemId) {
                                                                    return '-';
                                                                }

                                                                $item = \App\Models\SaleOrderItem::find($itemId);
                                                                if (!$item || !$item->product) {
                                                                    return '-';
                                                                }

                                                                $product = \App\Models\Product::find($item->product_id);
                                                                if (!$product) {
                                                                    return '-';
                                                                }

                                                                $totalStock = $product->stock_quantity;
                                                                $reserved = $product->reserved_quantity;
                                                                $available = $product->available_stock;

                                                                // บวกสต็อกที่จองไว้สำหรับ item นี้กลับมา
                                                                $currentReservation = \App\Models\StockReservation::where('sale_order_item_id', $itemId)
                                                                    ->where('expires_at', '>', now())
                                                                    ->first();
                                                                if ($currentReservation) {
                                                                    $available += $currentReservation->reserved_quantity;
                                                                }

                                                                $color = $available > 10 ? 'success' : ($available > 0 ? 'warning' : 'danger');

                                                                return new \Illuminate\Support\HtmlString('
                                                                    <div class="space-y-2">
                                                                        <div class="flex items-center gap-4">
                                                                            <span class="text-' . $color . '-600 dark:text-' . $color . '-400 font-bold text-2xl">'
                                                                    . number_format($available) . ' หน่วย</span>
                                                                            <span class="text-sm text-gray-500 dark:text-gray-400">พร้อมใช้งาน</span>
                                                                        </div>
                                                                        <div class="text-xs text-gray-600 dark:text-gray-400 space-y-1">
                                                                            <div>สต็อกทั้งหมด: <span class="font-semibold">' . number_format($totalStock) . '</span></div>
                                                                            ' . ($reserved > 0 ? '<div class="text-warning-600 dark:text-warning-400">ถูกจองโดยใบอื่น: <span class="font-semibold">' . number_format($reserved - ($currentReservation ? $currentReservation->reserved_quantity : 0)) . '</span></div>' : '') . '
                                                                        </div>
                                                                    </div>
                                                                ');
                                                            })
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
                                                            ->helperText(function (callable $get) {
                                                                $itemId = $get('../../item_id');
                                                                if (!$itemId) {
                                                                    return null;
                                                                }

                                                                $item = \App\Models\SaleOrderItem::find($itemId);
                                                                if (!$item || !$item->product) {
                                                                    return null;
                                                                }

                                                                $product = \App\Models\Product::find($item->product_id);
                                                                if (!$product) {
                                                                    return null;
                                                                }

                                                                $available = $product->available_stock;

                                                                // บวกสต็อกที่จองไว้สำหรับ item นี้กลับมา
                                                                $currentReservation = \App\Models\StockReservation::where('sale_order_item_id', $itemId)
                                                                    ->where('expires_at', '>', now())
                                                                    ->first();
                                                                if ($currentReservation) {
                                                                    $available += $currentReservation->reserved_quantity;
                                                                }

                                                                return "พร้อมใช้: {$available} หน่วย";
                                                            })
                                                            ->reactive()
                                                            ->afterStateUpdated(function ($state, callable $get, callable $set) {
                                                                $unitPrice = $get('unit_price') ?? 0;
                                                                $discount = $get('discount') ?? 0;
                                                                $set('total_price', ($unitPrice * $state) - $discount);
                                                            })
                                                            ->columnSpan(1),
                                                        \Filament\Forms\Components\TextInput::make('unit_price')
                                                            ->label('ราคาต่อหน่วย')
                                                            ->required()
                                                            ->numeric()
                                                            ->prefix('฿')
                                                            ->reactive()
                                                            ->afterStateUpdated(function ($state, callable $get, callable $set) {
                                                                $quantity = $get('quantity') ?? 1;
                                                                $discount = $get('discount') ?? 0;
                                                                $set('total_price', ($state * $quantity) - $discount);
                                                            })
                                                            ->columnSpan(1),
                                                        \Filament\Forms\Components\TextInput::make('discount')
                                                            ->label('ส่วนลด')
                                                            ->numeric()
                                                            ->default(0)
                                                            ->prefix('฿')
                                                            ->reactive()
                                                            ->afterStateUpdated(function ($state, callable $get, callable $set) {
                                                                $quantity = $get('quantity') ?? 1;
                                                                $unitPrice = $get('unit_price') ?? 0;
                                                                $set('total_price', ($unitPrice * $quantity) - $state);
                                                            })
                                                            ->columnSpan(1),
                                                        \Filament\Schemas\Components\Text::make(
                                                            fn(callable $get): string =>
                                                                '฿ ' . number_format(
                                                                    (($get('unit_price') ?? 0) * ($get('quantity') ?? 0)) - ($get('discount') ?? 0),
                                                                    2
                                                                )
                                                        )
                                                            ->color('success')
                                                            ->badge()
                                                            ->size(\Filament\Support\Enums\TextSize::Large)
                                                            ->weight(\Filament\Support\Enums\FontWeight::Bold)
                                                            ->columnSpan(3),
                                                    ]),
                                            ])
                                            ->mountUsing(fn($form, $state) => $form->fill([
                                                'product_id' => \App\Models\SaleOrderItem::find($state)?->product_id,
                                                'description' => \App\Models\SaleOrderItem::find($state)?->description,
                                                'quantity' => \App\Models\SaleOrderItem::find($state)?->quantity,
                                                'unit_price' => \App\Models\SaleOrderItem::find($state)?->unit_price,
                                                'discount' => \App\Models\SaleOrderItem::find($state)?->discount,
                                            ]))
                                            ->action(function (array $data, $state, $livewire) {
                                                $item = \App\Models\SaleOrderItem::find($state);
                                                if (!$item) {
                                                    \Filament\Notifications\Notification::make()
                                                        ->danger()
                                                        ->title('ไม่พบรายการสินค้า')
                                                        ->body('ไม่สามารถแก้ไขรายการสินค้าได้ เนื่องจากไม่พบข้อมูล')
                                                        ->duration(10000)
                                                        ->send();
                                                    return;
                                                }

                                                // ดึงข้อมูล Product ล่าสุดจากฐานข้อมูล (fresh query)
                                                $product = \App\Models\Product::find($item->product_id);
                                                if (!$product) {
                                                    \Filament\Notifications\Notification::make()
                                                        ->danger()
                                                        ->title('ไม่พบสินค้า')
                                                        ->body('ไม่สามารถแก้ไขรายการสินค้าได้ เนื่องจากไม่พบข้อมูลสินค้า')
                                                        ->duration(10000)
                                                        ->send();
                                                    return;
                                                }

                                                // ตรวจสอบสต็อกพร้อมใช้งาน
                                                $availableStock = $product->available_stock;

                                                // บวกสต็อกที่จองไว้สำหรับ item นี้กลับมา
                                                $currentReservation = \App\Models\StockReservation::where('sale_order_item_id', $item->id)
                                                    ->where('expires_at', '>', now())
                                                    ->first();
                                                if ($currentReservation) {
                                                    $availableStock += $currentReservation->reserved_quantity;
                                                }

                                                if ($availableStock < $data['quantity']) {
                                                    $reserved = $product->reserved_quantity;
                                                    $totalStock = $product->stock_quantity;

                                                    \Filament\Notifications\Notification::make()
                                                        ->danger()
                                                        ->title('สต็อกไม่เพียงพอ')
                                                        ->body("สินค้า {$product->name}\n• สต็อกทั้งหมด: {$totalStock} หน่วย\n• ถูกจองแล้ว: {$reserved} หน่วย\n• พร้อมใช้: {$availableStock} หน่วย\n• ต้องการ: {$data['quantity']} หน่วย")
                                                        ->duration(10000)
                                                        ->send();
                                                    return;
                                                }

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
                                                    ->body('รายการสินค้าได้รับการอัปเดตแล้ว การจองสต็อกได้รับการปรับปรุงอัตโนมัติ')
                                                    ->duration(3000)
                                                    ->send();

                                                // Refresh the record
                                                $livewire->record->refresh();
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
                                                $item = \App\Models\SaleOrderItem::find($state);
                                                if ($item) {
                                                    $item->delete();

                                                    \Filament\Notifications\Notification::make()
                                                        ->success()
                                                        ->title('ลบสินค้าสำเร็จ')
                                                        ->body('รายการสินค้าถูกลบออกจากใบสั่งขายแล้ว การจองสต็อกได้ถูกปลดล็อคอัตโนมัติ')
                                                        ->duration(3000)
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
                Section::make('ข้อมูลการชำระเงิน')
                    ->icon(Heroicon::CreditCard)
                    ->schema([
                        TextEntry::make('payment_status')
                            ->label('สถานะการชำระเงิน')
                            ->badge(),
                        TextEntry::make('payment_method')
                            ->label('ช่องทางการชำระเงิน')
                            ->formatStateUsing(fn($state) => $state?->getLabel() ?? '-'),
                        TextEntry::make('term_of_payment')
                            ->label('เงื่อนไขการชำระเงิน')
                            ->placeholder('-'),
                        TextEntry::make('document_type')
                            ->label('ประเภทเอกสาร')
                            ->formatStateUsing(fn($state) => $state?->getLabel() ?? '-'),
                        TextEntry::make('reference_number')
                            ->label('เลขที่อ้างอิง')
                            ->placeholder('-')
                            ->icon(Heroicon::DocumentDuplicate)
                            ->columnSpan(2),
                    ])
                    ->columns(4)
                    ->columnSpanFull(),
                Section::make('ข้อมูลการจัดส่ง')
                    ->icon(Heroicon::Truck)
                    ->collapsible()
                    ->collapsed(fn($record) => !$record->delivery_date && !$record->shipping_method && !$record->shipping_address)
                    ->visible(fn($record) => $record->delivery_date || $record->shipping_method || $record->shipping_address)
                    ->schema([
                        TextEntry::make('delivery_date')
                            ->label('วันที่จัดส่ง')
                            ->date('d/m/Y')
                            ->placeholder('-')
                            ->icon(Heroicon::Calendar),
                        TextEntry::make('shipping_method')
                            ->label('วิธีการจัดส่ง')
                            ->placeholder('-')
                            ->icon(Heroicon::Truck),
                        TextEntry::make('shipping_address')
                            ->label('ที่อยู่จัดส่ง')
                            ->placeholder('ใช้ที่อยู่ลูกค้า')
                            ->icon(Heroicon::MapPin)
                            ->columnSpanFull(),
                    ])
                    ->columns(2)
                    ->columnSpanFull(),
                Section::make('ข้อมูลผู้ติดต่อ')
                    ->icon(Heroicon::Phone)
                    ->collapsible()
                    ->collapsed(fn($record) => !$record->contact_person && !$record->contact_phone)
                    ->visible(fn($record) => $record->contact_person || $record->contact_phone)
                    ->schema([
                        TextEntry::make('contact_person')
                            ->label('ชื่อผู้ติดต่อ')
                            ->placeholder('-')
                            ->icon(Heroicon::User),
                        TextEntry::make('contact_phone')
                            ->label('เบอร์โทรติดต่อ')
                            ->placeholder('-')
                            ->icon(Heroicon::Phone),
                    ])
                    ->columns(2)
                    ->columnSpanFull(),
                Section::make('ไฟล์แนบ')
                    ->icon(Heroicon::PaperClip)
                    ->collapsible()
                    ->collapsed()
                    ->visible(fn($record) => !empty($record->attachments))
                    ->schema([
                        TextEntry::make('attachments')
                            ->label('ไฟล์แนบ')
                            ->listWithLineBreaks()
                            ->formatStateUsing(function ($state) {
                                if (empty($state)) {
                                    return '-';
                                }
                                $files = [];
                                foreach ($state as $file) {
                                    $filename = basename($file);
                                    $url = \Storage::url($file);
                                    $files[] = "<a href='{$url}' target='_blank' class='text-primary-600 hover:underline'>{$filename}</a>";
                                }
                                return new \Illuminate\Support\HtmlString(implode('<br>', $files));
                            })
                            ->columnSpanFull(),
                    ])
                    ->columnSpanFull(),
                Section::make('หมายเหตุและข้อมูลเพิ่มเติม')
                    ->icon(Heroicon::InformationCircle)
                    ->collapsible()
                    ->schema([
                        TextEntry::make('salesman.name')
                            ->label('พนักงานขาย')
                            ->icon(Heroicon::User)
                            ->placeholder('-'),
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
                    ])
                    ->columns(4)
                    ->columnSpanFull(),
            ]);
    }
}
