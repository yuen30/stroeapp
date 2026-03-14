<?php

namespace App\Models;

use App\Traits\DocumentObservable;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Product extends Model
{
    use HasFactory, HasUlids, SoftDeletes, DocumentObservable;

    protected $documentNumberField = 'code';

    public function getDocumentType(): string
    {
        return 'product';
    }

    protected $fillable = [
        'name',
        'code',
        'category_id',
        'description',
        'company_id',
        'branch_id',
        'unit_id',
        'brand_id',
        'cost_price',
        'selling_price',
        'stock_quantity',
        'min_stock',
        'max_stock',
        'photo_path',
        'credit_limit',
        'credit_days',
        'vat_rate',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'cost_price' => 'decimal:2',
            'selling_price' => 'decimal:2',
            'stock_quantity' => 'integer',
            'min_stock' => 'integer',
            'max_stock' => 'integer',
            'credit_limit' => 'integer',
            'credit_days' => 'integer',
            'vat_rate' => 'integer',
            'is_active' => 'boolean',
        ];
    }

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function unit(): BelongsTo
    {
        return $this->belongsTo(Unit::class);
    }

    public function brand(): BelongsTo
    {
        return $this->belongsTo(Brand::class);
    }

    public function stocks(): HasMany
    {
        return $this->hasMany(Stock::class);
    }

    public function prices(): HasMany
    {
        return $this->hasMany(ProductPrice::class);
    }

    public function images(): HasMany
    {
        return $this->hasMany(ProductImage::class);
    }

    public function purchaseOrderItems(): HasMany
    {
        return $this->hasMany(PurchaseOrderItem::class);
    }

    public function saleOrderItems(): HasMany
    {
        return $this->hasMany(SaleOrderItem::class);
    }

    public function stockMovements(): HasMany
    {
        return $this->hasMany(StockMovement::class);
    }

    public function stockReservations(): HasMany
    {
        return $this->hasMany(StockReservation::class);
    }

    // Accessor สำหรับ reserved quantity
    public function getReservedQuantityAttribute(): int
    {
        return $this
            ->stockReservations()
            ->where('expires_at', '>', now())
            ->sum('reserved_quantity');
    }

    // Accessor สำหรับ available stock
    public function getAvailableStockAttribute(): int
    {
        return max(0, $this->stock_quantity - $this->reserved_quantity);
    }

    // ตรวจสอบว่า stock ต่ำกว่าขั้นต่ำหรือไม่
    public function isLowStock(): bool
    {
        return $this->min_stock > 0 && $this->stock_quantity <= $this->min_stock;
    }

    // ตรวจสอบว่า stock เกินขั้นสูงสุดหรือไม่
    public function isOverStock(): bool
    {
        return $this->max_stock > 0 && $this->stock_quantity >= $this->max_stock;
    }
}
