<?php

namespace App\Models;

use App\Enums\OrderStatus;
use App\Traits\DocumentObservable;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;

class PurchaseOrder extends Model
{
    use HasUlids, SoftDeletes, LogsActivity, DocumentObservable;

    protected $documentNumberField = 'order_number';

    public function getDocumentType(): string
    {
        return 'purchase_order';
    }

    protected $fillable = [
        'company_id',
        'branch_id',
        'supplier_id',
        'created_by',
        'order_number',
        'order_date',
        'expected_date',
        'status',
        'subtotal',
        'discount_amount',
        'vat_amount',
        'total_amount',
        'payment_terms',
        'delivery_address',
        'contact_person',
        'contact_phone',
        'reference_number',
        'attachments',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'order_date' => 'date',
            'expected_date' => 'date',
            'status' => OrderStatus::class,
            'subtotal' => 'decimal:2',
            'discount_amount' => 'decimal:2',
            'vat_amount' => 'decimal:2',
            'total_amount' => 'decimal:2',
            'attachments' => 'array',
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

    public function supplier(): BelongsTo
    {
        return $this->belongsTo(Supplier::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function items(): HasMany
    {
        return $this->hasMany(PurchaseOrderItem::class);
    }

    public function stockMovements(): HasMany
    {
        return $this->hasMany(StockMovement::class);
    }

    public function goodsReceipts(): HasMany
    {
        return $this->hasMany(GoodsReceipt::class);
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly([
                'order_number',
                'order_date',
                'expected_date',
                'status',
                'supplier.name',
                'subtotal',
                'discount_amount',
                'vat_amount',
                'total_amount',
                'payment_terms',
                'delivery_address',
                'contact_person',
                'contact_phone',
                'reference_number',
                'notes',
            ])
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs()
            ->setDescriptionForEvent(fn(string $eventName) => match ($eventName) {
                'created' => 'สร้างใบสั่งซื้อ',
                'updated' => 'แก้ไขใบสั่งซื้อ',
                'deleted' => 'ลบใบสั่งซื้อ',
                default => $eventName,
            });
    }
}
