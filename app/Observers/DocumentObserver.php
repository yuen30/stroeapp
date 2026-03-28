<?php

namespace App\Observers;

use App\Models\DocumentRunningNumber;
use App\Traits\DocumentObservable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class DocumentObserver
{
    /**
     * Handle the Model "creating" event.
     *
     * @param  Model|DocumentObservable  $model
     */
    public function creating(Model $model): void
    {
        // Only process models using the DocumentObservable trait and missing a number
        $numberField = $model->getDocumentNumberAttribute();

        if (! empty($model->{$numberField})) {
            return;
        }

        $type = $model->getDocumentType();
        $companyId = $model->company_id ?? null;
        $branchId = $model->branch_id ?? null;

        // Transaction to ensure atomicity and prevent duplicate numbers
        DB::transaction(function () use ($model, $type, $companyId, $branchId, $numberField) {
            $runningNumber = DocumentRunningNumber::where('document_type', $type)
                ->where('is_active', true)
                ->when($companyId, fn ($q) => $q->where('company_id', $companyId), fn ($q) => $q->whereNull('company_id'))
                ->when($branchId, fn ($q) => $q->where('branch_id', $branchId), fn ($q) => $q->whereNull('branch_id'))
                ->lockForUpdate()
                ->first();

            if (! $runningNumber && $branchId) {
                // Fallback to company level if branch level not found and we were looking for a branch
                $runningNumber = DocumentRunningNumber::where('company_id', $companyId)
                    ->whereNull('branch_id')
                    ->where('document_type', $type)
                    ->where('is_active', true)
                    ->lockForUpdate()
                    ->first();
            }

            // Auto-create running number config if not found
            if (! $runningNumber) {
                $runningNumber = $this->createDefaultConfig($type, $companyId, $branchId);
            }

            if ($runningNumber) {
                // Generate the number
                $model->{$numberField} = $runningNumber->getNextNumber();

                // Increment current count
                $runningNumber->increment('current_number');
            }
        });
    }

    /**
     * Create default running number config if not exists
     */
    private function createDefaultConfig(string $type, ?string $companyId, ?string $branchId): ?DocumentRunningNumber
    {
        $prefixes = [
            'company' => 'COMP',
            'branch' => 'BR',
            'unit' => 'UNIT',
            'brand' => 'BRAND',
            'category' => 'CAT',
            'customer' => 'CUS',
            'supplier' => 'SUP',
            'product' => 'PROD',
            'sale_order' => 'SO',
            'purchase_order' => 'PO',
            'goods_receipt' => 'GR',
            'tax_invoice' => 'INV',
            'stock_reservation' => 'RESV',
            'contact' => 'CONT',
            'payment_method' => 'PM',
            'payment_status' => 'PS',
        ];

        if (! isset($prefixes[$type])) {
            return null;
        }

        return DocumentRunningNumber::create([
            'company_id' => $companyId,
            'branch_id' => $branchId,
            'document_type' => $type,
            'prefix' => $prefixes[$type],
            'date_format' => in_array($type, ['sale_order', 'purchase_order', 'goods_receipt', 'tax_invoice']) ? 'Ym' : '',
            'running_length' => 6,
            'current_number' => 0,
            'is_active' => true,
        ]);
    }
}
