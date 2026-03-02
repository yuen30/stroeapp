<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DocumentRunningNumber extends Model
{
    use HasUlids;

    protected $fillable = [
        'company_id',
        'branch_id',
        'document_type',
        'prefix',
        'date_format',
        'running_length',
        'current_number',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'running_length' => 'integer',
            'current_number' => 'integer',
            'is_active' => 'boolean',
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

    /**
     * Generate next document number based on pattern (e.g. INV2403-0001)
     */
    public function getNextNumber(): string
    {
        $dateComponent = '';
        if ($this->date_format) {
            $dateComponent = date($this->date_format); // e.g., 'Ym' -> 202603
        }

        $nextNum = $this->current_number + 1;
        $runningComponent = str_pad((string)$nextNum, $this->running_length, '0', STR_PAD_LEFT);

        return $this->prefix . $dateComponent . '-' . $runningComponent;
    }
}
