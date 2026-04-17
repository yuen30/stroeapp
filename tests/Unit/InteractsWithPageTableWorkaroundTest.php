<?php

namespace Tests\Unit;

use App\Filament\Pages\Reports\Widgets\GoodsReceiptOverview;
use Filament\Tables\Contracts\HasTable;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class InteractsWithPageTableWorkaroundTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function it_normalizes_null_column_searches_before_assigning_them_to_the_page_table(): void
    {
        $widget = new class extends GoodsReceiptOverview
        {
            public function resolveTablePage(): HasTable
            {
                return $this->getTablePageInstance();
            }
        };

        $widget->tableColumnSearches = null;

        $page = $widget->resolveTablePage();

        $this->assertSame([], $page->tableColumnSearches);
    }

    #[Test]
    public function it_normalizes_null_paginators_before_assigning_them_to_the_page_table(): void
    {
        $widget = new class extends GoodsReceiptOverview
        {
            public function resolveTablePage(): HasTable
            {
                return $this->getTablePageInstance();
            }
        };

        $widget->paginators = null;

        $page = $widget->resolveTablePage();

        $this->assertSame([], $page->paginators);
    }
}
