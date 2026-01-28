<?php

namespace Database\Seeders\Dummies;

use App\Domain\Resource\Enums\LogCurrencyFreeTriggerType;
use App\Models\Log\LogCurrencyFree;
use Carbon\CarbonImmutable;
use Illuminate\Database\Seeder;
use WonderPlanet\Domain\Currency\Constants\CurrencyConstants;

/**
 * ダミーのLogCurrencyFreeを生成する(コマンドは下記)
 * sail admin artisan db:seed --class="Database\Seeders\Dummies\DummyLogCurrencyFreeSeeder"
 */
class DummyLogCurrencyFreeSeeder extends Seeder
{
    public int $numberOfRecords = 20000;
    public ?CarbonImmutable $start = null;
    public ?CarbonImmutable $end = null;

    /**
     * ダミーデータ生成
     */
    public function run(): void
    {
        $now = CarbonImmutable::now()->setTimezone('Asia/Tokyo');
        if ($this->start === null || $this->end === null) {
            $this->start = $now->subHour()->startOfHour();
            $this->end = $this->start->copy()->endOfHour();
        }
        $platforms = [CurrencyConstants::OS_PLATFORM_IOS, CurrencyConstants::OS_PLATFORM_ANDROID];
        $triggerTypes = [LogCurrencyFreeTriggerType::SHOP->value];
        $triggerIds = ['trigger_1', 'trigger_2', 'trigger_3', 'trigger_4', 'trigger_5'];

        for ($i = 1; $i <= $this->numberOfRecords; $i++) {
            $beforeIngameAmount = rand(0, 10000);
            $beforeBonusAmount = rand(0, 10000);
            $beforeRewardAmount = rand(0, 10000);

            $changeIngameAmount = rand(-500, 500);
            $changeBonusAmount = rand(-300, 300);
            $changeRewardAmount = rand(-200, 200);

            $currentIngameAmount = $beforeIngameAmount + $changeIngameAmount;
            $currentBonusAmount = $beforeBonusAmount + $changeBonusAmount;
            $currentRewardAmount = $beforeRewardAmount + $changeRewardAmount;
            $usrUserId = 'user_' . $now->format('Ymd') . $i;

            $insertData[] = [
                'id' => fake()->uuid(),
                'logging_no' => $i,
                'usr_user_id' => $usrUserId,
                'os_platform' => $platforms[array_rand($platforms)],
                'before_ingame_amount' => $beforeIngameAmount,
                'before_bonus_amount' => $beforeBonusAmount,
                'before_reward_amount' => $beforeRewardAmount,
                'change_ingame_amount' => $changeIngameAmount,
                'change_bonus_amount' => $changeBonusAmount,
                'change_reward_amount' => $changeRewardAmount,
                'current_ingame_amount' => $currentIngameAmount,
                'current_bonus_amount' => $currentBonusAmount,
                'current_reward_amount' => $currentRewardAmount,
                'trigger_type' => $triggerTypes[array_rand($triggerTypes)],
                'trigger_id' => $triggerIds[array_rand($triggerIds)],
                'trigger_name' => 'Trigger ' . rand(1, 10),
                'trigger_detail' => json_encode(['detail' => 'example detail']),
                'request_id_type' => 'type_' . rand(1, 5),
                'request_id' => 'req_' . $i,
                'nginx_request_id' => 'nginx_req_' . $i,
                'created_at' => CarbonImmutable::createFromTimestamp(
                    rand($this->start->timestamp, $this->end->timestamp)
                )->toDateTimeString(),
                'updated_at' => $now->toDateTimeString(),
            ];

            if ($i % 1000 === 0 || $i === $this->numberOfRecords) {
                LogCurrencyFree::query()->insert($insertData);
                $insertData = [];
            }
        }
    }
}
