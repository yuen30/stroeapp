<?php

namespace App\Models;

use App\Enums\PaymentStatus;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class TaxInvoice extends Model
{
    use HasUlids, SoftDeletes;

    protected $fillable = [
        'company_id',
        'branch_id',
        'customer_id',
        'sale_order_id',
        'created_by',
        'tax_invoice_number',
        'document_date',
        'customer_name',
        'customer_tax_id',
        'customer_address',
        'customer_is_head_office',
        'customer_branch_no',
        'subtotal',
        'discount_amount',
        'vat_rate',
        'vat_amount',
        'total_amount',
        'payment_status',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'document_date' => 'date',
            'customer_is_head_office' => 'boolean',
            'payment_status' => PaymentStatus::class,
            'subtotal' => 'decimal:2',
            'discount_amount' => 'decimal:2',
            'vat_rate' => 'decimal:2',
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

    public function saleOrder(): BelongsTo
    {
        return $this->belongsTo(SaleOrder::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
