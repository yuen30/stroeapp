<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Model;

class StockReservation extends Model
{
    use HasUlids;

    protected $fillable = [
        'product_id',
        'sale_order_id',
        'sale_order_item_id',
        'reserved_quantity',
        'expires_at',
    ];

    protected function casts(): array
    {
        return [
            'reserved_quantity' => 'integer',
            'expires_at' => 'datetime',
        ];
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function saleOrder(): BelongsTo
    {
        return $this->belongsTo(SaleOrder::class);
    }

    public function saleOrderItem(): BelongsTo
    {
        return $this->belongsTo(SaleOrderItem::class);
    }
}
