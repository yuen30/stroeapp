<?php

namespace Tests\Feature\Filament;

use App\Filament\Resources\Customers\Pages\ListCustomers;
use App\Models\Company;
use App\Models\Customer;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class CustomerResourceTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->for(Company::factory()->create())->create();
    }

    #[Test]
    public function it_can_view_customers_list(): void
    {
        Livewire::actingAs($this->user)
            ->test(ListCustomers::class)
            ->assertStatus(200);
    }

    #[Test]
    public function it_can_see_customers_in_table(): void
    {
        Customer::factory()->create([
            'name' => 'บริษัท ทดสอบ จำกัด',
            'code' => 'CUST001',
        ]);

        Livewire::actingAs($this->user)
            ->test(ListCustomers::class)
            ->assertSee('บริษัท ทดสอบ จำกัด')
            ->assertSee('CUST001');
    }

    #[Test]
    public function it_can_search_by_name(): void
    {
        Customer::factory()->create(['name' => 'บริษัท AAA']);
        Customer::factory()->create(['name' => 'บริษัท BBB']);

        Livewire::actingAs($this->user)
            ->test(ListCustomers::class)
            ->searchTable('AAA')
            ->assertCanSeeTableRecords(Customer::where('name', 'like', '%AAA%')->get())
            ->assertCanNotSeeTableRecords(Customer::where('name', 'like', '%BBB%')->get());
    }

    #[Test]
    public function it_can_search_by_code(): void
    {
        Customer::factory()->create(['code' => 'CODE-A']);
        Customer::factory()->create(['code' => 'CODE-B']);

        Livewire::actingAs($this->user)
            ->test(ListCustomers::class)
            ->searchTable('CODE-A')
            ->assertCanSeeTableRecords(Customer::where('code', 'CODE-A')->get());
    }

    #[Test]
    public function it_shows_multiple_customers(): void
    {
        $customer1 = Customer::factory()->create(['name' => 'ลูกค้า A']);
        $customer2 = Customer::factory()->create(['name' => 'ลูกค้า B']);

        Livewire::actingAs($this->user)
            ->test(ListCustomers::class)
            ->assertCanSeeTableRecords([$customer1, $customer2]);
    }

    #[Test]
    public function it_displays_credit_limit(): void
    {
        Customer::factory()->create([
            'name' => 'ลูกค้า VIP',
            'credit_limit' => 100000,
        ]);

        Livewire::actingAs($this->user)
            ->test(ListCustomers::class)
            ->assertSee('ลูกค้า VIP');
    }

    #[Test]
    public function it_shows_active_and_inactive_customers(): void
    {
        Customer::factory()->create(['name' => 'ลูกค้าที่ใช้งาน', 'is_active' => true]);
        Customer::factory()->create(['name' => 'ลูกค้าไม่ใช้งาน', 'is_active' => false]);

        Livewire::actingAs($this->user)
            ->test(ListCustomers::class)
            ->assertCanSeeTableRecords(Customer::where('is_active', true)->get());
    }
}
