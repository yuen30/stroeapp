<?php

namespace App\Traits;

use App\Models\DocumentRunningNumber;
use Illuminate\Database\Eloquent\Model;

trait DocumentObservable
{
    /**
     * Get the document type for this model.
     */
    abstract public function getDocumentType(): string;

    /**
     * Get the attribute name where the document number is stored.
     */
    public function getDocumentNumberAttribute(): string
    {
        return property_exists($this, 'documentNumberField') ? $this->documentNumberField : 'document_number';
    }
}
