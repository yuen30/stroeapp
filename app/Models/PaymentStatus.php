<?php

namespace App\Models;

use App\Traits\DocumentObservable;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class PaymentStatus extends Model
{
    use DocumentObservable, HasFactory, HasUlids, LogsActivity, SoftDeletes;

    protected $documentNumberField = 'code';

    public function getDocumentType(): string
    {
        return 'payment_status';
    }

    protected $fillable = [
        'name',
        'code',
        'color',
        'description',
        'sort_order',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'sort_order' => 'integer',
            'is_active' => 'boolean',
        ];
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['name', 'code', 'color', 'description', 'sort_order', 'is_active'])
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs()
            ->setDescriptionForEvent(fn (string $eventName) => match ($eventName) {
                'created' => 'สร้างสถานะการชำระเงิน',
                'updated' => 'แก้ไขสถานะการชำระเงิน',
                'deleted' => 'ลบสถานะการชำระเงิน',
                default => $eventName,
            })
            ->useLogName('payment_status');
    }
}
