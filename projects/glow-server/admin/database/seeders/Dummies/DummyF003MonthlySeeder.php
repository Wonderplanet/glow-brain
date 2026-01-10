<?php

namespace Database\Seeders\Dummies;

use Carbon\CarbonImmutable;
use Illuminate\Database\Seeder;

/**
 * F003の日毎コマンドのダミーデータを生成する(コマンドは下記)
 * sail admin artisan db:seed --class="Database\Seeders\Dummies\DummyF003MonthlySeeder"
 */
class DummyF003MonthlySeeder extends Seeder
{
    /**
     * ダミーデータ生成
     */
    public function run(): void
    {
        ini_set('memory_limit', '1G');
        $numberOfRecords = 10000;
        $now = CarbonImmutable::now()->setTimezone('Asia/Tokyo');
        $start = $now->subMonth()->startOfMonth();
        $end = $start->copy()->endOfMonth();

        $seeder = new DummyMstStore();
        $seeder->run();
        
        $seeder = new DummyLogCurrencyPaidSeeder();
        $seeder->numberOfRecords = $numberOfRecords;
        $seeder->start = $start;
        $seeder->end = $end;
        $seeder->run();

        $seeder = new DummyLogStoreSeeder();
        $seeder->numberOfRecords = $numberOfRecords;
        $seeder->start = $start;
        $seeder->end = $end;
        $seeder->run();

    }
}
