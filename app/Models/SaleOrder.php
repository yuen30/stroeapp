<?php

namespace App\Models;

use App\Enums\DocumentType;
use App\Enums\OrderStatus;
use App\Enums\PaymentMethod;
use App\Enums\PaymentStatus;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class SaleOrder extends Model
{
    use HasUlids, SoftDeletes;

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
        'status',
        'payment_status',
        'payment_method',
        'subtotal',
        'discount_amount',
        'vat_amount',
        'total_amount',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'order_date' => 'date',
            'due_date' => 'date',
            'document_type' => DocumentType::class,
            'status' => OrderStatus::class,
            'payment_status' => PaymentStatus::class,
            'payment_method' => PaymentMethod::class,
            'subtotal' => 'decimal:2',
            'discount_amount' => 'decimal:2',
            'vat_amount' => 'decimal:2',
            'total_amount' => 'decimal:2',
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
}
