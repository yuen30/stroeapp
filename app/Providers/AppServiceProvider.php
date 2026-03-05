<?php

namespace App\Providers;

use App\Models\Brand;
use App\Models\Category;
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
        Category::observe(CategoryObserver::class);
        Brand::observe(BrandObserver::class);
        Unit::observe(UnitObserver::class);
        Supplier::observe(SupplierObserver::class);
        GoodsReceipt::observe(GoodsReceiptObserver::class);
        SaleOrder::observe(SaleOrderObserver::class);
        SaleOrderItem::observe(SaleOrderItemObserver::class);
        PurchaseOrder::observe(PurchaseOrderObserver::class);
        PurchaseOrderItem::observe(PurchaseOrderItemObserver::class);
        TaxInvoice::observe(TaxInvoiceObserver::class);
        Product::observe(ProductObserver::class);
        Stock::observe(StockObserver::class);
    }
}
