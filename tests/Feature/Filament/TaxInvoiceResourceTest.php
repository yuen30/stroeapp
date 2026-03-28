<?php

namespace Tests\Feature\Filament;

use App\Filament\Resources\TaxInvoices\Pages\ListTaxInvoices;
use App\Models\Customer;
use App\Models\TaxInvoice;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class TaxInvoiceResourceTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create();
    }

    #[Test]
    public function it_can_view_tax_invoices_list(): void
    {
        Livewire::actingAs($this->user)
            ->test(ListTaxInvoices::class)
            ->assertStatus(200);
    }

    #[Test]
    public function it_can_see_tax_invoices_in_table(): void
    {
        $customer = Customer::factory()->create();

        $taxInvoice = TaxInvoice::factory()->for($customer)->create([
            'tax_invoice_number' => 'TAX-TEST-001',
            'created_by' => $this->user->id,
        ]);

        Livewire::actingAs($this->user)
            ->test(ListTaxInvoices::class)
            ->assertSee('TAX-TEST-001');
    }

    #[Test]
    public function it_can_search_tax_invoices(): void
    {
        $customer = Customer::factory()->create();

        TaxInvoice::factory()->for($customer)->create([
            'tax_invoice_number' => 'TAX-SEARCH-001',
            'created_by' => $this->user->id,
        ]);

        TaxInvoice::factory()->for($customer)->create([
            'tax_invoice_number' => 'TAX-OTHER-001',
            'created_by' => $this->user->id,
        ]);

        Livewire::actingAs($this->user)
            ->test(ListTaxInvoices::class)
            ->searchTable('TAX-SEARCH-001')
            ->assertCanSeeTableRecords(TaxInvoice::where('tax_invoice_number', 'TAX-SEARCH-001')->get())
            ->assertCanNotSeeTableRecords(TaxInvoice::where('tax_invoice_number', 'TAX-OTHER-001')->get());
    }
}
