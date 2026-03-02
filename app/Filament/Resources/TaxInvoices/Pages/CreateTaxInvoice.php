<?php

namespace App\Filament\Resources\TaxInvoices\Pages;

use App\Filament\Resources\TaxInvoices\TaxInvoiceResource;
use Filament\Resources\Pages\CreateRecord;

class CreateTaxInvoice extends CreateRecord
{
    protected static string $resource = TaxInvoiceResource::class;
}
