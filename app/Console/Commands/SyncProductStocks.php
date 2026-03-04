<?php

namespace App\Console\Commands;

use App\Models\Product;
use App\Models\Stock;
use Illuminate\Console\Command;

class SyncProductStocks extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'stock:sync {--force : Force sync even if stock exists}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Sync stock records for all products';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $this->info('Starting stock synchronization...');
        $this->newLine();

        $products = Product::with('stocks')->get();
        $created = 0;
        $updated = 0;
        $skipped = 0;

        $progressBar = $this->output->createProgressBar($products->count());
        $progressBar->start();

        foreach ($products as $product) {
            $stock = $product->stocks()->first();

            if (!$stock) {
                // สร้าง Stock record ใหม่
                Stock::create([
                    'product_id' => $product->id,
                    'quantity' => $product->stock_quantity ?? 0,
                    'cost_price' => $product->cost_price ?? 0,
                    'selling_price' => $product->selling_price ?? 0,
                ]);
                $created++;
            } elseif ($this->option('force')) {
                // อัปเดต Stock record ที่มีอยู่ (ถ้าใช้ --force)
                $stock->update([
                    'quantity' => $product->stock_quantity ?? $stock->quantity,
                    'cost_price' => $product->cost_price ?? $stock->cost_price,
                    'selling_price' => $product->selling_price ?? $stock->selling_price,
                ]);
                $updated++;
            } else {
                $skipped++;
            }

            $progressBar->advance();
        }

        $progressBar->finish();
        $this->newLine(2);

        // แสดงสรุปผลลัพธ์
        $this->info('Stock synchronization completed!');
        $this->newLine();

        $this->table(
            ['Status', 'Count'],
            [
                ['Created', $created],
                ['Updated', $updated],
                ['Skipped', $skipped],
                ['Total Products', $products->count()],
            ]
        );

        if ($skipped > 0 && !$this->option('force')) {
            $this->newLine();
            $this->comment('💡 Tip: Use --force option to update existing stock records');
        }

        return Command::SUCCESS;
    }
}
