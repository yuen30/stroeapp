<?php

namespace App\Filament\Resources\GoodsReceipts\Pages;

use App\Filament\Resources\GoodsReceipts\Schemas\GoodsReceiptInfolist;
use App\Filament\Resources\GoodsReceipts\GoodsReceiptResource;
use Filament\Resources\Pages\ViewRecord;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Actions;

class ViewGoodsReceipt extends ViewRecord
{
    protected static string $resource = GoodsReceiptResource::class;

    public function infolist(Schema $schema): Schema
    {
        return GoodsReceiptInfolist::configure($schema);
    }

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('addItem')
                ->label('เพิ่มสินค้า')
                ->icon(Heroicon::Plus)
                ->color('success')
                ->visible(fn(): bool => $this->record->status->value === 'draft')
                ->modalHeading('เพิ่มสินค้าในใบรับสินค้า')
                ->modalWidth('4xl')
                ->form([
                    \Filament\Schemas\Components\Grid::make(3)
                        ->schema([
                            \Filament\Forms\Components\Select::make('purchase_order_item_id')
                                ->label('สินค้าจากใบสั่งซื้อ')
                                ->options(function () {
                                    if (!$this->record->purchase_order_id) {
                                        return [];
                                    }

                                    $poItems = \App\Models\PurchaseOrderItem::where('purchase_order_id', $this->record->purchase_order_id)
                                        ->with('product')
                                        ->get();

                                    // คำนวณจำนวนที่รับแล้วจากใบรับสินค้าที่ยืนยันแล้ว
                                    $receivedQuantities = \App\Models\GoodsReceiptItem::whereHas('goodsReceipt', function ($query) {
                                        $query
                                            ->where('purchase_order_id', $this->record->purchase_order_id)
                                            ->where('status', \App\Enums\OrderStatus::Confirmed);
                                    })
                                        ->selectRaw('purchase_order_item_id, SUM(quantity) as total_received')
                                        ->groupBy('purchase_order_item_id')
                                        ->pluck('total_received', 'purchase_order_item_id');

                                    return $poItems->mapWithKeys(function ($item) use ($receivedQuantities) {
                                        $ordered = $item->quantity;
                                        $received = $receivedQuantities[$item->id] ?? 0;
                                        $remaining = $ordered - $received;

                                        // แสดงเฉพาะสินค้าที่ยังรับไม่ครบ
                                        if ($remaining > 0) {
                                            return [$item->id => $item->product->name . " (สั่ง: {$ordered} | รับแล้ว: {$received} | คงเหลือ: {$remaining})"];
                                        }

                                        return [];
                                    })->filter();
                                })
                                ->required()
                                ->searchable()
                                ->preload()
                                ->native(false)
                                ->reactive()
                                ->afterStateUpdated(function ($state, callable $set) {
                                    if ($state) {
                                        $poItem = \App\Models\PurchaseOrderItem::with('product')->find($state);
                                        if ($poItem) {
                                            // คำนวณจำนวนที่รับแล้ว
                                            $receivedQuantity = \App\Models\GoodsReceiptItem::whereHas('goodsReceipt', function ($query) {
                                                $query
                                                    ->where('purchase_order_id', $this->record->purchase_order_id)
                                                    ->where('status', \App\Enums\OrderStatus::Confirmed);
                                            })
                                                ->where('purchase_order_item_id', $state)
                                                ->sum('quantity');

                                            $remaining = $poItem->quantity - $receivedQuantity;

                                            $set('product_id', $poItem->product_id);
                                            $set('description', $poItem->description);
                                            $set('quantity', min($remaining, 1));  // ตั้งค่าเริ่มต้นเป็น 1 หรือจำนวนที่เหลือ
                                            $set('max_quantity', $remaining);  // เก็บจำนวนสูงสุดที่รับได้
                                        }
                                    }
                                })
                                ->columnSpanFull(),
                            \Filament\Forms\Components\Hidden::make('product_id'),
                            \Filament\Forms\Components\Hidden::make('max_quantity'),
                            \Filament\Forms\Components\Textarea::make('description')
                                ->label('รายละเอียด')
                                ->rows(2)
                                ->placeholder('รายละเอียดเพิ่มเติม')
                                ->columnSpanFull()
                                ->columnStart(1),
                            \Filament\Forms\Components\TextInput::make('quantity')
                                ->label('จำนวนที่รับ')
                                ->required()
                                ->numeric()
                                ->default(1)
                                ->minValue(1)
                                ->suffix('หน่วย')
                                ->columnSpan(3)
                                ->rules([
                                    function () {
                                        return function (string $attribute, $value, \Closure $fail) {
                                            $data = $this->mountedActionsData[0] ?? [];
                                            $maxQuantity = $data['max_quantity'] ?? null;

                                            if ($maxQuantity !== null && $value > $maxQuantity) {
                                                $fail("จำนวนที่รับต้องไม่เกิน {$maxQuantity} หน่วย");
                                            }
                                        };
                                    },
                                ]),
                        ]),
                ])
                ->action(function (array $data) {
                    // ตรวจสอบจำนวนที่รับอีกครั้งก่อนบันทึก
                    $poItem = \App\Models\PurchaseOrderItem::find($data['purchase_order_item_id']);
                    if (!$poItem) {
                        \Filament\Notifications\Notification::make()
                            ->danger()
                            ->title('ไม่พบข้อมูลสินค้า')
                            ->send();
                        return;
                    }

                    // คำนวณจำนวนที่รับแล้วจากใบรับสินค้าที่ยืนยันแล้ว
                    $receivedQuantity = \App\Models\GoodsReceiptItem::whereHas('goodsReceipt', function ($query) {
                        $query
                            ->where('purchase_order_id', $this->record->purchase_order_id)
                            ->where('status', \App\Enums\OrderStatus::Confirmed);
                    })
                        ->where('purchase_order_item_id', $data['purchase_order_item_id'])
                        ->sum('quantity');

                    $remaining = $poItem->quantity - $receivedQuantity;

                    if ($data['quantity'] > $remaining) {
                        \Filament\Notifications\Notification::make()
                            ->danger()
                            ->title('ไม่สามารถรับสินค้าได้')
                            ->body("จำนวนที่รับต้องไม่เกิน {$remaining} หน่วย (สั่ง: {$poItem->quantity} | รับแล้ว: {$receivedQuantity})")
                            ->send();
                        return;
                    }

                    // ตรวจสอบว่ามีสินค้านี้อยู่แล้วหรือไม่
                    $existingItem = $this
                        ->record
                        ->items()
                        ->where('purchase_order_item_id', $data['purchase_order_item_id'])
                        ->first();

                    if ($existingItem) {
                        // ถ้ามีอยู่แล้ว ให้อัพเดทจำนวน
                        $existingItem->update([
                            'description' => $data['description'] ?? $existingItem->description,
                            'quantity' => $data['quantity'],
                        ]);

                        $message = 'อัพเดทสินค้าสำเร็จ';
                    } else {
                        // ถ้ายังไม่มี ให้สร้างใหม่
                        $this->record->items()->create([
                            'purchase_order_item_id' => $data['purchase_order_item_id'],
                            'product_id' => $data['product_id'],
                            'description' => $data['description'] ?? null,
                            'quantity' => $data['quantity'],
                        ]);

                        $message = 'เพิ่มสินค้าสำเร็จ';
                    }

                    // Refresh the record
                    $this->record->refresh();

                    \Filament\Notifications\Notification::make()
                        ->success()
                        ->title($message)
                        ->send();
                }),
            Actions\Action::make('addAllItems')
                ->label('รับสินค้าทั้งหมด')
                ->icon(Heroicon::PlusCircle)
                ->color('success')
                ->visible(fn(): bool => $this->record->status->value === 'draft')
                ->requiresConfirmation()
                ->modalHeading('รับสินค้าทั้งหมด')
                ->modalDescription('คุณต้องการเพิ่มสินค้าทั้งหมดจากใบสั่งซื้อที่ยังรับไม่ครบหรือไม่?')
                ->action(function () {
                    if (!$this->record->purchase_order_id) {
                        \Filament\Notifications\Notification::make()
                            ->warning()
                            ->title('ไม่พบข้อมูลใบสั่งซื้อ')
                            ->send();
                        return;
                    }

                    $poItems = \App\Models\PurchaseOrderItem::where('purchase_order_id', $this->record->purchase_order_id)
                        ->with('product')
                        ->get();

                    // คำนวณจำนวนที่รับแล้วจากใบรับสินค้าที่ยืนยันแล้ว
                    $receivedQuantities = \App\Models\GoodsReceiptItem::whereHas('goodsReceipt', function ($query) {
                        $query
                            ->where('purchase_order_id', $this->record->purchase_order_id)
                            ->where('status', \App\Enums\OrderStatus::Confirmed);
                    })
                        ->selectRaw('purchase_order_item_id, SUM(quantity) as total_received')
                        ->groupBy('purchase_order_item_id')
                        ->pluck('total_received', 'purchase_order_item_id');

                    $addedCount = 0;
                    foreach ($poItems as $poItem) {
                        $ordered = $poItem->quantity;
                        $received = $receivedQuantities[$poItem->id] ?? 0;
                        $remaining = $ordered - $received;

                        // เพิ่มเฉพาะสินค้าที่ยังรับไม่ครบ
                        if ($remaining > 0) {
                            // ตรวจสอบว่ามีสินค้านี้ใน draft นี้แล้วหรือไม่
                            $existingItem = $this
                                ->record
                                ->items()
                                ->where('purchase_order_item_id', $poItem->id)
                                ->first();

                            if ($existingItem) {
                                // อัพเดทจำนวน
                                $existingItem->update([
                                    'quantity' => $remaining,
                                ]);
                            } else {
                                // สร้างใหม่
                                $this->record->items()->create([
                                    'purchase_order_item_id' => $poItem->id,
                                    'product_id' => $poItem->product_id,
                                    'description' => $poItem->description,
                                    'quantity' => $remaining,
                                ]);
                            }
                            $addedCount++;
                        }
                    }

                    $this->record->refresh();

                    if ($addedCount > 0) {
                        \Filament\Notifications\Notification::make()
                            ->success()
                            ->title('เพิ่มสินค้าสำเร็จ')
                            ->body("เพิ่มสินค้า {$addedCount} รายการ")
                            ->send();
                    } else {
                        \Filament\Notifications\Notification::make()
                            ->info()
                            ->title('ไม่มีสินค้าที่ต้องรับ')
                            ->body('รับสินค้าครบทุกรายการแล้ว')
                            ->send();
                    }
                }),
            Actions\Action::make('confirm')
                ->label('ยืนยันใบรับสินค้า')
                ->icon(Heroicon::CheckCircle)
                ->color('info')
                ->visible(fn(): bool => $this->record->status->value === 'draft')
                ->requiresConfirmation()
                ->modalHeading('ยืนยันใบรับสินค้า')
                ->modalDescription('คุณแน่ใจหรือไม่ว่าต้องการยืนยันใบรับสินค้านี้? หลังจากยืนยันแล้วจะไม่สามารถแก้ไขรายการสินค้าได้และระบบจะอัพเดทสต็อกสินค้า')
                ->action(function () {
                    // ตรวจสอบว่ามีสินค้าหรือไม่
                    if ($this->record->items()->count() === 0) {
                        \Filament\Notifications\Notification::make()
                            ->warning()
                            ->title('ไม่สามารถยืนยันได้')
                            ->body('กรุณาเพิ่มสินค้าอย่างน้อย 1 รายการก่อนยืนยันใบรับสินค้า')
                            ->send();

                        return;
                    }

                    $this->record->update([
                        'status' => \App\Enums\OrderStatus::Confirmed,
                    ]);

                    \Filament\Notifications\Notification::make()
                        ->success()
                        ->title('ยืนยันใบรับสินค้าสำเร็จ')
                        ->body('ใบรับสินค้าเลขที่ ' . $this->record->receipt_number . ' ได้รับการยืนยันแล้ว')
                        ->send();
                }),
            Actions\Action::make('printPdf')
                ->label('พิมพ์/PDF')
                ->icon(Heroicon::Printer)
                ->color('gray')
                ->visible(fn(): bool => $this->record->status->value === 'confirmed')
                ->action(function () {
                    return response()->streamDownload(function () {
                        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('pdf.goods-receipt', [
                            'goodsReceipt' => $this->record->load(['company', 'branch', 'supplier', 'purchaseOrder', 'items.product.unit', 'creator']),
                        ]);
                        echo $pdf->stream();
                    }, 'GR-' . $this->record->receipt_number . '.pdf');
                }),
            Actions\Action::make('cancel')
                ->label('ยกเลิก')
                ->icon(Heroicon::XCircle)
                ->color('danger')
                ->visible(fn(): bool => in_array($this->record->status->value, ['draft', 'confirmed']))
                ->requiresConfirmation()
                ->modalHeading('ยกเลิกใบรับสินค้า')
                ->modalDescription('คุณแน่ใจหรือไม่ว่าต้องการยกเลิกใบรับสินค้านี้?')
                ->action(function () {
                    $this->record->update([
                        'status' => \App\Enums\OrderStatus::Cancelled,
                    ]);

                    \Filament\Notifications\Notification::make()
                        ->success()
                        ->title('ยกเลิกใบรับสินค้าสำเร็จ')
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
