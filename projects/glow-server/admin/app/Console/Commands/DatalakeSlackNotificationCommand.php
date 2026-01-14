<?php

namespace App\Console\Commands;

use App\Entities\Clock;
use App\Services\Datalake\DatalakeSlackNotificationCommandService;
use Illuminate\Console\Command;

class DatalakeSlackNotificationCommand extends Command
{
    public function __construct(
        private DatalakeSlackNotificationCommandService $service,
        private Clock $clock,
    ) {
        parent::__construct();
    }

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:datalake-slack-notification-command';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $executionTime = $this->clock->now();
        $this->service->exec($executionTime);
    }
}
