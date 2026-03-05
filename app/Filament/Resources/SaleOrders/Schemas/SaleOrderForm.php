<?php

namespace App\Filament\Resources\SaleOrders\Schemas;

use App\Enums\DocumentType;
use App\Enums\OrderStatus;
use App\Enums\PaymentMethod;
use App\Enums\PaymentStatus;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;

class SaleOrderForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                \Filament\Schemas\Components\Callout::make('คำเตือน')
                    ->description('การแก้ไขใบสั่งขายจะส่งผลต่อสต็อกสินค้าและรายการเอกสารที่เกี่ยวข้อง')
                    ->warning()
                    ->icon(Heroicon::ExclamationTriangle)
                    ->visible(fn($context) => $context === 'edit')
                    ->columnSpanFull(),
                \Filament\Schemas\Components\Section::make('ข้อมูลทั่วไป')
                    ->description('ข้อมูลพื้นฐานของใบสั่งขาย')
                    ->icon(Heroicon::DocumentText)
                    ->collapsible()
                    ->schema([
                        DatePicker::make('order_date')
                            ->label('วันที่สั่งซื้อ')
                            ->required()
                            ->default(now())
                            ->native(false)
                            ->displayFormat('d/m/Y')
                            ->columnSpan(1),
                        DatePicker::make('due_date')
                            ->label('วันครบกำหนด')
                            ->native(false)
                            ->displayFormat('d/m/Y')
                            ->helperText('วันที่ครบกำหนดชำระเงิน')
                            ->columnSpan(1),
                        Select::make('customer_id')
                            ->label('ลูกค้า')
                            ->relationship('customer', 'name')
                            ->required()
                            ->searchable()
                            ->preload()
                            ->native(false)
                            ->placeholder('เลือกลูกค้า')
                            ->reactive()
                            ->afterStateUpdated(function ($state, callable $set) {
                                if ($state) {
                                    $customer = \App\Models\Customer::find($state);
                                    if ($customer) {
                                        // ดึงข้อมูลจากลูกค้ามาแสดงตามที่มี
                                        $set('term_of_payment', $customer->credit_days ?? null);

                                        // คำนวณวันครบกำหนดถ้ามี credit_days
                                        if ($customer->credit_days > 0) {
                                            $set('due_date', now()->addDays($customer->credit_days)->format('Y-m-d'));
                                        }
                                    }
                                }
                            })
                            ->createOptionForm([
                                TextInput::make('name')
                                    ->label('ชื่อลูกค้า')
                                    ->required()
                                    ->maxLength(255),
                                TextInput::make('code')
                                    ->label('รหัสลูกค้า')
                                    ->maxLength(50)
                                    ->placeholder('ถ้าไม่ระบุจะสร้างอัตโนมัติ'),
                                TextInput::make('tax_id')
                                    ->label('เลขประจำตัวผู้เสียภาษี')
                                    ->maxLength(13),
                                TextInput::make('tel')
                                    ->label('เบอร์โทรศัพท์')
                                    ->tel()
                                    ->maxLength(20),
                                Textarea::make('address_0')
                                    ->label('ที่อยู่')
                                    ->rows(2)
                                    ->columnSpanFull(),
                            ])
                            ->createOptionModalHeading('เพิ่มลูกค้าใหม่')
                            ->columnSpan(2),
                        \Filament\Forms\Components\Placeholder::make('credit_info')
                            ->label('ข้อมูลวงเงินเครดิต')
                            ->content(function (callable $get) {
                                $customerId = $get('customer_id');
                                if (!$customerId) {
                                    return 'กรุณาเลือกลูกค้าก่อน';
                                }

                                $customer = \App\Models\Customer::find($customerId);
                                if (!$customer) {
                                    return '-';
                                }

                                // ถ้าไม่มีวงเงินเครดิต (เงินสด)
                                if ($customer->credit_limit <= 0) {
                                    return new \Illuminate\Support\HtmlString('
                                        <div class="text-sm">
                                            <div class="flex items-center gap-2 text-success-600 dark:text-success-400">
                                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                                </svg>
                                                <span class="font-semibold">ลูกค้าเงินสด</span>
                                            </div>
                                        </div>
                                    ');
                                }

                                $outstanding = $customer->getTotalOutstandingAmount();
                                $remaining = $customer->getRemainingCreditLimit();
                                $percentage = $customer->getCreditUsagePercentage();

                                $statusColor = match (true) {
                                    $percentage >= 90 => 'danger',
                                    $percentage >= 70 => 'warning',
                                    default => 'success',
                                };

                                return new \Illuminate\Support\HtmlString('
                                    <div class="space-y-2 text-sm">
                                        <div class="grid grid-cols-2 gap-2">
                                            <div>
                                                <span class="text-gray-500 dark:text-gray-400">วงเงินทั้งหมด:</span>
                                                <span class="font-semibold ml-1">' . number_format($customer->credit_limit, 2) . ' ฿</span>
                                            </div>
                                            <div>
                                                <span class="text-gray-500 dark:text-gray-400">ยอดค้างชำระ:</span>
                                                <span class="font-semibold ml-1">' . number_format($outstanding, 2) . ' ฿</span>
                                            </div>
                                        </div>
                                        <div>
                                            <div class="flex justify-between items-center mb-1">
                                                <span class="text-gray-500 dark:text-gray-400">วงเงินคงเหลือ:</span>
                                                <span class="font-bold text-' . $statusColor . '-600 dark:text-' . $statusColor . '-400">' . number_format($remaining, 2) . ' ฿</span>
                                            </div>
                                            <div class="w-full bg-gray-200 rounded-full h-2 dark:bg-gray-700">
                                                <div class="bg-' . $statusColor . '-600 h-2 rounded-full" style="width: ' . $percentage . '%"></div>
                                            </div>
                                            <div class="text-xs text-gray-500 dark:text-gray-400 mt-1">
                                                ใช้ไปแล้ว ' . number_format($percentage, 1) . '%
                                            </div>
                                        </div>
                                    </div>
                                ');
                            })
                            ->visible(fn(callable $get) => $get('customer_id') !== null)
                            ->columnSpan(2),
                        Select::make('salesman_id')
                            ->label('พนักงานขาย')
                            ->relationship('salesman', 'name')
                            ->searchable()
                            ->preload()
                            ->native(false)
                            ->placeholder('เลือกพนักงานขาย')
                            ->default(fn() => auth()->user()?->id),
                        Select::make('company_id')
                            ->label('บริษัท')
                            ->relationship('company', 'name')
                            ->required()
                            ->searchable()
                            ->preload()
                            ->native(false)
                            ->placeholder('เลือกบริษัท')
                            ->default(fn() => auth()->user()?->company_id)
                            ->reactive()
                            ->columnSpan(1),
                        Select::make('branch_id')
                            ->label('สาขา')
                            ->relationship('branch', 'name', fn($query, $get) =>
                                $get('company_id') ? $query->where('company_id', $get('company_id')) : $query)
                            ->searchable()
                            ->preload()
                            ->native(false)
                            ->placeholder('เลือกสาขา')
                            ->default(fn() => auth()->user()?->branch_id)
                            ->columnSpan(1),
                    ])
                    ->columns(2)
                    ->columnSpanFull(),
                \Filament\Schemas\Components\Section::make('รายการสินค้า')
                    ->description('เพิ่มรายการสินค้าลงในใบสั่งขาย กำหนดจำนวนและราคา')
                    ->icon(Heroicon::ShoppingCart)
                    ->schema([
                        \Filament\Forms\Components\Repeater::make('items')
                            ->relationship()
                            ->hiddenLabel()
                            ->columns(6)
                            ->schema([
                                Select::make('product_id')
                                    ->label('สินค้า')
                                    ->relationship('product', 'name')
                                    ->searchable()
                                    ->preload()
                                    ->required()
                                    ->native(false)
                                    ->reactive()
                                    ->afterStateUpdated(function ($state, callable $set, callable $get) {
                                        $product = \App\Models\Product::find($state);
                                        if ($product) {
                                            $set('unit_price', $product->price);
                                            $qty = $get('quantity') ?: 1;
                                            $set('quantity', $qty);
                                            $discount = $get('discount') ?: 0;
                                            $total = ($qty * $product->price) - $discount;
                                            $set('total_price', max(0, $total));
                                        }
                                    })
                                    ->columnSpan(2),
                                TextInput::make('description')
                                    ->label('รายละเอียดเพิ่มเติม')
                                    ->columnSpan(4),
                                TextInput::make('quantity')
                                    ->label('จำนวน')
                                    ->numeric()
                                    ->default(1)
                                    ->required()
                                    ->minValue(1)
                                    ->reactive()
                                    ->afterStateUpdated(fn($state, callable $set, callable $get) => self::updateItemTotal($set, $get))
                                    ->columnSpan(1),
                                TextInput::make('unit_price')
                                    ->label('ราคาต่อหน่วย')
                                    ->numeric()
                                    ->default(0)
                                    ->required()
                                    ->reactive()
                                    ->afterStateUpdated(fn($state, callable $set, callable $get) => self::updateItemTotal($set, $get))
                                    ->columnSpan(2),
                                TextInput::make('discount')
                                    ->label('ส่วนลด')
                                    ->numeric()
                                    ->default(0)
                                    ->reactive()
                                    ->afterStateUpdated(fn($state, callable $set, callable $get) => self::updateItemTotal($set, $get))
                                    ->columnSpan(1),
                                TextInput::make('total_price')
                                    ->label('รวมเงิน')
                                    ->numeric()
                                    ->required()
                                    ->disabled()
                                    ->dehydrated()
                                    ->columnSpan(2),
                            ])
                            ->reactive()
                            ->afterStateUpdated(fn($state, callable $set, callable $get) => self::updateGrandTotal($set, $get))
                            ->itemLabel(fn(array $state): ?string => $state['product_id'] ?? null ? \App\Models\Product::find($state['product_id'])?->name : null)
                            ->addActionLabel('เพิ่มรายการสินค้า')
                            ->defaultItems(1)
                    ])
                    ->visibleOn('edit')
                    ->columnSpanFull(),
                \Filament\Schemas\Components\Section::make('สรุปยอดเงิน')
                    ->description('ยอดยกมา หักส่วนลด และคำนวณภาษี')
                    ->icon(Heroicon::Calculator)
                    ->collapsible()
                    ->visibleOn('edit')
                    ->schema([
                        TextInput::make('subtotal')
                            ->label('มูลค่าสินค้า (Subtotal)')
                            ->numeric()
                            ->default(0)
                            ->disabled()
                            ->dehydrated()
                            ->prefix('฿')
                            ->columnSpan(1),
                        TextInput::make('discount_amount')
                            ->label('ส่วนลดท้ายบิล')
                            ->numeric()
                            ->default(0)
                            ->reactive()
                            ->afterStateUpdated(fn($state, callable $set, callable $get) => self::updateGrandTotal($set, $get))
                            ->prefix('฿')
                            ->columnSpan(1),
                        \Filament\Forms\Components\Toggle::make('is_vat_included')
                            ->label('รวมภาษีมูลค่าเพิ่ม (VAT 7%)')
                            ->default(true)
                            ->reactive()
                            ->afterStateUpdated(fn($state, callable $set, callable $get) => self::updateGrandTotal($set, $get))
                            ->columnSpanFull(),
                        TextInput::make('vat_amount')
                            ->label('ภาษีมูลค่าเพิ่ม (VAT)')
                            ->numeric()
                            ->default(0)
                            ->disabled()
                            ->dehydrated()
                            ->prefix('฿')
                            ->columnSpan(1),
                        TextInput::make('total_amount')
                            ->label('ยอดสุทธิ (Total)')
                            ->numeric()
                            ->default(0)
                            ->disabled()
                            ->dehydrated()
                            ->prefix('฿')
                            ->extraInputAttributes(['class' => 'text-xl font-bold text-primary-600 dark:text-primary-400'])
                            ->columnSpan(1),
                    ])
                    ->columns(2)
                    ->columnSpanFull(),
                \Filament\Schemas\Components\Section::make('ข้อมูลสถานะและเพิ่มเติม')
                    ->description('ข้อมูลการอ้างอิงและสถานะเอกสาร')
                    ->icon(Heroicon::InformationCircle)
                    ->collapsible()
                    ->schema([
                        TextInput::make('invoice_number')
                            ->label('เลขที่เอกสาร')
                            ->placeholder('ถ้าไม่ระบุ ระบบจะสร้างอัตโนมัติ')
                            ->maxLength(50)
                            ->unique(ignoreRecord: true)
                            ->visibleOn('create')
                            ->columnSpan(1),
                        TextInput::make('invoice_number')
                            ->label('เลขที่เอกสาร')
                            ->disabled()
                            ->visibleOn(['edit', 'view'])
                            ->columnSpan(1),
                        Select::make('document_type')
                            ->label('ประเภทเอกสาร')
                            ->options(DocumentType::class)
                            ->default('tax_invoice')
                            ->required()
                            ->native(false)
                            ->columnSpan(1),
                        TextInput::make('term_of_payment')
                            ->label('เงื่อนไขการชำระเงิน')
                            ->placeholder('เช่น สินเชื่อ 30 วัน, เงินสด')
                            ->maxLength(255)
                            ->columnSpan(1),
                        Select::make('payment_method')
                            ->label('ช่องทางการชำระเงิน')
                            ->options(PaymentMethod::class)
                            ->required()
                            ->native(false)
                            ->columnSpan(1),
                        Select::make('status')
                            ->label('สถานะเอกสาร')
                            ->options(OrderStatus::class)
                            ->default('draft')
                            ->required()
                            ->native(false)
                            ->visibleOn('edit')
                            ->columnSpan(1),
                        Select::make('payment_status')
                            ->label('สถานะการชำระเงิน')
                            ->options(PaymentStatus::class)
                            ->default('unpaid')
                            ->required()
                            ->native(false)
                            ->columnSpan(1),
                        Textarea::make('notes')
                            ->label('หมายเหตุ')
                            ->rows(3)
                            ->placeholder('บันทึกเพิ่มเติมเกี่ยวกับใบสั่งขายนี้')
                            ->columnSpanFull(),
                    ])
                    ->columns(2)
                    ->columnSpanFull(),
            ]);
    }

    private static function updateItemTotal(callable $set, callable $get): void
    {
        $quantity = (float) ($get('quantity') ?: 0);
        $unit_price = (float) ($get('unit_price') ?: 0);
        $discount = (float) ($get('discount') ?: 0);
        $total = ($quantity * $unit_price) - $discount;
        $set('total_price', max(0, $total));
    }

    public static function updateGrandTotal(callable $set, callable $get): void
    {
        $items = $get('items') ?? [];
        $subtotal = 0;

        foreach ($items as $item) {
            $subtotal += (float) ($item['total_price'] ?? 0);
        }

        $set('subtotal', $subtotal);

        $discount_amount = (float) ($get('discount_amount') ?: 0);
        $afterDiscount = max(0, $subtotal - $discount_amount);

        $isVatIncluded = $get('is_vat_included') ?? true;

        if ($isVatIncluded) {
            $vat_amount = $afterDiscount * 0.07;
        } else {
            $vat_amount = 0;
        }

        $set('vat_amount', round($vat_amount, 2));
        $set('total_amount', round($afterDiscount + $vat_amount, 2));
    }
}
