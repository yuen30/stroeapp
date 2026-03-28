<?php

namespace App\Providers;

use App\Models\Branch;
use App\Models\Brand;
use App\Models\Category;
use App\Models\Company;
use App\Models\Contact;
use App\Models\Customer;
use App\Models\GoodsReceipt;
use App\Models\PaymentMethod;
use App\Models\PaymentStatus;
use App\Models\Product;
use App\Models\PurchaseOrder;
use App\Models\PurchaseOrderItem;
use App\Models\SaleOrder;
use App\Models\SaleOrderItem;
use App\Models\Stock;
use App\Models\StockReservation;
use App\Models\Supplier;
use App\Models\TaxInvoice;
use App\Models\Unit;
use App\Observers\DocumentObserver;
use App\Observers\GoodsReceiptObserver;
use App\Observers\ProductObserver;
use App\Observers\PurchaseOrderItemObserver;
use App\Observers\PurchaseOrderObserver;
use App\Observers\SaleOrderItemObserver;
use App\Observers\SaleOrderObserver;
use App\Observers\StockObserver;
use App\Observers\SupplierObserver;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Models using DocumentObserver for auto code generation
        Company::observe(DocumentObserver::class);
        Branch::observe(DocumentObserver::class);
        Customer::observe(DocumentObserver::class);
        Supplier::observe([SupplierObserver::class, DocumentObserver::class]);
        Unit::observe(DocumentObserver::class);
        Brand::observe(DocumentObserver::class);
        Category::observe(DocumentObserver::class);
        Product::observe([ProductObserver::class, DocumentObserver::class]);

        // Transactional Document Models
        GoodsReceipt::observe([GoodsReceiptObserver::class, DocumentObserver::class]);
        SaleOrder::observe([SaleOrderObserver::class, DocumentObserver::class]);
        SaleOrderItem::observe(SaleOrderItemObserver::class);
        PurchaseOrder::observe([PurchaseOrderObserver::class, DocumentObserver::class]);
        PurchaseOrderItem::observe(PurchaseOrderItemObserver::class);
        TaxInvoice::observe(DocumentObserver::class);

        Stock::observe(StockObserver::class);
        StockReservation::observe(DocumentObserver::class);
        Contact::observe(DocumentObserver::class);

        // Payment Models
        PaymentMethod::observe(DocumentObserver::class);
        PaymentStatus::observe(DocumentObserver::class);
    }
}
