<?php

namespace App\Models;

use App\Enums\DocumentType;
use App\Enums\OrderStatus;
use App\Traits\DocumentObservable;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class SaleOrder extends Model
{
    use DocumentObservable, HasFactory, HasUlids, LogsActivity, SoftDeletes;

    protected $documentNumberField = 'invoice_number';

    public function getDocumentType(): string
    {
        return 'sale_order';
    }

    protected $fillable = [
        'company_id',
        'branch_id',
        'customer_id',
        'created_by',
        'salesman_id',
        'document_type',
        'invoice_number',
        'order_date',
        'due_date',
        'term_of_payment',
        'reference_number',
        'status',
        'payment_status_id',
        'payment_method_id',
        'subtotal',
        'discount_amount',
        'vat_amount',
        'total_amount',
        'notes',
        'attachments',
        'delivery_date',
        'shipping_method',
        'shipping_address',
        'contact_person',
        'contact_phone',
    ];

    protected function casts(): array
    {
        return [
            'order_date' => 'date',
            'due_date' => 'date',
            'delivery_date' => 'date',
            'document_type' => DocumentType::class,
            'status' => OrderStatus::class,
            'subtotal' => 'decimal:2',
            'discount_amount' => 'decimal:2',
            'vat_amount' => 'decimal:2',
            'total_amount' => 'decimal:2',
            'attachments' => 'array',
        ];
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly([
                'status',
                'subtotal',
                'discount_amount',
                'vat_amount',
                'total_amount',
                'notes',
            ])
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs()
            ->setDescriptionForEvent(fn(string $eventName) => match ($eventName) {
                'created' => 'สร้างใบส่งสินค้า',
                'updated' => 'แก้ไขใบส่งสินค้า',
                'deleted' => 'ลบใบส่งสินค้า',
                default => $eventName,
            })
            ->useLogName('sale_order');
    }

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function salesman(): BelongsTo
    {
        return $this->belongsTo(User::class, 'salesman_id');
    }

    public function items(): HasMany
    {
        return $this->hasMany(SaleOrderItem::class);
    }

    public function stockMovements(): HasMany
    {
        return $this->hasMany(StockMovement::class);
    }

    public function taxInvoices(): HasMany
    {
        return $this->hasMany(TaxInvoice::class);
    }

    public function paymentMethod(): BelongsTo
    {
        return $this->belongsTo(PaymentMethod::class);
    }

    public function paymentStatus(): BelongsTo
    {
        return $this->belongsTo(PaymentStatus::class);
    }
}
