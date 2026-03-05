<?php

namespace App\Filament\Resources\SaleOrders\Schemas;

use App\Enums\DocumentType;
use App\Enums\OrderStatus;
use App\Enums\PaymentMethod;
use App\Enums\PaymentStatus;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Schemas\Schema;

class SaleOrderForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                \Filament\Schemas\Components\Callout::make('คำเตือน')
                    ->description('การแก้ไขใบสั่งขายจะส่งผลต่อสต็อกสินค้าและรายการเอกสารที่เกี่ยวข้อง')
                    ->warning()
                    ->icon(\Filament\Support\Icons\Heroicon::ExclamationTriangle)
                    ->visible(fn($context) => $context === 'edit')
                    ->columnSpanFull(),

                \Filament\Schemas\Components\Section::make('ข้อมูลทั่วไป')
                    ->description('ข้อมูลพื้นฐานของใบสั่งขาย')
                    ->icon(\Filament\Support\Icons\Heroicon::DocumentText)
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
                        Select::make('salesman_id')
                            ->label('พนักงานขาย')
                            ->relationship('salesman', 'name')
                            ->searchable()
                            ->preload()
                            ->native(false)
                            ->placeholder('เลือกพนักงานขาย')
                            ->default(fn() => auth()->user()?->id)
                            ->columnSpan(2),
                    ])
                    ->columns(2)
                    ->columnSpanFull(),

                \Filament\Schemas\Components\Section::make('รายการสินค้า')
                    ->description('เพิ่มรายการสินค้าลงในใบสั่งขาย กำหนดจำนวนและราคา')
                    ->icon(\Filament\Support\Icons\Heroicon::ShoppingCart)
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
                    ->columnSpanFull(),

                \Filament\Schemas\Components\Section::make('สรุปยอดเงิน')
                    ->description('ยอดยกมา หักส่วนลด และคำนวณภาษี')
                    ->icon(\Filament\Support\Icons\Heroicon::Calculator)
                    ->collapsible()
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
                    ->icon(\Filament\Support\Icons\Heroicon::InformationCircle)
                    ->collapsible()
                    ->schema([
                        TextInput::make('invoice_number')
                            ->label('เลขที่เอกสาร')
                            ->placeholder('ระบบจะสร้างอัตโนมัติ')
                            ->disabled()
                            ->dehydrated(false)
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
                            ->native(false)
                            ->columnSpan(1),
                        Select::make('status')
                            ->label('สถานะเอกสาร')
                            ->options(OrderStatus::class)
                            ->default('draft')
                            ->required()
                            ->native(false)
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
