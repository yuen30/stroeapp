<?php

namespace App\Console\Commands;

use App\Services\StockReservationService;
use Illuminate\Console\Command;

class CleanupExpiredReservations extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'reservations:cleanup';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'ลบการจองสต็อกที่หมดอายุ';

    /**
     * Execute the console command.
     */
    public function handle(StockReservationService $service): int
    {
        $this->info('กำลังลบการจองสต็อกที่หมดอายุ...');

        $count = $service->cleanupExpiredReservations();

        if ($count > 0) {
            $this->info("ลบการจองที่หมดอายุสำเร็จ: {$count} รายการ");
        } else {
            $this->info('ไม่พบการจองที่หมดอายุ');
        }

        return Command::SUCCESS;
    }
}
