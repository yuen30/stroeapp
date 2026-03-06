<?php

namespace App\Filament\Resources\SaleOrders\Pages;

use App\Enums\OrderStatus;
use App\Filament\Resources\SaleOrders\SaleOrderResource;
use Filament\Resources\Pages\ViewRecord;
use Filament\Support\Icons\Heroicon;
use Filament\Actions;

class ViewSaleOrder extends ViewRecord
{
    protected static string $resource = SaleOrderResource::class;

    protected ?string $pollingInterval = '3s';

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('addItem')
                ->label('เพิ่มสินค้า')
                ->icon(Heroicon::Plus)
                ->color('success')
                ->visible(fn(): bool => $this->record->status->value === 'draft')
                ->modalHeading('เพิ่มสินค้าในใบสั่งขาย')
                ->modalWidth('4xl')
                ->form([
                    \Filament\Schemas\Components\Grid::make(3)
                        ->schema([
                            \Filament\Forms\Components\Select::make('product_id')
                                ->label('สินค้า')
                                ->options(\App\Models\Product::pluck('name', 'id'))
                                ->searchable()
                                ->preload()
                                ->required()
                                ->native(false)
                                ->reactive()
                                ->afterStateUpdated(function ($state, callable $set) {
                                    if ($state) {
                                        $product = \App\Models\Product::find($state);
                                        if ($product) {
                                            $set('unit_price', $product->selling_price);
                                            $set('description', $product->description);
                                            $set('stock_quantity', $product->stock_quantity);
                                        }
                                    }
                                })
                                ->columnSpanFull(),
                            \Filament\Forms\Components\Placeholder::make('stock_info')
                                ->label('สต็อกคงเหลือ')
                                ->content(function (callable $get) {
                                    $productId = $get('product_id');
                                    if (!$productId) {
                                        return '-';
                                    }
                                    $product = \App\Models\Product::find($productId);
                                    if (!$product) {
                                        return '-';
                                    }
                                    $totalStock = $product->stock_quantity;
                                    $reserved = $product->reserved_quantity;
                                    $available = $product->available_stock;
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
                                                ' . ($reserved > 0 ? '<div class="text-warning-600 dark:text-warning-400">ถูกจอง: <span class="font-semibold">' . number_format($reserved) . '</span></div>' : '') . '
                                            </div>
                                        </div>
                                    ');
                                })
                                ->columnSpanFull(),
                            \Filament\Forms\Components\Textarea::make('description')
                                ->label('รายละเอียด')
                                ->rows(2)
                                ->columnSpanFull(),
                            \Filament\Forms\Components\TextInput::make('quantity')
                                ->label('จำนวน')
                                ->required()
                                ->numeric()
                                ->default(1)
                                ->minValue(1)
                                ->suffix('หน่วย')
                                ->helperText(function (callable $get) {
                                    $productId = $get('product_id');
                                    if (!$productId) {
                                        return null;
                                    }
                                    $product = \App\Models\Product::find($productId);
                                    if (!$product) {
                                        return null;
                                    }
                                    $available = $product->available_stock;
                                    $reserved = $product->reserved_quantity;
                                    return $reserved > 0
                                        ? "พร้อมใช้: {$available} หน่วย (ถูกจอง: {$reserved})"
                                        : "พร้อมใช้: {$available} หน่วย";
                                })
                                ->reactive()
                                ->afterStateUpdated(function ($state, callable $get, callable $set) {
                                    $unitPrice = $get('unit_price') ?? 0;
                                    $discount = $get('discount') ?? 0;
                                    $set('total_price', ($unitPrice * $state) - $discount);
                                }),
                            \Filament\Forms\Components\TextInput::make('unit_price')
                                ->label('ราคา/หน่วย')
                                ->required()
                                ->numeric()
                                ->prefix('฿')
                                ->reactive()
                                ->afterStateUpdated(function ($state, callable $get, callable $set) {
                                    $quantity = $get('quantity') ?? 1;
                                    $discount = $get('discount') ?? 0;
                                    $set('total_price', ($state * $quantity) - $discount);
                                }),
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
                                }),
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
                                ->columnSpan(1),
                        ])
                ])
                ->action(function (array $data) {
                    // ตรวจสอบสต็อกก่อนเพิ่มสินค้า
                    $product = \App\Models\Product::find($data['product_id']);
                    if (!$product) {
                        \Filament\Notifications\Notification::make()
                            ->danger()
                            ->title('ไม่พบสินค้า')
                            ->body('ไม่สามารถเพิ่มสินค้าได้ เนื่องจากไม่พบข้อมูลสินค้าในระบบ')
                            ->duration(5000)
                            ->send();
                        return;
                    }

                    // ตรวจสอบว่ามีสินค้านี้อยู่แล้วหรือไม่
                    $existingItem = $this
                        ->record
                        ->items()
                        ->where('product_id', $data['product_id'])
                        ->first();

                    $requestedQuantity = $data['quantity'];
                    if ($existingItem) {
                        $requestedQuantity += $existingItem->quantity;
                    }

                    // ตรวจสอบสต็อกพร้อมใช้งาน (หักการจองแล้ว)
                    $availableStock = $product->available_stock;

                    // ถ้ามีรายการเดิมอยู่แล้ว ต้องบวกสต็อกที่จองไว้กลับมา
                    if ($existingItem) {
                        $existingReservation = \App\Models\StockReservation::where('sale_order_item_id', $existingItem->id)
                            ->where('expires_at', '>', now())
                            ->first();
                        if ($existingReservation) {
                            $availableStock += $existingReservation->reserved_quantity;
                        }
                    }

                    if ($availableStock < $requestedQuantity) {
                        $reserved = $product->reserved_quantity;
                        $totalStock = $product->stock_quantity;

                        \Filament\Notifications\Notification::make()
                            ->danger()
                            ->title('สต็อกไม่เพียงพอ')
                            ->body("สินค้า {$product->name}\n• สต็อกทั้งหมด: {$totalStock} หน่วย\n• ถูกจองแล้ว: {$reserved} หน่วย\n• พร้อมใช้: {$availableStock} หน่วย\n• ต้องการ: {$requestedQuantity} หน่วย")
                            ->duration(10000)
                            ->send();
                        return;
                    }

                    if ($existingItem) {
                        // ถ้ามีแล้ว ให้อัปเดตจำนวนและคำนวณใหม่
                        $newQuantity = $existingItem->quantity + $data['quantity'];
                        $totalPrice = ($data['unit_price'] * $newQuantity) - ($data['discount'] ?? 0);

                        $existingItem->update([
                            'quantity' => $newQuantity,
                            'unit_price' => $data['unit_price'],
                            'discount' => $data['discount'] ?? 0,
                            'total_price' => $totalPrice,
                            'description' => $data['description'] ?? $existingItem->description,
                        ]);

                        \Filament\Notifications\Notification::make()
                            ->success()
                            ->title('อัปเดตสินค้าสำเร็จ')
                            ->body('เพิ่มจำนวนสินค้าที่มีอยู่แล้ว (การจองสต็อกได้รับการอัปเดตอัตโนมัติ)')
                            ->duration(3000)
                            ->send();
                    } else {
                        // ถ้ายังไม่มี ให้สร้างใหม่
                        $data['total_price'] = ($data['unit_price'] * $data['quantity']) - ($data['discount'] ?? 0);
                        $this->record->items()->create($data);

                        \Filament\Notifications\Notification::make()
                            ->success()
                            ->title('เพิ่มสินค้าสำเร็จ')
                            ->body('สต็อกได้ถูกจองอัตโนมัติแล้ว')
                            ->duration(3000)
                            ->send();
                    }

                    $this->record->refresh();

                    // คำนวณยอดรวมใหม่
                    $this->recalculateTotals();
                }),
            Actions\Action::make('confirm')
                ->label('ยืนยันใบสั่งขาย')
                ->icon(Heroicon::CheckCircle)
                ->color('info')
                ->visible(fn(): bool => $this->record->status->value === 'draft')
                ->requiresConfirmation()
                ->modalHeading('ยืนยันใบสั่งขาย')
                ->modalDescription(function () {
                    $customer = $this->record->customer;
                    $orderAmount = $this->record->total_amount;

                    // ถ้าเป็นลูกค้าเงินสด
                    if ($customer->credit_limit <= 0) {
                        return 'คุณแน่ใจหรือไม่ว่าต้องการยืนยันใบสั่งขายนี้? หลังจากยืนยันแล้วระบบจะตัดสต็อกสินค้า';
                    }

                    // ตรวจสอบวงเงินเครดิต
                    $remaining = $customer->getRemainingCreditLimit();
                    $outstanding = $customer->getTotalOutstandingAmount();

                    if ($orderAmount > $remaining) {
                        return new \Illuminate\Support\HtmlString('
                            <div class="space-y-2">
                                <p class="text-danger-600 dark:text-danger-400 font-semibold">⚠️ วงเงินเครดิตไม่เพียงพอ!</p>
                                <div class="text-sm space-y-1">
                                    <p>วงเงินทั้งหมด: <span class="font-semibold">' . number_format($customer->credit_limit, 2) . ' ฿</span></p>
                                    <p>ยอดค้างชำระ: <span class="font-semibold">' . number_format($outstanding, 2) . ' ฿</span></p>
                                    <p>วงเงินคงเหลือ: <span class="font-semibold text-danger-600">' . number_format($remaining, 2) . ' ฿</span></p>
                                    <p>ยอดใบสั่งขายนี้: <span class="font-semibold">' . number_format($orderAmount, 2) . ' ฿</span></p>
                                    <p class="text-danger-600 dark:text-danger-400 font-semibold mt-2">เกินวงเงิน: ' . number_format($orderAmount - $remaining, 2) . ' ฿</p>
                                </div>
                                <p class="text-warning-600 dark:text-warning-400 mt-2">คุณยังคงต้องการยืนยันใบสั่งขายนี้หรือไม่?</p>
                            </div>
                        ');
                    }

                    // วงเงินเพียงพอ แต่แสดงข้อมูล
                    $percentage = $customer->getCreditUsagePercentage();
                    $newPercentage = (($outstanding + $orderAmount) / $customer->credit_limit) * 100;

                    return new \Illuminate\Support\HtmlString('
                        <div class="space-y-2">
                            <p>คุณแน่ใจหรือไม่ว่าต้องการยืนยันใบสั่งขายนี้? หลังจากยืนยันแล้วระบบจะตัดสต็อกสินค้า</p>
                            <div class="text-sm space-y-1 mt-3 p-3 bg-gray-50 dark:bg-gray-800 rounded">
                                <p class="font-semibold mb-2">ข้อมูลวงเงินเครดิต:</p>
                                <p>วงเงินทั้งหมด: <span class="font-semibold">' . number_format($customer->credit_limit, 2) . ' ฿</span></p>
                                <p>ยอดค้างชำระปัจจุบัน: <span class="font-semibold">' . number_format($outstanding, 2) . ' ฿</span> (' . number_format($percentage, 1) . '%)</p>
                                <p>ยอดใบสั่งขายนี้: <span class="font-semibold">' . number_format($orderAmount, 2) . ' ฿</span></p>
                                <p class="text-success-600 dark:text-success-400">วงเงินคงเหลือหลังยืนยัน: <span class="font-semibold">' . number_format($remaining - $orderAmount, 2) . ' ฿</span> (' . number_format(100 - $newPercentage, 1) . '%)</p>
                            </div>
                        </div>
                    ');
                })
                ->action(function () {
                    // ตรวจสอบว่ามีสินค้าหรือไม่
                    if ($this->record->items()->count() === 0) {
                        \Filament\Notifications\Notification::make()
                            ->warning()
                            ->title('ไม่สามารถยืนยันได้')
                            ->body('กรุณาเพิ่มสินค้าอย่างน้อย 1 รายการก่อนยืนยันใบสั่งขาย')
                            ->duration(5000)
                            ->send();
                        return;
                    }

                    // ตรวจสอบสต็อกสินค้าก่อนยืนยัน
                    $insufficientStock = [];
                    foreach ($this->record->items as $item) {
                        $product = $item->product;
                        $availableStock = $product->available_stock;

                        // บวกสต็อกที่จองไว้สำหรับ item นี้กลับมา
                        $reservation = \App\Models\StockReservation::where('sale_order_item_id', $item->id)
                            ->where('expires_at', '>', now())
                            ->first();
                        if ($reservation) {
                            $availableStock += $reservation->reserved_quantity;
                        }

                        if ($availableStock < $item->quantity) {
                            $reserved = $product->reserved_quantity;
                            $insufficientStock[] = "{$product->name} (สต็อกทั้งหมด: {$product->stock_quantity}, ถูกจอง: {$reserved}, พร้อมใช้: {$availableStock}, ต้องการ: {$item->quantity})";
                        }
                    }

                    if (!empty($insufficientStock)) {
                        \Filament\Notifications\Notification::make()
                            ->danger()
                            ->title('สต็อกสินค้าไม่เพียงพอ')
                            ->body('สินค้าต่อไปนี้มีสต็อกไม่เพียงพอ: ' . implode(', ', $insufficientStock))
                            ->duration(10000)
                            ->send();
                        return;
                    }

                    $customer = $this->record->customer;
                    $orderAmount = $this->record->total_amount;

                    // ตรวจสอบวงเงินเครดิต (เฉพาะลูกค้าที่มีวงเงิน)
                    if ($customer->credit_limit > 0) {
                        $remaining = $customer->getRemainingCreditLimit();

                        // ถ้าเกินวงเงิน แสดงคำเตือนแต่ยังให้ยืนยันได้
                        if ($orderAmount > $remaining) {
                            \Filament\Notifications\Notification::make()
                                ->warning()
                                ->title('เกินวงเงินเครดิต')
                                ->body('ใบสั่งขายนี้เกินวงเงินเครดิตที่เหลือ ' . number_format($orderAmount - $remaining, 2) . ' ฿')
                                ->duration(10000)
                                ->send();
                        }
                    }

                    // ยืนยันใบสั่งขาย
                    try {
                        $this->record->update(['status' => OrderStatus::Confirmed]);

                        \Filament\Notifications\Notification::make()
                            ->success()
                            ->title('ยืนยันใบสั่งขายสำเร็จ')
                            ->body('ใบสั่งขายเลขที่ ' . $this->record->invoice_number . ' ได้รับการยืนยันแล้ว สต็อกถูกตัดและการจองถูกปลดล็อคอัตโนมัติ')
                            ->send();
                    } catch (\Exception $e) {
                        \Filament\Notifications\Notification::make()
                            ->danger()
                            ->title('ไม่สามารถยืนยันใบสั่งขายได้')
                            ->body($e->getMessage())
                            ->duration(10000)
                            ->send();
                    }
                }),
            Actions\Action::make('createTaxInvoice')
                ->label('สร้างใบกำกับภาษี')
                ->icon(Heroicon::DocumentText)
                ->color('primary')
                ->visible(fn(): bool => $this->record->status->value === 'confirmed')
                ->disabled(fn(): bool => $this->record->taxInvoices()->exists())
                ->tooltip(fn(): ?string => $this->record->taxInvoices()->exists()
                    ? 'มีใบกำกับภาษีสำหรับใบสั่งขายนี้แล้ว'
                    : null)
                ->url(fn() => route('filament.store.resources.tax-invoices.create', [
                    'sale_order_id' => $this->record->id
                ])),
            Actions\Action::make('printPdf')
                ->label('พิมพ์/PDF')
                ->icon(Heroicon::Printer)
                ->color('gray')
                ->visible(fn(): bool => $this->record->status->value === 'confirmed')
                ->action(function () {
                    return response()->streamDownload(function () {
                        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('pdf.sale-order', [
                            'saleOrder' => $this->record->load(['company', 'branch', 'customer', 'items.product.unit', 'creator']),
                        ]);
                        echo $pdf->stream();
                    }, 'SO-' . $this->record->invoice_number . '.pdf');
                }),
            Actions\Action::make('cancel')
                ->label('ยกเลิก')
                ->icon(Heroicon::XCircle)
                ->color('danger')
                ->visible(fn(): bool => in_array($this->record->status->value, ['draft', 'confirmed']))
                ->requiresConfirmation()
                ->modalHeading('ยกเลิกใบสั่งขาย')
                ->modalDescription('คุณแน่ใจหรือไม่ว่าต้องการยกเลิกใบสั่งขายนี้? ถ้ายืนยันแล้วระบบจะคืนสต็อกสินค้า')
                ->action(function () {
                    $this->record->update(['status' => OrderStatus::Cancelled]);

                    \Filament\Notifications\Notification::make()
                        ->success()
                        ->title('ยกเลิกใบสั่งขายสำเร็จ')
                        ->body('ใบสั่งขายเลขที่ ' . $this->record->invoice_number . ' ถูกยกเลิกแล้ว สต็อกและการจองได้รับการคืนอัตโนมัติ')
                        ->duration(3000)
                        ->send();
                }),
            Actions\EditAction::make()
                ->label('แก้ไข')
                ->icon(Heroicon::PencilSquare)
                ->visible(fn(): bool => $this->record->status->value === 'draft'),
            Actions\DeleteAction::make()
                ->label('ลบ')
                ->icon(Heroicon::Trash)
                ->visible(fn(): bool => $this->record->status->value === 'draft'),
        ];
    }

    protected function recalculateTotals(): void
    {
        $items = $this->record->items;

        $subtotal = $items->sum('total_price');
        $discountAmount = $this->record->discount_amount ?? 0;
        $vatAmount = ($subtotal - $discountAmount) * 0.07;
        $totalAmount = $subtotal - $discountAmount + $vatAmount;

        $this->record->update([
            'subtotal' => $subtotal,
            'vat_amount' => $vatAmount,
            'total_amount' => $totalAmount,
        ]);
    }
}
