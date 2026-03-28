<?php

namespace App\Filament\Resources\GoodsReceipts\Pages;

use App\Enums\OrderStatus;
use App\Filament\Resources\GoodsReceipts\GoodsReceiptResource;
use App\Filament\Resources\GoodsReceipts\Schemas\GoodsReceiptInfolist;
use App\Models\GoodsReceiptItem;
use App\Models\Product;
use App\Models\PurchaseOrderItem;
use Barryvdh\DomPDF\Facade\Pdf;
use Filament\Actions;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ViewRecord;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;

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
                ->visible(fn (): bool => $this->record->status->value === 'draft')
                ->modalHeading($this->record->is_standalone ? 'เพิ่มสินค้า (รับแยก)' : 'เพิ่มสินค้าในใบรับสินค้า')
                ->modalWidth('4xl')
                ->schema(function () {
                    if ($this->record->purchase_order_id === null) {
                        return [
                            Grid::make(3)
                                ->schema([
                                    Select::make('product_id')
                                        ->label('สินค้า')
                                        ->options(function () {
                                            return Product::where('is_active', true)
                                                ->orderBy('name')
                                                ->get()
                                                ->mapWithKeys(fn ($product) => [
                                                    $product->id => "{$product->name} ({$product->code})",
                                                ]);
                                        })
                                        ->searchable()
                                        ->preload()
                                        ->native(false)
                                        ->required()
                                        ->reactive()
                                        ->columnSpanFull(),
                                    Textarea::make('description')
                                        ->label('รายละเอียด')
                                        ->rows(2)
                                        ->placeholder('รายละเอียดเพิ่มเติม')
                                        ->columnSpanFull(),
                                    TextInput::make('quantity')
                                        ->label('จำนวนที่รับ')
                                        ->required()
                                        ->numeric()
                                        ->default(1)
                                        ->minValue(1)
                                        ->suffix('หน่วย')
                                        ->columnSpan(3),
                                ]),
                        ];
                    }

                    return [
                        Grid::make(3)
                            ->schema([
                                Select::make('purchase_order_item_id')
                                    ->label('สินค้าจากใบสั่งซื้อ')
                                    ->options(function () {
                                        if (! $this->record->purchase_order_id) {
                                            return [];
                                        }

                                        $poItems = PurchaseOrderItem::where('purchase_order_id', $this->record->purchase_order_id)
                                            ->with('product')
                                            ->get();

                                        // คำนวณจำนวนที่รับแล้วจากใบรับสินค้าที่ยืนยันแล้ว
                                        $receivedQuantities = GoodsReceiptItem::whereHas('goodsReceipt', function ($query) {
                                            $query
                                                ->where('purchase_order_id', $this->record->purchase_order_id)
                                                ->where('status', OrderStatus::Confirmed);
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
                                                return [$item->id => $item->product->name." (สั่ง: {$ordered} | รับแล้ว: {$received} | คงเหลือ: {$remaining})"];
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
                                            $poItem = PurchaseOrderItem::with('product')->find($state);
                                            if ($poItem) {
                                                // คำนวณจำนวนที่รับแล้ว
                                                $receivedQuantity = GoodsReceiptItem::whereHas('goodsReceipt', function ($query) {
                                                    $query
                                                        ->where('purchase_order_id', $this->record->purchase_order_id)
                                                        ->where('status', OrderStatus::Confirmed);
                                                })
                                                    ->where('purchase_order_item_id', $state)
                                                    ->sum('quantity');

                                                $remaining = $poItem->quantity - $receivedQuantity;

                                                $set('product_id', $poItem->product_id);
                                                $set('description', $poItem->description);
                                                $set('quantity', min($remaining, 1));
                                                $set('max_quantity', $remaining);
                                            }
                                        }
                                    })
                                    ->columnSpanFull(),
                                Hidden::make('product_id'),
                                Hidden::make('max_quantity'),
                                Textarea::make('description')
                                    ->label('รายละเอียด')
                                    ->rows(2)
                                    ->placeholder('รายละเอียดเพิ่มเติม')
                                    ->columnSpanFull()
                                    ->columnStart(1),
                                TextInput::make('quantity')
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
                    ];
                })
                ->action(function (array $data) {
                    if ($this->record->purchase_order_id === null) {
                        // Standalone mode: เพิ่มสินค้าโดยตรง
                        if (empty($data['product_id'])) {
                            Notification::make()
                                ->danger()
                                ->title('ไม่สามารถเพิ่มสินค้าได้')
                                ->body('กรุณาเลือกสินค้า')
                                ->send();

                            return;
                        }

                        // ตรวจสอบว่ามีสินค้านี้อยู่แล้วหรือไม่
                        $existingItem = $this
                            ->record
                            ->items()
                            ->where('product_id', $data['product_id'])
                            ->first();

                        if ($existingItem) {
                            $existingItem->update([
                                'description' => $data['description'] ?? $existingItem->description,
                                'quantity' => $data['quantity'],
                            ]);
                            $message = 'อัพเดทสินค้าสำเร็จ';
                        } else {
                            $this->record->items()->create([
                                'product_id' => $data['product_id'],
                                'description' => $data['description'] ?? null,
                                'quantity' => $data['quantity'],
                            ]);
                            $message = 'เพิ่มสินค้าสำเร็จ';
                        }

                        // ตรวจสอบ min/max stock
                        $product = Product::find($data['product_id']);
                        $newStock = $product->stock_quantity + $data['quantity'];

                        if ($product->max_stock > 0 && $newStock > $product->max_stock) {
                            Notification::make()
                                ->warning()
                                ->title('แจ้งเตือนสต็อก')
                                ->body("{$product->name}: สต็อกใหม่ ({$newStock}) เกินขั้นสูง ({$product->max_stock})")
                                ->send();
                        }

                        if ($product->min_stock > 0 && $newStock <= $product->min_stock) {
                            Notification::make()
                                ->warning()
                                ->title('แจ้งเตือนสต็อก')
                                ->body("{$product->name}: สต็อกใหม่ ({$newStock}) ต่ำกว่าหรือเท่ากับขั้นต่ำ ({$product->min_stock})")
                                ->send();
                        }
                    } else {
                        // Non-standalone mode: เพิ่มจากใบสั่งซื้อ
                        $poItem = PurchaseOrderItem::find($data['purchase_order_item_id']);
                        if (! $poItem) {
                            Notification::make()
                                ->danger()
                                ->title('ไม่พบข้อมูลสินค้า')
                                ->send();

                            return;
                        }

                        // คำนวณจำนวนที่รับแล้วจากใบรับสินค้าที่ยืนยันแล้ว
                        $receivedQuantity = GoodsReceiptItem::whereHas('goodsReceipt', function ($query) {
                            $query
                                ->where('purchase_order_id', $this->record->purchase_order_id)
                                ->where('status', OrderStatus::Confirmed);
                        })
                            ->where('purchase_order_item_id', $data['purchase_order_item_id'])
                            ->sum('quantity');

                        $remaining = $poItem->quantity - $receivedQuantity;

                        if ($data['quantity'] > $remaining) {
                            Notification::make()
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
                            $existingItem->update([
                                'description' => $data['description'] ?? $existingItem->description,
                                'quantity' => $data['quantity'],
                            ]);
                            $message = 'อัพเดทสินค้าสำเร็จ';
                        } else {
                            $this->record->items()->create([
                                'purchase_order_item_id' => $data['purchase_order_item_id'],
                                'product_id' => $data['product_id'],
                                'description' => $data['description'] ?? null,
                                'quantity' => $data['quantity'],
                            ]);
                            $message = 'เพิ่มสินค้าสำเร็จ';
                        }
                    }

                    // Refresh the record
                    $this->record->refresh();

                    Notification::make()
                        ->success()
                        ->title($message)
                        ->send();
                }),
            Actions\Action::make('addAllItems')
                ->label('รับสินค้าทั้งหมด')
                ->icon(Heroicon::PlusCircle)
                ->color('success')
                ->hidden(fn (): bool => $this->record->purchase_order_id === null)
                ->requiresConfirmation()
                ->modalHeading('รับสินค้าทั้งหมด')
                ->modalDescription('คุณต้องการเพิ่มสินค้าทั้งหมดจากใบสั่งซื้อที่ยังรับไม่ครบหรือไม่?')
                ->action(function () {
                    if (! $this->record->purchase_order_id) {
                        Notification::make()
                            ->warning()
                            ->title('ไม่พบข้อมูลใบสั่งซื้อ')
                            ->send();

                        return;
                    }

                    $poItems = PurchaseOrderItem::where('purchase_order_id', $this->record->purchase_order_id)
                        ->with('product')
                        ->get();

                    // คำนวณจำนวนที่รับแล้วจากใบรับสินค้าที่ยืนยันแล้ว
                    $receivedQuantities = GoodsReceiptItem::whereHas('goodsReceipt', function ($query) {
                        $query
                            ->where('purchase_order_id', $this->record->purchase_order_id)
                            ->where('status', OrderStatus::Confirmed);
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
                        Notification::make()
                            ->success()
                            ->title('เพิ่มสินค้าสำเร็จ')
                            ->body("เพิ่มสินค้า {$addedCount} รายการ")
                            ->send();
                    } else {
                        Notification::make()
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
                ->visible(fn (): bool => $this->record->status->value === 'draft')
                ->requiresConfirmation()
                ->modalHeading('ยืนยันใบรับสินค้า')
                ->modalDescription('คุณแน่ใจหรือไม่ว่าต้องการยืนยันใบรับสินค้านี้? หลังจากยืนยันแล้วจะไม่สามารถแก้ไขรายการสินค้าได้และระบบจะอัพเดทสต็อกสินค้า')
                ->action(function () {
                    if ($this->record->items()->count() === 0) {
                        Notification::make()
                            ->warning()
                            ->title('ไม่สามารถยืนยันได้')
                            ->body('กรุณาเพิ่มสินค้าอย่างน้อย 1 รายการก่อนยืนยันใบรับสินค้า')
                            ->send();

                        return;
                    }

                    // ตรวจสอบ min/max stock
                    $warnings = [];
                    foreach ($this->record->items as $item) {
                        $product = $item->product;
                        $newStock = $product->stock_quantity + $item->quantity;

                        if ($product->max_stock > 0 && $newStock > $product->max_stock) {
                            $warnings[] = "{$product->name}: สต็อกใหม่ ({$newStock}) เกินขั้นสูง ({$product->max_stock})";
                        }

                        if ($product->min_stock > 0 && $newStock <= $product->min_stock) {
                            $warnings[] = "{$product->name}: สต็อกใหม่ ({$newStock}) ต่ำกว่าหรือเท่ากับขั้นต่ำ ({$product->min_stock})";
                        }
                    }

                    if (! empty($warnings)) {
                        foreach ($warnings as $warning) {
                            Notification::make()
                                ->warning()
                                ->title('แจ้งเตือนสต็อก')
                                ->body($warning)
                                ->send();
                        }
                    }

                    $this->record->update([
                        'status' => OrderStatus::Confirmed,
                    ]);

                    Notification::make()
                        ->success()
                        ->title('ยืนยันใบรับสินค้าสำเร็จ')
                        ->body('ใบรับสินค้าเลขที่ '.$this->record->receipt_number.' ได้รับการยืนยันแล้ว')
                        ->send();
                }),
            Actions\Action::make('printPdf')
                ->label('พิมพ์/PDF')
                ->icon(Heroicon::Printer)
                ->color('gray')
                ->visible(fn (): bool => $this->record->status->value === 'confirmed')
                ->action(function () {
                    return response()->streamDownload(function () {
                        $pdf = Pdf::loadView('pdf.goods-receipt', [
                            'goodsReceipt' => $this->record->load(['company', 'branch', 'supplier', 'purchaseOrder', 'items.product.unit', 'creator']),
                        ]);
                        echo $pdf->stream();
                    }, 'GR-'.$this->record->receipt_number.'.pdf');
                }),
            Actions\Action::make('cancel')
                ->label('ยกเลิก')
                ->icon(Heroicon::XCircle)
                ->color('danger')
                ->visible(fn (): bool => in_array($this->record->status->value, ['draft', 'confirmed']))
                ->requiresConfirmation()
                ->modalHeading('ยกเลิกใบรับสินค้า')
                ->modalDescription('คุณแน่ใจหรือไม่ว่าต้องการยกเลิกใบรับสินค้านี้?')
                ->action(function () {
                    $this->record->update([
                        'status' => OrderStatus::Cancelled,
                    ]);

                    Notification::make()
                        ->success()
                        ->title('ยกเลิกใบรับสินค้าสำเร็จ')
                        ->send();
                }),
            Actions\EditAction::make()
                ->label('แก้ไข')
                ->icon(Heroicon::PencilSquare)
                ->visible(fn (): bool => $this->record->status->value === 'draft'),
            Actions\DeleteAction::make()
                ->label('ลบ')
                ->icon(Heroicon::Trash)
                ->visible(fn (): bool => $this->record->status->value === 'draft'),
        ];
    }
}
