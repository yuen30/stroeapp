<?php

namespace App\Filament\Resources\SaleOrders\Schemas;

use App\Enums\DocumentType;
use App\Enums\OrderStatus;
use App\Models\Branch;
use App\Models\Company;
use App\Models\Customer;
use App\Models\Product;
use Filament\Forms\Components\ColorPicker;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Callout;
use Filament\Schemas\Components\Flex;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\HtmlString;

class SaleOrderForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Callout::make('คำเตือน')
                    ->description('การแก้ไขใบสั่งขายจะส่งผลต่อสต็อกสินค้าและรายการเอกสารที่เกี่ยวข้อง')
                    ->warning()
                    ->icon(Heroicon::ExclamationTriangle)
                    ->visible(fn($context) => $context === 'edit')
                    ->columnSpanFull(),
                // Layout หลัก: Flex แบบ 2 คอลัมน์
                Flex::make([
                    // คอลัมน์ซ้าย: ข้อมูลหลัก
                    Grid::make(1)
                        ->schema([
                            // ข้อมูลทั่วไป
                            Section::make('ข้อมูลทั่วไป')
                                ->description('ข้อมูลพื้นฐานของใบสั่งขาย')
                                ->icon(Heroicon::DocumentText)
                                ->columns(2)
                                ->schema([
                                    DatePicker::make('order_date')
                                        ->label('วันที่ขาย')
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
                                        ->minDate(now())
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
                                                $customer = Customer::find($state);
                                                if ($customer) {
                                                    if ($customer->credit_days > 0) {
                                                        $set('term_of_payment', 'เครดิต ' . $customer->credit_days . ' วัน');
                                                        $set('due_date', now()->addDays($customer->credit_days)->format('Y-m-d'));
                                                    } else {
                                                        $set('term_of_payment', 'เงินสด');
                                                        $set('due_date', null);
                                                    }
                                                }
                                            }
                                        })
                                        // ->createOptionForm([
                                        //     TextInput::make('name')->label('ชื่อลูกค้า')->required()->maxLength(255),
                                        //     TextInput::make('code')->label('รหัสลูกค้า')->maxLength(50),
                                        //     TextInput::make('tax_id')->label('เลขประจำตัวผู้เสียภาษี')->maxLength(13),
                                        //     TextInput::make('tel')->label('เบอร์โทรศัพท์')->tel()->maxLength(20),
                                        //     Textarea::make('address_0')->label('ที่อยู่')->rows(2)->columnSpanFull(),
                                        // ])
                                        // ->createOptionModalHeading('เพิ่มลูกค้าใหม่')
                                        ->columnSpan(2),
                                    Select::make('salesman_id')
                                        ->label('พนักงานขาย')
                                        ->relationship('salesman', 'name')
                                        ->searchable()
                                        ->preload()
                                        ->native(false)
                                        ->placeholder('เลือกพนักงานขาย')
                                        ->default(fn() => Auth::user()?->id)
                                        ->columnSpan(1),
                                    TextInput::make('reference_number')
                                        ->label('เลขที่อ้างอิง')
                                        ->placeholder('PO ของลูกค้า')
                                        ->maxLength(100)
                                        ->columnSpan(1),
                                    Select::make('company_id')
                                        ->label('บริษัท')
                                        ->relationship('company', 'name')
                                        ->required()
                                        ->searchable()
                                        ->preload()
                                        ->native(false)
                                        ->placeholder('เลือกบริษัท')
                                        ->default(fn() => Company::first()?->id)
                                        ->reactive()
                                        ->columnSpan(1),
                                    Select::make('branch_id')
                                        ->label('สาขา')
                                        ->relationship('branch', 'name', fn($query, $get) => $get('company_id') ? $query->where('company_id', $get('company_id')) : $query)
                                        ->searchable()
                                        ->preload()
                                        ->native(false)
                                        ->placeholder('เลือกสาขา')
                                        ->default(fn() => Branch::where('is_headquarter', true)->first()?->id)
                                        ->columnSpan(1),
                                ]),
                            // // รายการสินค้า
                            // Section::make('รายการสินค้า')
                            //     ->description('เพิ่มรายการสินค้าลงในใบสั่งขาย')
                            //     ->icon(Heroicon::ShoppingCart)
                            //     ->collapsible()
                            //     ->collapsed(false)
                            //     ->schema([
                            //         Repeater::make('items')
                            //             ->relationship()
                            //             ->hiddenLabel()
                            //             ->columns(6)
                            //             ->schema([
                            //                 Select::make('product_id')
                            //                     ->label('สินค้า')
                            //                     ->relationship('product', 'name')
                            //                     ->searchable()
                            //                     ->preload()
                            //                     ->required()
                            //                     ->native(false)
                            //                     ->reactive()
                            //                     ->afterStateUpdated(function ($state, callable $set, callable $get) {
                            //                         $product = Product::find($state);
                            //                         if ($product) {
                            //                             $set('unit_price', $product->price);
                            //                             $qty = $get('quantity') ?: 1;
                            //                             $set('quantity', $qty);
                            //                             $discount = $get('discount') ?: 0;
                            //                             $total = ($qty * $product->price) - $discount;
                            //                             $set('total_price', max(0, $total));
                            //                         }
                            //                     })
                            //                     ->columnSpan(2),
                            //                 TextInput::make('description')
                            //                     ->label('รายละเอียดเพิ่มเติม')
                            //                     ->columnSpan(4),
                            //                 TextInput::make('quantity')
                            //                     ->label('จำนวน')
                            //                     ->numeric()
                            //                     ->inputMode('decimal')
                            //                     ->default(1)
                            //                     ->required()
                            //                     ->minValue(1)
                            //                     ->reactive()
                            //                     ->afterStateUpdated(fn ($state, callable $set, callable $get) => self::updateItemTotal($set, $get))
                            //                     ->columnSpan(1),
                            //                 TextInput::make('unit_price')
                            //                     ->label('ราคาต่อหน่วย')
                            //                     ->numeric()
                            //                     ->inputMode('decimal')
                            //                     ->default(0)
                            //                     ->required()
                            //                     ->reactive()
                            //                     ->afterStateUpdated(fn ($state, callable $set, callable $get) => self::updateItemTotal($set, $get))
                            //                     ->columnSpan(2),
                            //                 TextInput::make('discount')
                            //                     ->label('ส่วนลด')
                            //                     ->numeric()
                            //                     ->inputMode('decimal')
                            //                     ->default(0)
                            //                     ->reactive()
                            //                     ->afterStateUpdated(fn ($state, callable $set, callable $get) => self::updateItemTotal($set, $get))
                            //                     ->columnSpan(1),
                            //                 TextInput::make('total_price')
                            //                     ->label('รวมเงิน')
                            //                     ->numeric()
                            //                     ->required()
                            //                     ->disabled()
                            //                     ->dehydrated()
                            //                     ->columnSpan(2),
                            //             ])
                            //             ->reactive()
                            //             ->afterStateUpdated(fn ($state, callable $set, callable $get) => self::updateGrandTotal($set, $get))
                            //             ->itemLabel(fn (array $state): ?string => $state['product_id'] ?? null ? Product::find($state['product_id'])?->name : null)
                            //             ->addActionLabel('เพิ่มรายการสินค้า')
                            //             ->defaultItems(1),
                            //     ])
                            //     ->columnSpanFull(),
                            // ข้อมูลเพิ่มเติม
                            Section::make('ข้อมูลเพิ่มเติม')
                                ->description('ข้อมูลการจัดส่งและผู้ติดต่อ')
                                ->icon(Heroicon::InformationCircle)
                                ->collapsible()
                                ->collapsed()
                                ->columns(2)
                                ->schema([
                                    DatePicker::make('delivery_date')
                                        ->label('วันที่จัดส่ง')
                                        ->native(false)
                                        ->displayFormat('d/m/Y')
                                        ->minDate(now())
                                        ->columnSpan(1),
                                    TextInput::make('shipping_method')
                                        ->label('วิธีการจัดส่ง')
                                        ->placeholder('รถส่งของบริษัท, Kerry, Flash')
                                        ->maxLength(100)
                                        ->columnSpan(1),
                                    Textarea::make('shipping_address')
                                        ->label('ที่อยู่จัดส่ง')
                                        ->rows(2)
                                        ->placeholder('ถ้าไม่ระบุจะใช้ที่อยู่ลูกค้า')
                                        ->columnSpanFull(),
                                    TextInput::make('contact_person')
                                        ->label('ชื่อผู้ติดต่อ')
                                        ->maxLength(255)
                                        ->columnSpan(1),
                                    TextInput::make('contact_phone')
                                        ->label('เบอร์โทรติดต่อ')
                                        ->tel()
                                        ->maxLength(20)
                                        ->columnSpan(1),
                                ]),
                            // ไฟล์แนบ
                            Section::make('ไฟล์แนบ')
                                ->description('แนบเอกสารที่เกี่ยวข้อง')
                                ->icon(Heroicon::PaperClip)
                                ->collapsible()
                                ->collapsed()
                                ->schema([
                                    FileUpload::make('attachments')
                                        ->label('ไฟล์แนบ')
                                        ->multiple()
                                        ->directory('sale-orders')
                                        ->acceptedFileTypes(['application/pdf', 'image/*', 'application/msword', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document'])
                                        ->maxSize(5120)
                                        ->downloadable()
                                        ->openable()
                                        ->previewable()
                                        ->reorderable()
                                        ->columnSpanFull(),
                                ]),
                            // หมายเหตุ
                            Section::make('หมายเหตุ')
                                ->icon(Heroicon::ChatBubbleBottomCenterText)
                                ->collapsible()
                                ->collapsed()
                                ->schema([
                                    Textarea::make('notes')
                                        ->label('หมายเหตุ')
                                        ->rows(3)
                                        ->placeholder('บันทึกเพิ่มเติม')
                                        ->columnSpanFull(),
                                ]),
                        ]),
                    // คอลัมน์ขวา: Sidebar (ข้อมูลสรุป)
                    Grid::make(1)
                        ->schema([
                            // ข้อมูลวงเงินเครดิต
                            Section::make('วงเงินเครดิต')
                                ->icon(Heroicon::CreditCard)
                                ->visible(fn(callable $get) => $get('customer_id') !== null)
                                ->schema([
                                    Placeholder::make('credit_info')
                                        ->hiddenLabel()
                                        ->content(function (callable $get) {
                                            $customerId = $get('customer_id');
                                            if (!$customerId) {
                                                return 'กรุณาเลือกลูกค้า';
                                            }

                                            $customer = Customer::find($customerId);
                                            if (!$customer) {
                                                return '-';
                                            }

                                            if ($customer->credit_limit <= 0) {
                                                return new HtmlString('
                                                    <div class="flex items-center gap-2 text-success-600 dark:text-success-400">
                                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                                        </svg>
                                                        <span class="font-semibold">ลูกค้าเงินสด</span>
                                                    </div>
                                                ');
                                            }

                                            $outstanding = $customer->getTotalOutstandingAmount();
                                            $remaining = $customer->getRemainingCreditLimit();
                                            $percentage = $customer->getCreditUsagePercentage();
                                            $statusColor = $percentage >= 90 ? 'danger' : ($percentage >= 70 ? 'warning' : 'success');

                                            return new HtmlString('
                                                <div class="space-y-3">
                                                    <div class="grid grid-cols-2 gap-2 text-sm">
                                                        <div>
                                                            <div class="text-gray-500 dark:text-gray-400">วงเงินทั้งหมด</div>
                                                            <div class="font-semibold">' . number_format($customer->credit_limit, 2) . ' ฿</div>
                                                        </div>
                                                        <div>
                                                            <div class="text-gray-500 dark:text-gray-400">ยอดค้างชำระ</div>
                                                            <div class="font-semibold">' . number_format($outstanding, 2) . ' ฿</div>
                                                        </div>
                                                    </div>
                                                    <div>
                                                        <div class="flex justify-between items-center mb-1">
                                                            <span class="text-sm text-gray-500 dark:text-gray-400">วงเงินคงเหลือ</span>
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
                                        }),
                                ]),
                            // // สรุปยอดเงิน
                            // Section::make('สรุปยอดเงิน')
                            //     ->icon(Heroicon::Calculator)
                            //     ->collapsible()
                            //     ->collapsed(false)
                            //     ->schema([
                            //         TextInput::make('subtotal')
                            //             ->label('มูลค่าสินค้า')
                            //             ->numeric()
                            //             ->inputMode('decimal')
                            //             ->default(0)
                            //             ->prefix('฿')
                            //             ->reactive()
                            //             ->afterStateUpdated(fn ($state, callable $set, callable $get) => self::calculateTotal($set, $get))
                            //             ->extraInputAttributes(['class' => 'text-right']),
                            //         TextInput::make('discount_amount')
                            //             ->label('ส่วนลดท้ายบิล')
                            //             ->numeric()
                            //             ->inputMode('decimal')
                            //             ->default(0)
                            //             ->prefix('฿')
                            //             ->reactive()
                            //             ->afterStateUpdated(fn ($state, callable $set, callable $get) => self::calculateTotal($set, $get))
                            //             ->extraInputAttributes(['class' => 'text-right']),
                            //         TextInput::make('vat_amount')
                            //             ->label('ภาษีมูลค่าเพิ่ม (VAT)')
                            //             ->numeric()
                            //             ->inputMode('decimal')
                            //             ->default(0)
                            //             ->prefix('฿')
                            //             ->helperText('กรอก 0 ถ้าไม่มี VAT หรือกรอก % VAT ที่ต้องการ')
                            //             ->reactive()
                            //             ->afterStateUpdated(fn ($state, callable $set, callable $get) => self::calculateTotal($set, $get))
                            //             ->extraInputAttributes(['class' => 'text-right']),
                            //         TextInput::make('total_amount')
                            //             ->label('ยอดสุทธิ')
                            //             ->numeric()
                            //             ->inputMode('decimal')
                            //             ->default(0)
                            //             ->prefix('฿')
                            //             ->extraInputAttributes(['class' => 'text-right text-2xl font-bold text-primary-600 dark:text-primary-400'])
                            //             ->extraAttributes(['style' => 'margin-top: 1rem; padding-top: 1rem; border-top: 2px solid rgb(229 231 235 / 1);']),
                            //     ]),
                            // สถานะและการชำระเงิน
                            Section::make('สถานะ')
                                ->icon(Heroicon::DocumentCheck)
                                ->schema([
                                    TextInput::make('invoice_number')
                                        ->label('เลขที่เอกสาร')
                                        ->placeholder('สร้างอัตโนมัติ')
                                        ->maxLength(50)
                                        ->unique(ignoreRecord: true)
                                        ->visibleOn('create'),
                                    TextInput::make('invoice_number')
                                        ->label('เลขที่เอกสาร')
                                        ->disabled()
                                        ->visibleOn(['edit', 'view']),
                                    Select::make('document_type')
                                        ->label('ประเภทเอกสาร')
                                        ->options(DocumentType::class)
                                        ->default('tax_invoice')
                                        ->required()
                                        ->native(false),
                                    TextInput::make('term_of_payment')
                                        ->label('เงื่อนไขชำระเงิน')
                                        ->placeholder('เช่น เครดิต 30 วัน')
                                        ->maxLength(255),
                                    Select::make('payment_method_id')
                                        ->label('ช่องทางชำระเงิน')
                                        ->relationship('paymentMethod', 'name')
                                        ->searchable()
                                        ->required()
                                        ->preload()
                                        ->native(false)
                                        ->placeholder('เลือกช่องทางชำระเงิน')
                                        ->createOptionForm([
                                            TextInput::make('name')
                                                ->label('ชื่อวิธีการชำระเงิน')
                                                ->required()
                                                ->maxLength(255),
                                            TextInput::make('code')
                                                ->label('รหัส')
                                                ->maxLength(50)
                                                ->placeholder('ไม่ระบุ = สร้างอัตโนมัติ'),
                                            Textarea::make('description')
                                                ->label('รายละเอียด')
                                                ->rows(2)
                                                ->columnSpanFull(),
                                        ])
                                        ->createOptionModalHeading('เพิ่มวิธีการชำระเงินใหม่'),
                                    Select::make('status')
                                        ->label('สถานะเอกสาร')
                                        ->options(OrderStatus::class)
                                        ->default('draft')
                                        ->required()
                                        ->native(false)
                                        ->visibleOn('edit'),
                                    Select::make('payment_status_id')
                                        ->label('สถานะการชำระเงิน')
                                        ->relationship('paymentStatus', 'name')
                                        ->searchable()
                                        ->preload()
                                        ->required()
                                        ->native(false)
                                        ->placeholder('เลือกสถานะการชำระเงิน')
                                        ->createOptionForm([
                                            TextInput::make('name')
                                                ->label('ชื่อสถานะ')
                                                ->required()
                                                ->maxLength(255),
                                            TextInput::make('code')
                                                ->label('รหัส')
                                                ->maxLength(50)
                                                ->placeholder('ไม่ระบุ = สร้างอัตโนมัติ'),
                                            ColorPicker::make('color')
                                                ->label('สี')
                                                ->placeholder('#FF0000'),
                                            Textarea::make('description')
                                                ->label('รายละเอียด')
                                                ->rows(2)
                                                ->columnSpanFull(),
                                        ])
                                        ->createOptionModalHeading('เพิ่มสถานะการชำระเงินใหม่'),
                                ]),
                        ])
                        ->grow(false),  // Sidebar ไม่ขยาย
                ])
                    ->from('lg')  // แยกเป็น 2 คอลัมน์ตั้งแต่ lg breakpoint
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

    private static function calculateTotal(callable $set, callable $get): void
    {
        $subtotal = (float) ($get('subtotal') ?: 0);
        $discount_amount = (float) ($get('discount_amount') ?: 0);
        $vat_amount = (float) ($get('vat_amount') ?: 0);

        $total = $subtotal - $discount_amount + $vat_amount;
        $set('total_amount', max(0, $total));
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
