<?php

namespace App\Filament\Resources\GoodsReceipts\Schemas;

use App\Enums\OrderStatus;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Callout;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;

class GoodsReceiptForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(4)
            ->components([
                Section::make('ข้อมูลทั่วไป')
                    ->description('ข้อมูลพื้นฐานของใบรับสินค้า')
                    ->icon(Heroicon::DocumentText)
                    ->collapsible()
                    ->columnSpanFull()
                    ->columns(4)
                    ->schema([
                        Select::make('purchase_order_id')
                            ->label('ใบสั่งซื้ออ้างอิง')
                            ->relationship(
                                'purchaseOrder',
                                'order_number',
                                fn($query) => $query->whereIn('status', ['confirmed', 'partially_received'])
                            )
                            ->searchable()
                            ->preload()
                            ->required()
                            ->reactive()
                            ->afterStateUpdated(function ($state, callable $set) {
                                if ($state) {
                                    $po = \App\Models\PurchaseOrder::with(['company', 'branch', 'supplier'])->find($state);
                                    if ($po) {
                                        $set('company_id', $po->company_id);
                                        $set('branch_id', $po->branch_id);
                                        $set('supplier_id', $po->supplier_id);
                                    }
                                }
                            })
                            ->columnSpan(2),
                        DatePicker::make('document_date')
                            ->label('วันที่รับสินค้า')
                            ->required()
                            ->native(false)
                            ->displayFormat('d/m/Y')
                            ->default(now())
                            ->columnSpan(2),
                        Select::make('company_id')
                            ->label('บริษัท')
                            ->relationship('company', 'name')
                            ->required()
                            ->default(fn() => auth()->user()->company_id)
                            ->disabled()
                            ->dehydrated()
                            ->columnSpan(2),
                        Select::make('branch_id')
                            ->label('สาขา')
                            ->relationship('branch', 'name')
                            ->default(fn() => auth()->user()->branch_id)
                            ->disabled()
                            ->dehydrated()
                            ->columnSpan(2),
                        Select::make('supplier_id')
                            ->label('ผู้จำหน่าย')
                            ->relationship('supplier', 'name')
                            ->required()
                            ->disabled()
                            ->dehydrated()
                            ->columnSpan(2),
                        TextInput::make('supplier_delivery_no')
                            ->label('เลขที่ใบส่งของผู้จำหน่าย')
                            ->maxLength(255)
                            ->dehydrateStateUsing(fn($state) => $state ? strtoupper($state) : $state)
                            ->columnSpan(2),
                    ]),
                Section::make('หมายเหตุและไฟล์แนบ')
                    ->schema([
                        Textarea::make('notes')
                            ->label('หมายเหตุ')
                            ->rows(3)
                            ->columnSpanFull(),
                        FileUpload::make('attachments')
                            ->label('ไฟล์แนบ')
                            ->multiple()
                            ->maxFiles(10)
                            ->maxSize(10240)
                            ->acceptedFileTypes(['application/pdf', 'image/*', 'application/msword', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document', 'application/vnd.ms-excel', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'])
                            ->directory('goods-receipts/attachments')
                            ->visibility('public')
                            ->downloadable()
                            ->openable()
                            ->previewable()
                            ->columnSpanFull(),
                    ])
                    ->collapsible()
                    ->columnSpanFull(),
            ]);
    }
}
