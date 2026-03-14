<?php

namespace App\Models;

use App\Traits\DocumentObservable;
use Filament\Models\Contracts\HasAvatar;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Storage;
use Leek\FilamentDiceBear\Concerns\HasDiceBearAvatar;
use Leek\FilamentDiceBear\Enums\DiceBearStyle;

class Customer extends Model implements HasAvatar
{
    use HasUlids, SoftDeletes, HasDiceBearAvatar, DocumentObservable;

    protected $documentNumberField = 'code';

    public function getDocumentType(): string
    {
        return 'customer';
    }

    protected $fillable = [
        'company_id',
        'name',
        'code',
        'address_0',
        'address_1',
        'amphoe',
        'province',
        'postal_code',
        'tel',
        'fax',
        'tax_id',
        'credit_limit',
        'credit_days',
        'vat_rate',
        'is_head_office',
        'branch_no',
        'photo_path',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'credit_limit' => 'integer',
            'credit_days' => 'integer',
            'vat_rate' => 'integer',
            'is_head_office' => 'boolean',
            'is_active' => 'boolean',
        ];
    }

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function contacts(): HasMany
    {
        return $this->hasMany(Contact::class);
    }

    public function saleOrders(): HasMany
    {
        return $this->hasMany(SaleOrder::class);
    }

    // คำนวณยอดค้างชำระทั้งหมด (ใบสั่งขายที่ยังไม่ชำระเงิน)
    public function getTotalOutstandingAmount(): float
    {
        return $this
            ->saleOrders()
            ->whereIn('status', ['confirmed', 'partially_received'])
            ->whereIn('payment_status', ['unpaid', 'partial'])
            ->sum('total_amount');
    }

    // คำนวณวงเงินคงเหลือ
    public function getRemainingCreditLimit(): float
    {
        if ($this->credit_limit <= 0) {
            return 0;
        }

        $outstanding = $this->getTotalOutstandingAmount();
        return max(0, $this->credit_limit - $outstanding);
    }

    // ตรวจสอบว่าสามารถสร้างใบสั่งขายได้หรือไม่
    public function canCreateSaleOrder(float $amount): bool
    {
        // ถ้าไม่มีวงเงินเครดิต (เงินสด) ให้สร้างได้เสมอ
        if ($this->credit_limit <= 0) {
            return true;
        }

        $remaining = $this->getRemainingCreditLimit();
        return $amount <= $remaining;
    }

    // คำนวณเปอร์เซ็นต์การใช้วงเงิน
    public function getCreditUsagePercentage(): float
    {
        if ($this->credit_limit <= 0) {
            return 0;
        }

        $outstanding = $this->getTotalOutstandingAmount();
        return min(100, ($outstanding / $this->credit_limit) * 100);
    }

    protected function getCustomAvatarUrl(): ?string
    {
        if ($this->photo_path) {
            return Storage::url($this->photo_path);
        }

        return null;  // Falls back to DiceBear
    }

    public function dicebearAvatarStyle(): DiceBearStyle
    {
        return DiceBearStyle::Initials;
    }

    public function dicebearAvatarOptions(): array
    {
        return [
            'seed' => $this->name,
        ];
    }
}
