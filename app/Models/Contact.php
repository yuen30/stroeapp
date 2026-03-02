<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Contact extends Model
{
    use HasUlids, SoftDeletes;

    protected $fillable = [
        'customer_id',
        'name',
        'code',
        'address_0',
        'address_1',
        'amphoe',
        'province',
        'postal_code',
        'email',
        'tel',
        'fax',
        'tax_id',
        'photo_path',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
        ];
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }
}
