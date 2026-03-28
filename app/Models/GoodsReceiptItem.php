<?php

namespace App\Models;

use App\Enums\ProductCondition;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class GoodsReceiptItem extends Model
{
    use HasFactory, HasUlids;

    protected $fillable = [
        'goods_receipt_id',
        'product_id',
        'purchase_order_item_id',
        'description',
        'quantity',
        'condition',
        'quantity_damaged',
        'quantity_defective',
        'quality_notes',
        'images',
    ];

    protected function casts(): array
    {
        return [
            'quantity' => 'integer',
            'quantity_damaged' => 'integer',
            'quantity_defective' => 'integer',
            'condition' => ProductCondition::class,
            'images' => 'array',
        ];
    }

    public function goodsReceipt(): BelongsTo
    {
        return $this->belongsTo(GoodsReceipt::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function purchaseOrderItem(): BelongsTo
    {
        return $this->belongsTo(PurchaseOrderItem::class);
    }

    // คำนวณจำนวนสินค้าที่ดี
    public function getQuantityGoodAttribute(): int
    {
        return $this->quantity - $this->quantity_damaged - $this->quantity_defective;
    }
}
