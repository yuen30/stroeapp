<?php

namespace App\Filament\Resources\PurchaseOrders\Pages;

use App\Filament\Resources\PurchaseOrders\Schemas\PurchaseOrderInfolist;
use App\Filament\Resources\PurchaseOrders\PurchaseOrderResource;
use Filament\Resources\Pages\ViewRecord;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Actions;

class ViewPurchaseOrder extends ViewRecord
{
    protected static string $resource = PurchaseOrderResource::class;

    public function infolist(Schema $schema): Schema
    {
        return PurchaseOrderInfolist::configure($schema);
    }

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('addItem')
                ->label('เพิ่มสินค้า')
                ->icon(Heroicon::Plus)
                ->color('success')
                ->visible(fn(): bool => $this->record->status->value === 'draft')
                ->modalHeading('เพิ่มสินค้าในใบสั่งซื้อ')
                ->modalWidth('4xl')
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
                                ->reactive()
                                ->afterStateUpdated(function ($state, callable $set) {
                                    if ($state) {
                                        $product = \App\Models\Product::find($state);
                                        if ($product) {
                                            $set('unit_price', $product->cost_price);
                                            $set('description', $product->description);
                                            $set('quantity', 1);
                                            $set('discount', 0);
                                        }
                                    }
                                })
                                ->columnSpanFull(),
                            \Filament\Forms\Components\Textarea::make('description')
                                ->label('รายละเอียด')
                                ->rows(2)
                                ->placeholder('รายละเอียดเพิ่มเติม')
                                ->columnSpanFull()
                                ->columnStart(1),
                            \Filament\Forms\Components\TextInput::make('quantity')
                                ->label('จำนวน')
                                ->required()
                                ->numeric()
                                ->default(1)
                                ->minValue(1)
                                ->suffix('หน่วย')
                                ->reactive()
                                ->afterStateUpdated(function ($state, callable $set, callable $get) {
                                    $unitPrice = $get('unit_price') ?? 0;
                                    $discount = $get('discount') ?? 0;
                                    $total = ($unitPrice * $state) - $discount;
                                    $set('_total', $total);
                                }),
                            \Filament\Forms\Components\TextInput::make('unit_price')
                                ->label('ราคาซื้อต่อหน่วย')
                                ->required()
                                ->numeric()
                                ->prefix('฿')
                                ->reactive()
                                ->afterStateUpdated(function ($state, callable $set, callable $get) {
                                    $quantity = $get('quantity') ?? 0;
                                    $discount = $get('discount') ?? 0;
                                    $total = ($state * $quantity) - $discount;
                                    $set('_total', $total);
                                }),
                            \Filament\Forms\Components\TextInput::make('discount')
                                ->label('ส่วนลด')
                                ->numeric()
                                ->default(0)
                                ->prefix('฿')
                                ->reactive()
                                ->afterStateUpdated(function ($state, callable $set, callable $get) {
                                    $unitPrice = $get('unit_price') ?? 0;
                                    $quantity = $get('quantity') ?? 0;
                                    $total = ($unitPrice * $quantity) - $state;
                                    $set('_total', $total);
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
                                ->columnSpan(1),
                        ]),
                ])
                ->action(function (array $data) {
                    $totalPrice = ($data['unit_price'] * $data['quantity']) - ($data['discount'] ?? 0);

                    // ตรวจสอบว่ามีสินค้านี้อยู่แล้วหรือไม่
                    $existingItem = $this
                        ->record
                        ->items()
                        ->where('product_id', $data['product_id'])
                        ->first();

                    if ($existingItem) {
                        // ถ้ามีอยู่แล้ว ให้อัพเดทจำนวนและราคา
                        $existingItem->update([
                            'description' => $data['description'] ?? $existingItem->description,
                            'quantity' => $data['quantity'],
                            'unit_price' => $data['unit_price'],
                            'discount' => $data['discount'] ?? 0,
                            'total_price' => $totalPrice,
                        ]);

                        $message = 'อัพเดทสินค้าสำเร็จ';
                    } else {
                        // ถ้ายังไม่มี ให้สร้างใหม่
                        $this->record->items()->create([
                            'product_id' => $data['product_id'],
                            'description' => $data['description'] ?? null,
                            'quantity' => $data['quantity'],
                            'unit_price' => $data['unit_price'],
                            'discount' => $data['discount'] ?? 0,
                            'total_price' => $totalPrice,
                        ]);

                        $message = 'เพิ่มสินค้าสำเร็จ';
                    }

                    // Refresh the record to get updated totals
                    $this->record->refresh();

                    \Filament\Notifications\Notification::make()
                        ->success()
                        ->title($message)
                        ->send();
                }),
            Actions\Action::make('confirm')
                ->label('ยืนยันใบสั่งซื้อ')
                ->icon(Heroicon::CheckCircle)
                ->color('info')
                ->visible(fn(): bool => $this->record->status->value === 'draft')
                ->requiresConfirmation()
                ->modalHeading('ยืนยันใบสั่งซื้อ')
                ->modalDescription('คุณแน่ใจหรือไม่ว่าต้องการยืนยันใบสั่งซื้อนี้? หลังจากยืนยันแล้วจะไม่สามารถแก้ไขรายการสินค้าได้')
                ->action(function () {
                    // ตรวจสอบว่ามีสินค้าหรือไม่
                    if ($this->record->items()->count() === 0) {
                        \Filament\Notifications\Notification::make()
                            ->warning()
                            ->title('ไม่สามารถยืนยันได้')
                            ->body('กรุณาเพิ่มสินค้าอย่างน้อย 1 รายการก่อนยืนยันใบสั่งซื้อ')
                            ->send();

                        return;
                    }

                    $this->record->update([
                        'status' => \App\Enums\OrderStatus::Confirmed,
                    ]);

                    \Filament\Notifications\Notification::make()
                        ->success()
                        ->title('ยืนยันใบสั่งซื้อสำเร็จ')
                        ->body('ใบสั่งซื้อเลขที่ ' . $this->record->order_number . ' ได้รับการยืนยันแล้ว')
                        ->send();
                }),
            Actions\Action::make('createGoodsReceipt')
                ->label('สร้างใบรับสินค้า')
                ->icon(Heroicon::ArrowDownTray)
                ->color('success')
                ->visible(fn(): bool => in_array($this->record->status->value, ['confirmed', 'partially_received']))
                ->modalHeading('สร้างใบรับสินค้า')
                ->modalWidth('3xl')
                ->schema([
                    \Filament\Schemas\Components\Grid::make(2)
                        ->schema([
                            \Filament\Forms\Components\DatePicker::make('document_date')
                                ->label('วันที่รับสินค้า')
                                ->required()
                                ->native(false)
                                ->displayFormat('d/m/Y')
                                ->default(now())
                                ->columnSpan(1),
                            \Filament\Forms\Components\TextInput::make('supplier_delivery_no')
                                ->label('เลขที่ใบส่งของผู้จำหน่าย')
                                ->maxLength(255)
                                ->columnSpan(1),
                            \Filament\Forms\Components\Textarea::make('notes')
                                ->label('หมายเหตุ')
                                ->rows(3)
                                ->columnSpanFull(),
                        ]),
                ])
                ->action(function (array $data) {
                    // สร้างใบรับสินค้า
                    $goodsReceipt = \App\Models\GoodsReceipt::create([
                        'purchase_order_id' => $this->record->id,
                        'company_id' => $this->record->company_id,
                        'branch_id' => $this->record->branch_id,
                        'supplier_id' => $this->record->supplier_id,
                        'document_date' => $data['document_date'],
                        'supplier_delivery_no' => $data['supplier_delivery_no'] ? strtoupper($data['supplier_delivery_no']) : null,
                        'notes' => $data['notes'] ?? null,
                        'status' => \App\Enums\OrderStatus::Draft,
                        'created_by' => auth()->id(),
                    ]);

                    \Filament\Notifications\Notification::make()
                        ->success()
                        ->title('สร้างใบรับสินค้าสำเร็จ')
                        ->body('ใบรับสินค้าเลขที่ ' . $goodsReceipt->receipt_number . ' ถูกสร้างแล้ว')
                        ->send();

                    // Redirect to goods receipt view page
                    return redirect()->to(\App\Filament\Resources\GoodsReceipts\GoodsReceiptResource::getUrl('view', ['record' => $goodsReceipt]));
                }),
            Actions\Action::make('printPdf')
                ->label('พิมพ์/PDF')
                ->icon(Heroicon::Printer)
                ->color('gray')
                ->visible(fn(): bool => in_array($this->record->status->value, ['confirmed', 'partially_received', 'completed']))
                ->action(function () {
                    return response()->streamDownload(function () {
                        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('pdf.purchase-order', [
                            'purchaseOrder' => $this->record->load(['company', 'branch', 'supplier', 'items.product.unit', 'creator']),
                        ]);
                        echo $pdf->stream();
                    }, 'PO-' . $this->record->order_number . '.pdf');
                }),
            Actions\Action::make('complete')
                ->label('เสร็จสิ้น')
                ->icon(Heroicon::CheckBadge)
                ->color('success')
                ->visible(fn(): bool => in_array($this->record->status->value, ['confirmed', 'partially_received']))
                ->requiresConfirmation()
                ->modalHeading('ทำเครื่องหมายเสร็จสิ้น')
                ->modalDescription('คุณแน่ใจหรือไม่ว่าต้องการทำเครื่องหมายใบสั่งซื้อนี้เป็นเสร็จสิ้น?')
                ->action(function () {
                    $this->record->update([
                        'status' => \App\Enums\OrderStatus::Completed,
                    ]);

                    \Filament\Notifications\Notification::make()
                        ->success()
                        ->title('ทำเครื่องหมายเสร็จสิ้นสำเร็จ')
                        ->send();
                }),
            Actions\Action::make('cancel')
                ->label('ยกเลิก')
                ->icon(Heroicon::XCircle)
                ->color('danger')
                ->visible(fn(): bool => in_array($this->record->status->value, ['draft', 'confirmed', 'partially_received']))
                ->requiresConfirmation()
                ->modalHeading('ยกเลิกใบสั่งซื้อ')
                ->modalDescription('คุณแน่ใจหรือไม่ว่าต้องการยกเลิกใบสั่งซื้อนี้?')
                ->action(function () {
                    $this->record->update([
                        'status' => \App\Enums\OrderStatus::Cancelled,
                    ]);

                    \Filament\Notifications\Notification::make()
                        ->success()
                        ->title('ยกเลิกใบสั่งซื้อสำเร็จ')
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
}
