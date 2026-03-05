<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Branch extends Model
{
    use HasFactory, HasUlids, SoftDeletes;

    protected $fillable = [
        'company_id',
        'is_headquarter',
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
        'photo_path',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'is_headquarter' => 'boolean',
            'is_active' => 'boolean',
        ];
    }

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function products(): HasMany
    {
        return $this->hasMany(Product::class);
    }

    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }
}
