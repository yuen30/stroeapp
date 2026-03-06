<?php

namespace App\Models;

use App\Enums\PaymentStatus;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;

class TaxInvoice extends Model
{
    use HasUlids, SoftDeletes, LogsActivity;

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
        'customer_address_line1',
        'customer_address_line2',
        'customer_amphoe',
        'customer_province',
        'customer_postal_code',
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

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly([
                'tax_invoice_number',
                'payment_status',
                'total_amount',
                'customer_name',
                'notes',
            ])
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs()
            ->setDescriptionForEvent(fn(string $eventName) => match ($eventName) {
                'created' => 'สร้างใบกำกับภาษี',
                'updated' => 'แก้ไขใบกำกับภาษี',
                'deleted' => 'ลบใบกำกับภาษี',
                default => $eventName,
            });
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

    // Accessor: รวมที่อยู่เป็น string เดียว
    public function getFullAddressAttribute(): string
    {
        $parts = array_filter([
            $this->customer_address_line1,
            $this->customer_address_line2,
            $this->customer_amphoe ? "อ.{$this->customer_amphoe}" : null,
            $this->customer_province ? "จ.{$this->customer_province}" : null,
            $this->customer_postal_code,
        ]);

        return implode(' ', $parts);
    }
}
