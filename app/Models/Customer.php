<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Customer extends Model
{
    use HasUlids, SoftDeletes;

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
}
