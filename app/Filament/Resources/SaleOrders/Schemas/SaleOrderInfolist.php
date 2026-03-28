<?php

namespace App\Filament\Resources\SaleOrders\Schemas;

use App\Models\Product;
use App\Models\SaleOrderItem;
use App\Models\StockReservation;
use Filament\Actions\Action;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Infolists\Components\RepeatableEntry;
use Filament\Infolists\Components\RepeatableEntry\TableColumn;
use Filament\Infolists\Components\TextEntry;
use Filament\Notifications\Notification;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Text;
use Filament\Schemas\Schema;
use Filament\Support\Enums\FontWeight;
use Filament\Support\Enums\TextSize;
use Filament\Support\Icons\Heroicon;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\HtmlString;

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
                                    ->formatStateUsing(fn () => '')
                                    ->suffixAction(
                                        Action::make('edit')
                                            ->icon(Heroicon::PencilSquare)
                                            ->iconButton()
                                            ->tooltip('แก้ไข')
                                            ->color('warning')
                                            ->schema([
                                                Grid::make(3)
                                                    ->schema([
                                                        Select::make('product_id')
                                                            ->label('สินค้า')
                                                            ->options(Product::pluck('name', 'id'))
                                                            ->required()
                                                            ->searchable()
                                                            ->preload()
                                                            ->native(false)
                                                            ->disabled()
                                                            ->columnSpanFull(),
                                                        Placeholder::make('stock_info')
                                                            ->label('สต็อกคงเหลือ')
                                                            ->content(function (callable $get) {
                                                                $itemId = $get('../../item_id');
                                                                if (! $itemId) {
                                                                    return '-';
                                                                }

                                                                $item = SaleOrderItem::find($itemId);
                                                                if (! $item || ! $item->product) {
                                                                    return '-';
                                                                }

                                                                $product = Product::find($item->product_id);
                                                                if (! $product) {
                                                                    return '-';
                                                                }

                                                                $totalStock = $product->stock_quantity;
                                                                $reserved = $product->reserved_quantity;
                                                                $available = $product->available_stock;

                                                                // บวกสต็อกที่จองไว้สำหรับ item นี้กลับมา
                                                                $currentReservation = StockReservation::where('sale_order_item_id', $itemId)
                                                                    ->where('expires_at', '>', now())
                                                                    ->first();
                                                                if ($currentReservation) {
                                                                    $available += $currentReservation->reserved_quantity;
                                                                }

                                                                $color = $available > 10 ? 'success' : ($available > 0 ? 'warning' : 'danger');

                                                                return new HtmlString('
                                                                    <div class="space-y-2">
                                                                        <div class="flex items-center gap-4">
                                                                            <span class="text-'.$color.'-600 dark:text-'.$color.'-400 font-bold text-2xl">'
                                                                    .number_format($available).' หน่วย</span>
                                                                            <span class="text-sm text-gray-500 dark:text-gray-400">พร้อมใช้งาน</span>
                                                                        </div>
                                                                        <div class="text-xs text-gray-600 dark:text-gray-400 space-y-1">
                                                                            <div>สต็อกทั้งหมด: <span class="font-semibold">'.number_format($totalStock).'</span></div>
                                                                            '.($reserved > 0 ? '<div class="text-warning-600 dark:text-warning-400">ถูกจองโดยใบอื่น: <span class="font-semibold">'.number_format($reserved - ($currentReservation ? $currentReservation->reserved_quantity : 0)).'</span></div>' : '').'
                                                                        </div>
                                                                    </div>
                                                                ');
                                                            })
                                                            ->columnSpanFull(),
                                                        Textarea::make('description')
                                                            ->label('รายละเอียด')
                                                            ->rows(2)
                                                            ->columnSpanFull()
                                                            ->columnStart(1),
                                                        TextInput::make('quantity')
                                                            ->label('จำนวน')
                                                            ->required()
                                                            ->numeric()
                                                            ->minValue(1)
                                                            ->suffix('หน่วย')
                                                            ->helperText(function (callable $get) {
                                                                $itemId = $get('../../item_id');
                                                                if (! $itemId) {
                                                                    return null;
                                                                }

                                                                $item = SaleOrderItem::find($itemId);
                                                                if (! $item || ! $item->product) {
                                                                    return null;
                                                                }

                                                                $product = Product::find($item->product_id);
                                                                if (! $product) {
                                                                    return null;
                                                                }

                                                                $available = $product->available_stock;

                                                                // บวกสต็อกที่จองไว้สำหรับ item นี้กลับมา
                                                                $currentReservation = StockReservation::where('sale_order_item_id', $itemId)
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
                                                        TextInput::make('unit_price')
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
                                                        TextInput::make('discount')
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
                                                        Text::make(
                                                            fn (callable $get): string => '฿ '.number_format(
                                                                (($get('unit_price') ?? 0) * ($get('quantity') ?? 0)) - ($get('discount') ?? 0),
                                                                2
                                                            )
                                                        )
                                                            ->color('success')
                                                            ->badge()
                                                            ->size(TextSize::Large)
                                                            ->weight(FontWeight::Bold)
                                                            ->columnSpan(3),
                                                    ]),
                                            ])
                                            ->mountUsing(fn ($form, $state) => $form->fill([
                                                'product_id' => SaleOrderItem::find($state)?->product_id,
                                                'description' => SaleOrderItem::find($state)?->description,
                                                'quantity' => SaleOrderItem::find($state)?->quantity,
                                                'unit_price' => SaleOrderItem::find($state)?->unit_price,
                                                'discount' => SaleOrderItem::find($state)?->discount,
                                            ]))
                                            ->action(function (array $data, $state, $livewire) {
                                                $item = SaleOrderItem::find($state);
                                                if (! $item) {
                                                    Notification::make()
                                                        ->danger()
                                                        ->title('ไม่พบรายการสินค้า')
                                                        ->body('ไม่สามารถแก้ไขรายการสินค้าได้ เนื่องจากไม่พบข้อมูล')
                                                        ->duration(10000)
                                                        ->send();

                                                    return;
                                                }

                                                // ดึงข้อมูล Product ล่าสุดจากฐานข้อมูล (fresh query)
                                                $product = Product::find($item->product_id);
                                                if (! $product) {
                                                    Notification::make()
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
                                                $currentReservation = StockReservation::where('sale_order_item_id', $item->id)
                                                    ->where('expires_at', '>', now())
                                                    ->first();
                                                if ($currentReservation) {
                                                    $availableStock += $currentReservation->reserved_quantity;
                                                }

                                                // แจ้งเตือนถ้าสต็อกไม่พอ แต่ยังอนุญาตให้บันทึกได้
                                                if ($availableStock < $data['quantity']) {
                                                    $reserved = $product->reserved_quantity;
                                                    $totalStock = $product->stock_quantity;
                                                    $shortage = $data['quantity'] - $availableStock;

                                                    Notification::make()
                                                        ->warning()
                                                        ->title('สต็อกไม่เพียงพอ')
                                                        ->body("สินค้า {$product->name}\n• สต็อกพร้อมใช้: {$availableStock} หน่วย\n• ต้องการ: {$data['quantity']} หน่วย\n• ขาด: {$shortage} หน่วย\n\n⚠️ ระบบจะบันทึกการเปลี่ยนแปลงและปรับยอดจองให้อัตโนมัติ")
                                                        ->duration(8000)
                                                        ->send();
                                                }

                                                $totalPrice = ($data['unit_price'] * $data['quantity']) - ($data['discount'] ?? 0);

                                                $item->update([
                                                    'description' => $data['description'] ?? null,
                                                    'quantity' => $data['quantity'],
                                                    'unit_price' => $data['unit_price'],
                                                    'discount' => $data['discount'] ?? 0,
                                                    'total_price' => $totalPrice,
                                                ]);

                                                Notification::make()
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
                                        Action::make('delete')
                                            ->icon(Heroicon::Trash)
                                            ->iconButton()
                                            ->tooltip('ลบ')
                                            ->color('danger')
                                            ->requiresConfirmation()
                                            ->modalHeading('ลบสินค้า')
                                            ->modalDescription('คุณแน่ใจหรือไม่ว่าต้องการลบสินค้านี้?')
                                            ->action(function ($state, $livewire) {
                                                $item = SaleOrderItem::find($state);
                                                if ($item) {
                                                    $item->delete();

                                                    Notification::make()
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
                                return $record->items->map(fn ($item, $index) => [
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
                    ->afterHeader(Action::make('update_totals')
                        ->label('แก้ไขยอดเงิน')
                        ->icon(Heroicon::Pencil)
                        ->color('primary')
                        ->fillForm(fn ($record) => [
                            'subtotal' => $record->subtotal,
                            'discount_amount' => $record->discount_amount,
                            'vat_amount' => $record->vat_amount,
                            'total_amount' => $record->total_amount,
                        ])
                        ->form([
                            TextInput::make('subtotal')
                                ->label('มูลค่าสินค้า')
                                ->numeric()
                                ->prefix('฿')
                                ->required(),
                            TextInput::make('discount_amount')
                                ->label('ส่วนลดท้ายบิล')
                                ->numeric()
                                ->prefix('฿'),
                            TextInput::make('vat_amount')
                                ->label('ภาษีมูลค่าเพิ่ม (VAT)')
                                ->numeric()
                                ->prefix('฿'),
                            TextInput::make('total_amount')
                                ->label('ยอดสุทธิ')
                                ->numeric()
                                ->prefix('฿'),
                        ])
                        ->action(function (array $data, $livewire) {
                            $subtotal = (float) ($data['subtotal'] ?? 0);
                            $discount = (float) ($data['discount_amount'] ?? 0);
                            $vat = (float) ($data['vat_amount'] ?? 0);
                            $total = $subtotal - $discount + $vat;

                            $record = $livewire->record;
                            $record->update([
                                'subtotal' => $subtotal,
                                'discount_amount' => $discount,
                                'vat_amount' => $vat,
                                'total_amount' => $total,
                            ]);

                            Notification::make()
                                ->success()
                                ->title('อัปเดตยอดเงินสำเร็จ')
                                ->body('ยอดเงินได้รับการอัปเดตแล้ว')
                                ->duration(3000)
                                ->send();

                            $livewire->record->refresh();
                        })
                        ->modalHeading('แก้ไขยอดเงิน')
                        ->modalDescription('ปรับยอดมูลค่าสินค้า ส่วนลด และ VAT ได้')
                        ->modalSubmitActionLabel('บันทึก')
                        ->modalWidth('md'))
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
                        TextEntry::make('paymentStatus.name')
                            ->label('สถานะการชำระเงิน')
                            ->badge(),
                        TextEntry::make('paymentMethod.name')
                            ->label('ช่องทางการชำระเงิน')
                            ->placeholder('-'),
                        TextEntry::make('term_of_payment')
                            ->label('เงื่อนไขการชำระเงิน/วันครบกำหนด')
                            ->placeholder('-'),
                        TextEntry::make('document_type')
                            ->label('ประเภทเอกสาร')
                            ->formatStateUsing(fn ($state) => $state?->getLabel() ?? '-'),
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
                    ->collapsed(fn ($record) => ! $record->delivery_date && ! $record->shipping_method && ! $record->shipping_address)
                    ->visible(fn ($record) => $record->delivery_date || $record->shipping_method || $record->shipping_address)
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
                    ->collapsed(fn ($record) => ! $record->contact_person && ! $record->contact_phone)
                    ->visible(fn ($record) => $record->contact_person || $record->contact_phone)
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
                    ->visible(fn ($record) => ! empty($record->attachments))
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
                                    $url = Storage::url($file);
                                    $files[] = "<a href='{$url}' target='_blank' class='text-primary-600 hover:underline'>{$filename}</a>";
                                }

                                return new HtmlString(implode('<br>', $files));
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
