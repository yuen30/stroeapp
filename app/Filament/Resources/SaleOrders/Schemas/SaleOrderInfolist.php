<?php

namespace App\Filament\Resources\SaleOrders\Schemas;

use Filament\Infolists\Components\Grid;
use Filament\Infolists\Components\Group;
use Filament\Infolists\Components\RepeatableEntry;
use Filament\Infolists\Components\Section;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Schema;

class SaleOrderInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Group::make()->schema([
                    Section::make('ข้อมูลลูกค้าและเอกสาร')
                        ->icon('heroicon-o-user-group')
                        ->columns(2)
                        ->schema([
                            TextEntry::make('customer.name')
                                ->label('ลูกค้า')
                                ->weight('bold')
                                ->columnSpan(2),
                            TextEntry::make('company.name')
                                ->label('บริษัท'),
                            TextEntry::make('branch.name')
                                ->label('สาขา'),
                            TextEntry::make('document_type')
                                ->label('ประเภทเอกสาร')
                                ->formatStateUsing(fn ($state) => $state?->getLabel() ?? '-'),
                            TextEntry::make('invoice_number')
                                ->label('เลขที่เอกสาร'),
                            TextEntry::make('order_date')
                                ->label('วันที่สั่งซื้อ')
                                ->date('d/m/Y'),
                            TextEntry::make('due_date')
                                ->label('วันครบกำหนด')
                                ->date('d/m/Y'),
                            TextEntry::make('term_of_payment')
                                ->label('เงื่อนไขการชำระเงิน'),
                            TextEntry::make('salesman.name')
                                ->label('พนักงานขาย')
                                ->columnSpan(2),
                        ]),

                    Section::make('รายการสินค้า')
                        ->icon('heroicon-o-shopping-cart')
                        ->schema([
                            RepeatableEntry::make('items')
                                ->label('รายการ')
                                ->columns(6)
                                ->schema([
                                    TextEntry::make('product.name')
                                        ->label('สินค้า')
                                        ->columnSpan(2),
                                    TextEntry::make('description')
                                        ->label('รายละเอียด')
                                        ->columnSpan(2),
                                    TextEntry::make('quantity')
                                        ->label('จำนวน')
                                        ->numeric(),
                                    TextEntry::make('unit_price')
                                        ->label('ราคา/หน่วย')
                                        ->money('thb'),
                                    TextEntry::make('discount')
                                        ->label('ส่วนลด')
                                        ->money('thb'),
                                    TextEntry::make('total_price')
                                        ->label('รวมเงิน')
                                        ->money('thb'),
                                ]),
                        ]),
                ])->columnSpan(['lg' => 2]),

                Group::make()->schema([
                    Section::make('สรุปยอดเงิน')
                        ->icon('heroicon-o-calculator')
                        ->schema([
                            TextEntry::make('subtotal')
                                ->label('มูลค่าสินค้า (Subtotal)')
                                ->money('thb'),
                            TextEntry::make('discount_amount')
                                ->label('ส่วนลดท้ายบิล')
                                ->money('thb'),
                            TextEntry::make('vat_amount')
                                ->label('ภาษีมูลค่าเพิ่ม (VAT)')
                                ->money('thb'),
                            TextEntry::make('total_amount')
                                ->label('ยอดสุทธิ (Total)')
                                ->money('thb')
                                ->size('lg')
                                ->weight('bold')
                                ->color('primary'),
                        ]),

                    Section::make('สถานะเอกสาร')
                        ->schema([
                            TextEntry::make('status')
                                ->label('สถานะ')
                                ->badge(),
                            TextEntry::make('payment_status')
                                ->label('สถานะการชำระเงิน')
                                ->badge(),
                            TextEntry::make('payment_method')
                                ->label('ช่องทางการชำระเงิน')
                                ->formatStateUsing(fn ($state) => $state?->getLabel() ?? '-'),
                            TextEntry::make('notes')
                                ->label('หมายเหตุ')
                                ->columnSpanFull(),
                        ]),
                ])->columnSpan(['lg' => 1]),
            ])
            ->columns(3);
    }
}
