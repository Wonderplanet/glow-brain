<?php

namespace Tests\Feature\Domain\DailyBonus;

use App\Domain\DailyBonus\Models\UsrComebackBonusProgress;
use App\Domain\DailyBonus\Services\ComebackBonusUpdateService;
use App\Domain\Resource\Enums\RewardType;
use App\Domain\Resource\Mst\Models\MstComebackBonus;
use App\Domain\Resource\Mst\Models\MstComebackBonusSchedule;
use App\Domain\Resource\Mst\Models\MstDailyBonusReward;
use App\Domain\Resource\Mst\Models\MstUserLevel;
use App\Domain\User\Constants\UserConstant;
use App\Domain\User\Models\UsrUserParameter;
use Carbon\CarbonImmutable;
use Tests\TestCase;

class ComebackBonusUpdateServiceTest extends TestCase
{
    private ComebackBonusUpdateService $comebackBonusUpdateService;

    protected function setUp(): void
    {
        parent::setUp();

        $this->comebackBonusUpdateService = app(ComebackBonusUpdateService::class);
    }

    private function setUpFixtures(string $now): CarbonImmutable
    {
        $now = $this->fixTime($now);
        MstComebackBonus::factory()->createMany([
            [
                'id' => 'bonus_1',
                'mst_comeback_bonus_schedule_id' => 'schedule_1',
                'login_day_count' => 1,
                'mst_daily_bonus_reward_group_id' => 'reward_group_1',
            ],
            [
                'id' => 'bonus_2',
                'mst_comeback_bonus_schedule_id' => 'schedule_1',
                'login_day_count' => 2,
                'mst_daily_bonus_reward_group_id' => 'reward_group_2',
            ],
            [
                'id' => 'bonus_3',
                'mst_comeback_bonus_schedule_id' => 'schedule_1',
                'login_day_count' => 3,
                'mst_daily_bonus_reward_group_id' => 'reward_group_3',
            ],
        ]);

        MstComebackBonusSchedule::factory()->createMany([
            [
                'id' => 'schedule_1',
                'inactive_condition_days' => '14',
                'duration_days' => 5,
                'start_at' => $now->toDateTimeString(),
                'end_at' => $now->addDays(30)->toDateTimeString(),
            ],
            [
                'id' => 'schedule_2',
                'inactive_condition_days' => '14',
                'duration_days' => 5,
                'start_at' => $now->addDays(5)->toDateTimeString(),
                'end_at' => $now->addDays(7)->toDateTimeString(),
            ],
        ]);

        MstDailyBonusReward::factory()->createMany([
            [
                'id' => 'reward_1',
                'group_id' => 'reward_group_1',
                'resource_type' => RewardType::COIN->value,
                'resource_id' => 'item_1',
                'resource_amount' => 1,
            ],
            [
                'id' => 'reward_2',
                'group_id' => 'reward_group_2',
                'resource_type' => RewardType::COIN->value,
                'resource_id' => null,
                'resource_amount' => 10,
            ],
            [
                'id' => 'reward_3',
                'group_id' => 'reward_group_3',
                'resource_type' => RewardType::COIN->value,
                'resource_id' => null,
                'resource_amount' => 100,
            ],
        ]);
        MstUserLevel::factory()->createMany([
            ['level' => 1, 'stamina' => 10, 'exp' => 0],
            ['level' => 2, 'stamina' => 10, 'exp' => 10],
            ['level' => 3, 'stamina' => 10, 'exp' => 100000],
        ]);

        return $now;
    }

    public function test_updateStatuses_ステータス更新ができている()
    {
        // Setup
        $usrUserId = $this->createUsrUser()->getId();
        $now1 = $this->setUpFixtures('2024-05-14 15:00:00');
        UsrUserParameter::factory()->create([
            'usr_user_id' => $usrUserId,
            'coin' => 0,
        ]);

        // Exercise
        $this->comebackBonusUpdateService->updateStatuses($usrUserId, UserConstant::PLATFORM_IOS, $now1, 15);
        $this->saveAll();
        $now2 = $this->fixTime($now1->addDay());
        $this->comebackBonusUpdateService->updateStatuses($usrUserId, UserConstant::PLATFORM_IOS, $now2, 0);
        $this->saveAll();
        $now3 = $this->fixTime($now2->addDay());
        $this->comebackBonusUpdateService->updateStatuses($usrUserId, UserConstant::PLATFORM_IOS, $now3, 0);
        $this->saveAll();

        // Verify

        // 報酬受け取り済みであることを確認
        $this->checkUsrProgress(
            $usrUserId,
            'schedule_1',
            isExist:true,
            expectedProgress: 3,
            expectedLatestUpdateAt: $now3->toDateTimeString(),
        );
        $usrParameter = UsrUserParameter::query()->where('usr_user_id', $usrUserId)->first();
        $this->assertEquals(111, $usrParameter->getCoin());
    }

    public function test_updateStatuses_条件日数に達していない場合もらえないこと()
    {
        // Setup
        $usrUserId = $this->createUsrUser()->getId();
        $now1 = $this->setUpFixtures('2024-05-14 15:00:00');
        UsrUserParameter::factory()->create([
            'usr_user_id' => $usrUserId,
            'coin' => 0,
        ]);

        // Exercice
        $this->comebackBonusUpdateService->updateStatuses($usrUserId, UserConstant::PLATFORM_IOS, $now1, 14);
        $this->saveAll();
        $now2 = $this->fixTime($now1->addDays(2));
        $this->comebackBonusUpdateService->updateStatuses($usrUserId, UserConstant::PLATFORM_IOS, $now2, 0);
        $this->saveAll();

        // Verify

        // 未開放であることを確認
        $this->checkUsrProgress(
            $usrUserId,
            'schedule_1',
            isExist:false,
            expectedProgress: null,
            expectedLatestUpdateAt: null,
        );
        $usrParameter = UsrUserParameter::query()->where('usr_user_id', $usrUserId)->first();
        $this->assertEquals(0, $usrParameter->getCoin());
    }

    public function test_updateStatuses_イベント期間外の場合()
    {
        // Setup
        $usrUserId = $this->createUsrUser()->getId();
        $now = $this->setUpFixtures('2024-05-14 15:00:00');
        $now = $now->addDays(31);
        UsrUserParameter::factory()->create([
            'usr_user_id' => $usrUserId,
            'coin' => 0,
        ]);

        // Exercise
        $this->comebackBonusUpdateService->updateStatuses($usrUserId, UserConstant::PLATFORM_IOS, $now, 15);
        $this->saveAll();

        // Verify

        // 未獲得であることを確認
        $this->checkUsrProgress(
            $usrUserId,
            'schedule_1',
            isExist:false,
            expectedProgress: null,
            expectedLatestUpdateAt: null,
        );
        $usrParameter = UsrUserParameter::query()->where('usr_user_id', $usrUserId)->first();
        $this->assertEquals(0, $usrParameter->getCoin());
    }

    public function test_updateStatuses_日跨ぎ確認()
    {
        // Setup
        $usrUserId = $this->createUsrUser()->getId();
        $now = $this->setUpFixtures('2024-05-14 19:00:00');
        UsrUserParameter::factory()->create([
            'usr_user_id' => $usrUserId,
            'coin' => 0,
        ]);

        // Exercise
        $this->comebackBonusUpdateService->updateStatuses($usrUserId, UserConstant::PLATFORM_IOS, $now, 15);
        $this->saveAll();
        // 日跨ぎする
        $now2 = $this->fixTime($now->addDay());
        $this->comebackBonusUpdateService->updateStatuses($usrUserId, UserConstant::PLATFORM_IOS, $now2, 0);
        $this->saveAll();
        // 日跨ぎしない
        $now3 = $this->fixTime($now2->addDay()->subSecond());
        $this->comebackBonusUpdateService->updateStatuses($usrUserId, UserConstant::PLATFORM_IOS, $now3, 0);
        $this->saveAll();

        // Verify

        // 報酬受け取り済みであることを確認
        $this->checkUsrProgress(
            $usrUserId,
            'schedule_1',
            isExist:true,
            expectedProgress: 2,
            expectedLatestUpdateAt: $now2->toDateTimeString(),
        );
        $usrParameter = UsrUserParameter::query()->where('usr_user_id', $usrUserId)->first();
        $this->assertEquals(11, $usrParameter->getCoin());
    }


    public function test_updateStatuses_期間中に再度報酬が受け取れていることを確認()
    {
        // Setup
        $usrUserId = $this->createUsrUser()->getId();
        $now = $this->setUpFixtures('2024-05-14 15:00:00');
        UsrUserParameter::factory()->create([
            'usr_user_id' => $usrUserId,
            'coin' => 0,
        ]);

        // Exercise
        $this->comebackBonusUpdateService->updateStatuses($usrUserId, UserConstant::PLATFORM_IOS, $now, 15);
        $this->saveAll();
        $now2 = $this->fixTime($now->addDay());
        $this->comebackBonusUpdateService->updateStatuses($usrUserId, UserConstant::PLATFORM_IOS, $now2, 0);
        $this->saveAll();
        $now3 = $this->fixTime($now2->addDays(15));
        $this->comebackBonusUpdateService->updateStatuses($usrUserId, UserConstant::PLATFORM_IOS, $now3, 15);
        $this->saveAll();
        $now4 = $this->fixTime($now3->addDay());
        $this->comebackBonusUpdateService->updateStatuses($usrUserId, UserConstant::PLATFORM_IOS, $now4, 0);
        $this->saveAll();

        // Verify

        // 報酬受け取り済みであることを確認
        $usrParameter = UsrUserParameter::query()->where('usr_user_id', $usrUserId)->first();
        $this->assertEquals(22, $usrParameter->getCoin());
    }

    public function test_updateStatuses_期間内で報酬を全て受け取った後の期間もログインできること()
    {
        // Setup
        $usrUserId = $this->createUsrUser()->getId();
        $now = $this->setUpFixtures('2024-05-14 15:00:00');
        UsrUserParameter::factory()->create([
            'usr_user_id' => $usrUserId,
            'coin' => 0,
        ]);

        // Exercice
        $this->comebackBonusUpdateService->updateStatuses($usrUserId, UserConstant::PLATFORM_IOS, $now, 15);
        $this->saveAll();
        for ($i = 0; $i < 4; $i++) {
            $now = $this->fixTime($now->addDay());
            $this->comebackBonusUpdateService->updateStatuses($usrUserId, UserConstant::PLATFORM_IOS, $now, 0);
            $this->saveAll();
        }

        // Verify

        // エラーにならず、報酬受け取り済みであることを確認
        $usrParameter = UsrUserParameter::query()->where('usr_user_id', $usrUserId)->first();
        $this->assertEquals(111, $usrParameter->getCoin());
    }

    private function setUpFixturesMultiEvents(string $now): CarbonImmutable
    {
        $now = $this->fixTime($now);
        MstComebackBonus::factory()->createMany([
            [
                'id' => 'bonus_1',
                'mst_comeback_bonus_schedule_id' => 'schedule_1',
                'login_day_count' => 1,
                'mst_daily_bonus_reward_group_id' => 'reward_group_1',
            ],
            [
                'id' => 'bonus_2',
                'mst_comeback_bonus_schedule_id' => 'schedule_1',
                'login_day_count' => 2,
                'mst_daily_bonus_reward_group_id' => 'reward_group_2',
            ],
            [
                'id' => 'bonus_3',
                'mst_comeback_bonus_schedule_id' => 'schedule_1',
                'login_day_count' => 3,
                'mst_daily_bonus_reward_group_id' => 'reward_group_3',
            ],
            [
                'id' => 'bonus_4',
                'mst_comeback_bonus_schedule_id' => 'schedule_2',
                'login_day_count' => 1,
                'mst_daily_bonus_reward_group_id' => 'reward_group_1',
            ],
        ]);

        MstComebackBonusSchedule::factory()->createMany([
            [
                'id' => 'schedule_1',
                'inactive_condition_days' => 14,
                'start_at' => $now->toDateTimeString(),
                'end_at' => $now->addDays(4)->toDateTimeString(),
            ],
            [
                'id' => 'schedule_2',
                'inactive_condition_days' => 24,
                'start_at' => $now->toDateTimeString(),
                'end_at' => $now->addDays(4)->toDateTimeString(),
            ],
        ]);

        MstDailyBonusReward::factory()->createMany([
            [
                'id' => 'reward_1',
                'group_id' => 'reward_group_1',
                'resource_type' => RewardType::COIN->value,
                'resource_id' => 'item_1',
                'resource_amount' => 1,
            ],
            [
                'id' => 'reward_2',
                'group_id' => 'reward_group_2',
                'resource_type' => RewardType::COIN->value,
                'resource_id' => null,
                'resource_amount' => 10,
            ],
            [
                'id' => 'reward_3',
                'group_id' => 'reward_group_3',
                'resource_type' => RewardType::COIN->value,
                'resource_id' => null,
                'resource_amount' => 100,
            ],
        ]);
        MstUserLevel::factory()->createMany([
            ['level' => 1, 'stamina' => 10, 'exp' => 0],
            ['level' => 2, 'stamina' => 10, 'exp' => 10],
            ['level' => 3, 'stamina' => 10, 'exp' => 100000],
        ]);

        return $now;
    }

    public function test_updateStatuses_複数カムバックログボが開催されている場合()
    {
        // Setup
        $usrUserId = $this->createUsrUser()->getId();
        $now1 = $this->setUpFixturesMultiEvents('2024-05-14 15:00:00');
        UsrUserParameter::factory()->create([
            'usr_user_id' => $usrUserId,
            'coin' => 0,
        ]);

        // Exercice
        $this->comebackBonusUpdateService->updateStatuses($usrUserId, UserConstant::PLATFORM_IOS, $now1, 25);
        $this->saveAll();
        $now2 = $this->fixTime($now1->addDays(2));
        $this->comebackBonusUpdateService->updateStatuses($usrUserId, UserConstant::PLATFORM_IOS, $now2, 0);
        $this->saveAll();

        // Verify

        $this->checkUsrProgress(
            $usrUserId,
            'schedule_1',
            isExist:true,
            expectedProgress: 2,
            expectedLatestUpdateAt: $now2->toDateTimeString(),
        );
        $this->checkUsrProgress(
            $usrUserId,
            'schedule_2',
            isExist:true,
            expectedProgress: 2,
            expectedLatestUpdateAt: $now2->toDateTimeString(),
        );
        $usrParameter = UsrUserParameter::query()->where('usr_user_id', $usrUserId)->first();
        $this->assertEquals(12, $usrParameter->getCoin());
    }

    public function test_updateStatuses_報酬受取期間を過ぎたら受け取れない()
    {
        // Setup
        $usrUserId = $this->createUsrUser()->getId();
        $now1 = $this->setUpFixtures('2024-05-14 15:00:00');
        UsrUserParameter::factory()->create([
            'usr_user_id' => $usrUserId,
            'coin' => 0,
        ]);

        // 初日を受け取る
        // Exercise
        $this->comebackBonusUpdateService->updateStatuses($usrUserId, UserConstant::PLATFORM_IOS, $now1, 15);
        $this->saveAll();

        // Verify
        // 初日報酬受け取り済みであることを確認
        $this->checkUsrProgress(
            $usrUserId,
            'schedule_1',
            isExist:true,
            expectedProgress: 1,
            expectedLatestUpdateAt: $now1->toDateTimeString(),
        );
        $usrParameter = UsrUserParameter::query()->where('usr_user_id', $usrUserId)->first();
        $this->assertEquals(1, $usrParameter->getCoin());

        // 二日目を受け取る
        // Exercise
        $now2 = $this->fixTime($now1->addDay());
        $this->comebackBonusUpdateService->updateStatuses($usrUserId, UserConstant::PLATFORM_IOS, $now2, 0);
        $this->saveAll();

        // Verify
        //  二日目報酬受け取り済みであることを確認
        $this->checkUsrProgress(
            $usrUserId,
            'schedule_1',
            isExist:true,
            expectedProgress: 2,
            expectedLatestUpdateAt: $now2->toDateTimeString(),
        );
        $usrParameter = UsrUserParameter::query()->where('usr_user_id', $usrUserId)->first();
        $this->assertEquals(11, $usrParameter->getCoin());

        // 期間を過ぎると受け取れない
        // Exercise
        $now3 = $this->fixTime($now2->addDays(5));
        $this->comebackBonusUpdateService->updateStatuses($usrUserId, UserConstant::PLATFORM_IOS, $now3, 0);
        $this->saveAll();

        // Verify
        // 期間を過ぎてるので、進捗は変わらない
        $this->checkUsrProgress(
            $usrUserId,
            'schedule_1',
            isExist:true,
            expectedProgress: 2,
            expectedLatestUpdateAt: $now2->toDateTimeString(),
        );
        $usrParameter->refresh();
        $this->assertEquals(11, $usrParameter->getCoin());
    }

    private function checkUsrProgress(
        string $usrUserId,
        string $mstComebackBonusScheduleId,
        bool $isExist,
        ?int $expectedProgress,
        ?string $expectedLatestUpdateAt
    ): void {
        $usrProgress = UsrComebackBonusProgress::query()
            ->where('usr_user_id', $usrUserId)
            ->where('mst_comeback_bonus_schedule_id', $mstComebackBonusScheduleId)
            ->first();

        if ($isExist) {
            $this->assertNotNull($usrProgress, 'not exist');
        } else {
            $this->assertNull($usrProgress, 'exist');
            return;
        }
        if ($expectedProgress !== null) {
            $this->assertEquals($expectedProgress, $usrProgress->getProgress(), 'progress');
        }

        if ($expectedLatestUpdateAt !== null) {
            $this->assertEquals($expectedLatestUpdateAt, $usrProgress->getLatestUpdateAt(), 'latest_update_at');
        }
    }
}
