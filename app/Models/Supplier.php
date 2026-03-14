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

class Supplier extends Model implements HasAvatar
{
    use HasUlids, SoftDeletes, HasDiceBearAvatar, DocumentObservable;

    protected $documentNumberField = 'code';

    public function getDocumentType(): string
    {
        return 'supplier';
    }

    protected $fillable = [
        'company_id',
        'name',
        'code',
        'contact_name',
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
            'is_active' => 'boolean',
        ];
    }

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function purchaseOrders(): HasMany
    {
        return $this->hasMany(PurchaseOrder::class);
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
