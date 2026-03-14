<?php

namespace App\Providers;

use App\Models\Branch;
use App\Models\Brand;
use App\Models\Category;
use App\Models\Company;
use App\Models\Customer;
use App\Models\GoodsReceipt;
use App\Models\Product;
use App\Models\PurchaseOrder;
use App\Models\PurchaseOrderItem;
use App\Models\SaleOrder;
use App\Models\SaleOrderItem;
use App\Models\Stock;
use App\Models\Supplier;
use App\Models\TaxInvoice;
use App\Models\Unit;
use App\Observers\BrandObserver;
use App\Observers\CategoryObserver;
use App\Observers\DocumentObserver;
use App\Observers\GoodsReceiptObserver;
use App\Observers\ProductObserver;
use App\Observers\PurchaseOrderItemObserver;
use App\Observers\PurchaseOrderObserver;
use App\Observers\SaleOrderItemObserver;
use App\Observers\SaleOrderObserver;
use App\Observers\StockObserver;
use App\Observers\SupplierObserver;
use App\Observers\TaxInvoiceObserver;
use App\Observers\UnitObserver;
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
        // ลงทะเบียน Model Observers
        // ลงทะเบียน Model Observers
        Category::observe(CategoryObserver::class);
        Brand::observe(BrandObserver::class);
        Unit::observe(UnitObserver::class);
        
        // Models using unified DocumentObserver
        Company::observe(DocumentObserver::class);
        Branch::observe(DocumentObserver::class);
        Customer::observe(DocumentObserver::class);
        Supplier::observe([SupplierObserver::class, DocumentObserver::class]);
        Product::observe([ProductObserver::class, DocumentObserver::class]);
        
        // Transactional Document Models
        GoodsReceipt::observe([GoodsReceiptObserver::class, DocumentObserver::class]);
        SaleOrder::observe([SaleOrderObserver::class, DocumentObserver::class]);
        SaleOrderItem::observe(SaleOrderItemObserver::class);
        PurchaseOrder::observe([PurchaseOrderObserver::class, DocumentObserver::class]);
        PurchaseOrderItem::observe(PurchaseOrderItemObserver::class);
        TaxInvoice::observe([TaxInvoiceObserver::class, DocumentObserver::class]);
        
        Stock::observe(StockObserver::class);
    }
}
