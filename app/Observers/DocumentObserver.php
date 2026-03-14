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
     * @param Model|DocumentObservable $model
     */
    public function creating(Model $model): void
    {
        // Only process models using the DocumentObservable trait and missing a number
        $numberField = $model->getDocumentNumberAttribute();
        
        if (!empty($model->{$numberField})) {
            return;
        }

        $type = $model->getDocumentType();
        $companyId = $model->company_id;
        $branchId = $model->branch_id;

        // Transaction to ensure atomicity and prevent duplicate numbers
        DB::transaction(function () use ($model, $type, $companyId, $branchId, $numberField) {
            $runningNumber = DocumentRunningNumber::where('document_type', $type)
                ->where('is_active', true)
                ->when($companyId, fn($q) => $q->where('company_id', $companyId), fn($q) => $q->whereNull('company_id'))
                ->when($branchId, fn($q) => $q->where('branch_id', $branchId), fn($q) => $q->whereNull('branch_id'))
                ->lockForUpdate()
                ->first();

            if (!$runningNumber && $branchId) {
                // Fallback to company level if branch level not found and we were looking for a branch
                $runningNumber = DocumentRunningNumber::where('company_id', $companyId)
                    ->whereNull('branch_id')
                    ->where('document_type', $type)
                    ->where('is_active', true)
                    ->lockForUpdate()
                    ->first();
            }

            if ($runningNumber) {
                // Generate the number
                $model->{$numberField} = $runningNumber->getNextNumber();
                
                // Increment current count
                $runningNumber->increment('current_number');
            }
        });
    }
}
