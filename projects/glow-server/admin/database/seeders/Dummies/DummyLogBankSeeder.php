<?php

namespace Database\Seeders\Dummies;

use App\Domain\Common\Enums\UserStatus;
use App\Models\Log\LogBank;
use App\Models\Usr\UsrUser;
use Carbon\CarbonImmutable;
use Illuminate\Database\Seeder;

/**
 * ダミーのLogBankを生成する(コマンドは下記)
 * sail admin artisan db:seed --class="Database\Seeders\Dummies\DummyLogBankSeeder"
 */
class DummyLogBankSeeder extends Seeder
{
    public int $numberOfRecords = 20000;

    /**
     * ダミーデータ生成
     */
    public function run(): void
    {
        $now = CarbonImmutable::now()->setTimezone('Asia/Tokyo');
        $startOfHour = $now->subHour()->startOfHour();
        $endOfHour = $startOfHour->copy()->endOfHour();
        $countryCodes = ['JP', 'US', 'FR', 'DE'];
        $eventIds = ['100', '200', '300'];
        $loggingNo = 0;
        for ($i = 1; $i <= $this->numberOfRecords; $i++, $loggingNo++) {
            $usrUserId = $now->format('Ymd') . $i;
            $usrUserInsertData[] = [
                'id' => $usrUserId,
                'status' => UserStatus::NORMAL->value,
                'tutorial_status' => '',
                'tos_version' => 0,
                'privacy_policy_version' => 0,
                'bn_user_id' => rand(0, 10) ? null : fake()->uuid(),
                'suspend_end_at' => null,
                'game_start_at' => $now->toDateTimeString(),
                'created_at' => $now->subDays(rand(0, 100))->toDateTimeString(),
                'updated_at' => $now->toDateTimeString(),
            ];

            $logBankInsertData[] = [
                'id' => fake()->uuid(),
                'usr_user_id' => $usrUserId,
                'nginx_request_id' => 'nginx_req_' . $i,
                'request_id' => 'req_' . $i,
                'logging_no' => $i,
                'event_id' => $eventIds[array_rand($eventIds)],
                'platform_user_id' => fake()->uuid(),
                'user_first_created_at' => $now->subDays(rand(0, 100))->toDateTimeString(),
                'user_agent' => 'Mozilla/5.0',
                'os_platform' => rand(1, 2),
                'os_version' => '10.' . rand(0, 9),
                'country_code' => $countryCodes[array_rand($countryCodes)],
                'ad_id' => "",
                'request_at' => $now->subHours(rand(0, 100))->toDateTimeString(),
                'created_at' => CarbonImmutable::createFromTimestamp(
                    rand($startOfHour->timestamp, $endOfHour->timestamp),
                    'Asia/Tokyo',
                )->toDateTimeString(),
                'updated_at' => $now->toDateTimeString(),
            ];

            if ($i % 1000 === 0 || $i === $this->numberOfRecords) {
                UsrUser::query()->upsert(
                    $usrUserInsertData,
                    ['usr_user_id'],
                    ['status', 'tutorial_status', 'tos_version', 'privacy_policy_version', 'bn_user_id', 'suspend_end_at', 'game_start_at', 'created_at', 'updated_at']
                );
                $usrUserInsertData = [];
                LogBank::query()->insert($logBankInsertData);
                $logBankInsertData = [];
            }
        }
    }
}
