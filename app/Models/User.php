<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;

use App\Enums\Roles;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\SoftDeletes;
use Filament\Models\Contracts\HasAvatar;
use Leek\FilamentDiceBear\Concerns\HasDiceBearAvatar;
use Leek\FilamentDiceBear\Enums\DiceBearStyle;
use Illuminate\Support\Facades\Storage;

class User extends Authenticatable implements HasAvatar
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable, HasUlids, SoftDeletes;
    use HasDiceBearAvatar;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'company_id',
        'branch_id',
        'name',
        'username',
        'email',
        'password',
        'profile_photo_path',
        'role',
        'is_active',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'role' => Roles::class,
            'is_active' => 'boolean',
        ];
    }

    public function purchaseOrders(): HasMany
    {
        return $this->hasMany(PurchaseOrder::class, 'created_by');
    }

    public function saleOrders(): HasMany
    {
        return $this->hasMany(SaleOrder::class, 'created_by');
    }

    public function stockMovements(): HasMany
    {
        return $this->hasMany(StockMovement::class, 'created_by');
    }

    protected function getCustomAvatarUrl(): ?string
    {
        if ($this->profile_photo_path) {
            return Storage::url($this->profile_photo_path);
        }

        return null;
    }

    public function dicebearAvatarStyle(): DiceBearStyle
    {
        return DiceBearStyle::Adventurer;
    }
}
