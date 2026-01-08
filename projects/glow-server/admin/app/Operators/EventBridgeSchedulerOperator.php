<?php

namespace App\Operators;

use App\Traits\NotificationTrait;
use Aws\Scheduler\SchedulerClient;

class EventBridgeSchedulerOperator
{
    use NotificationTrait;

    public function __construct()
    {
    }

    private function getClient(array $config): SchedulerClient
    {
        return new SchedulerClient([
            'region'  => $config['region'],
            'version' => 'latest',
            'credentials' => [
                'key'    => $config['key'],
                'secret' => $config['secret'],
            ]
        ]);
    }

    public function getSchedule(array $config): array
    {
        $client = $this->getClient($config);

        $scheduleName = $config['schedule_name'] ?? null;
        if (is_null($scheduleName)) {
            return [];
        }

        try {
            $result = $client->getSchedule(['Name' => $scheduleName])->toArray();
            \Log::info('EventBridge Scheduler getSchedule Success: ' . json_encode($result));
            return $result;
        } catch (\Exception $e) {
            \Log::error('EventBridge Scheduler getSchedule Error: ' . $e->getMessage());
            $this->sendDangerNotification('EventBridge Scheduler getSchedule Error', $e->getMessage());
            return [];
        }
    }

    public function updateSchedule(array $config, array $args): void
    {
        $client = $this->getClient($config);

        try {
            $client->updateSchedule($args);
            \Log::info('EventBridge Scheduler updateSchedule Success: ' . json_encode($args));
        } catch (\Exception $e) {
            \Log::error('EventBridge Scheduler updateSchedule Error: ' . $e->getMessage());
            $this->sendDangerNotification('EventBridge Scheduler updateSchedule Error', $e->getMessage());
        }
    }
}
