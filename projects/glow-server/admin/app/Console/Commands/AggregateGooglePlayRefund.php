<?php

namespace App\Console\Commands;

use App\Services\AggregateGooglePlayRefundCommandService;
use Illuminate\Console\Command;

class AggregateGooglePlayRefund extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:aggregate-google-play-refunds';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Aggregate Google Play refunds.';

    public function __construct(
        private readonly AggregateGooglePlayRefundCommandService $aggregateGooglePlayRefundCommandService,
    ) {
        parent::__construct();
    }

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->aggregateGooglePlayRefundCommandService->exec();
    }
}
