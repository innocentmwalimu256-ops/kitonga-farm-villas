<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class PurgeTestSales extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'pos:purge-test-sales';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Purge test POS sales (SALE-00001, SALE-00002) and test honey records';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Searching for test POS sales...');

        $testSales = DB::table('sales')
            ->whereIn('reference', ['SALE-00001', 'SALE-00002'])
            ->orWhere(function ($q) {
                $q->where('id', '<=', 2);
            })
            ->get();

        $count = $testSales->count();

        foreach ($testSales as $sale) {
            DB::table('payments')->where('sale_id', $sale->id)->delete();
            DB::table('inventory_movements')
                ->where('reference_type', 'sales')
                ->where('reference_id', $sale->id)
                ->delete();
            DB::table('sale_items')->where('sale_id', $sale->id)->delete();
            DB::table('sales')->where('id', $sale->id)->delete();
            $this->line("Purged sale: {$sale->reference} (ID: {$sale->id})");
        }

        DB::table('payments')
            ->whereNull('booking_id')
            ->whereNull('sale_id')
            ->delete();

        $this->info("Purged {$count} test sales successfully from database and financial reports.");
        return 0;
    }
}
