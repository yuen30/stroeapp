<?php

namespace Tests\Unit;

use App\Filament\Resources\Customers\CustomerResource;
use App\Filament\Resources\Suppliers\SupplierResource;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class GlobalSearchConfigurationTest extends TestCase
{
    #[Test]
    public function customer_global_search_does_not_reference_missing_email_column(): void
    {
        $this->assertNotContains('email', CustomerResource::getGloballySearchableAttributes());
    }

    #[Test]
    public function supplier_global_search_does_not_reference_missing_email_column(): void
    {
        $this->assertNotContains('email', SupplierResource::getGloballySearchableAttributes());
    }
}
