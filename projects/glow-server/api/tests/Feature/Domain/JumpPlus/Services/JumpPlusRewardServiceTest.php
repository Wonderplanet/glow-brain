<?php

namespace Feature\Domain\JumpPlus\Services;

use App\Domain\JumpPlus\Enums\DynJumpPlusRewardStatus;
use App\Domain\JumpPlus\Repositories\DynJumpPlusRewardRepository;
use App\Domain\JumpPlus\Services\JumpPlusRewardService;
use App\Domain\Resource\Entities\JumpPlusRewardBundle;
use App\Domain\Resource\Entities\Rewards\JumpPlusReward;
use App\Domain\Resource\Enums\RewardType;
use App\Domain\Resource\Mng\Models\MngJumpPlusReward;
use App\Domain\Resource\Mng\Models\MngJumpPlusRewardSchedule;
use App\Domain\User\Delegators\UserDelegator;
use Tests\Feature\Domain\JumpPlus\Entities\TestDynJumpPlusRewardEntity;
use Tests\TestCase;

class JumpPlusRewardServiceTest extends TestCase
{
    public function setUp(): void
    {
        parent::setUp();
    }

    public function test_getReceivableRewards_受取可能な報酬のみを取得できる()
    {
        // Setup
        $bnUserId = 'test_bn_user_id';
        $usrUser = $this->createUsrUser([
            'bn_user_id' => $bnUserId,
        ]);
        $now = $this->fixTime();

        // mst
        $mngRewardSchedules = collect([
            MngJumpPlusRewardSchedule::factory()->create(['id' => 'reward1', 'group_id' => 'group1', 'start_at' => '2021-01-01 00:00:00', 'end_at' => '2038-01-01 00:00:00']),
            MngJumpPlusRewardSchedule::factory()->create(['id' => 'reward2', 'group_id' => 'group2', 'start_at' => '2021-01-01 00:00:00', 'end_at' => '2038-01-01 00:00:00']),
            MngJumpPlusRewardSchedule::factory()->create(['id' => 'reward3', 'group_id' => 'group3', 'start_at' => '2021-01-01 00:00:00', 'end_at' => '2038-01-01 00:00:00']),
        ])->map->toEntity()->keyBy->getId();
        $groupedMngRewards = collect([
            MngJumpPlusReward::factory()->create(['group_id' => 'group1', 'resource_type' => RewardType::COIN->value, 'resource_id' => null, 'resource_amount' => 1]),
            MngJumpPlusReward::factory()->create(['group_id' => 'group2', 'resource_type' => RewardType::COIN->value, 'resource_id' => null, 'resource_amount' => 10]),
            MngJumpPlusReward::factory()->create(['group_id' => 'group2', 'resource_type' => RewardType::COIN->value, 'resource_id' => null, 'resource_amount' => 100]),
            MngJumpPlusReward::factory()->create(['group_id' => 'group3', 'resource_type' => RewardType::COIN->value, 'resource_id' => null, 'resource_amount' => 1000]),
        ])->map->toEntity()->groupBy->getGroupId();

        // usr

        // dyn
        $dynRewards = collect([
            new TestDynJumpPlusRewardEntity($bnUserId, 'reward1', DynJumpPlusRewardStatus::NOT_RECEIVED,),
            new TestDynJumpPlusRewardEntity($bnUserId, 'reward2', DynJumpPlusRewardStatus::NOT_RECEIVED,),
            new TestDynJumpPlusRewardEntity($bnUserId, 'reward3', DynJumpPlusRewardStatus::RECEIVED,),
        ])->keyBy->getMngJumpPlusRewardScheduleId();

        // mock
        // DynamoDBアクセス。reward1, reward2が受取可として返す
        $this->app->when(JumpPlusRewardService::class)
            ->needs(DynJumpPlusRewardRepository::class)
            ->give(function () use ($dynRewards) {
                $dynJumpPlusRewardRepository = $this->createMock(DynJumpPlusRewardRepository::class);
                $dynJumpPlusRewardRepository->method('getByMngJumpPlusRewardScheduleIds')->willReturn($dynRewards);
                return $dynJumpPlusRewardRepository;
            });
        // UserDelegator。bn_user_idを合わせるため
        $this->app->when(UserDelegator::class)
            ->needs(UserDelegator::class)
            ->give(function () use ($usrUser) {
                $userDelegator = $this->createMock(UserDelegator::class);
                $userDelegator->method('getUsrUserByUsrUserId')->willReturn($usrUser);
                return $userDelegator;
            });

        $service = $this->app->make(JumpPlusRewardService::class);

        // Exercice
        $result = $service->getReceivableRewards($usrUser->getId(), $now);

        // Verify
        $this->assertCount(2, $result);

        // 受け取り済み報酬（reward3）が結果に含まれていないことを確認
        $mngScheduleIds = $result->map(function (JumpPlusRewardBundle $bundle) {
            return $bundle->getDynJumpPlusReward()->getMngJumpPlusRewardScheduleId();
        })->all();
        $this->assertNotContains('reward3', $mngScheduleIds);

        $result = $result->keyBy(function (JumpPlusRewardBundle $bundle) {
            return $bundle->getDynJumpPlusReward()->getMngJumpPlusRewardScheduleId();
        });

        // reward1
        $actual = $result['reward1'];
        $this->assertInstanceOf(JumpPlusRewardBundle::class, $actual);
        //   DynJumpPlusReward
        $this->assertEquals($bnUserId, $actual->getDynJumpPlusReward()->getBnUserId());
        $this->assertEquals('reward1', $actual->getDynJumpPlusReward()->getMngJumpPlusRewardScheduleId());
        $this->assertEquals(DynJumpPlusRewardStatus::NOT_RECEIVED->value, $actual->getDynJumpPlusReward()->getStatus());
        //   JumpPlusRewards
        $this->assertCount(1, $actual->getJumpPlusRewards());
        $actual = $result['reward1']->getJumpPlusRewards()->first();
        $this->assertInstanceOf(JumpPlusReward::class, $actual);
        $this->assertEquals(RewardType::COIN->value, $actual->getType());
        $this->assertNull($actual->getResourceId());
        $this->assertEquals(1, $actual->getAmount());
        $this->assertEquals('reward1', $actual->getMngJumpPlusRewardScheduleId());
        $this->assertEquals('2038-01-01 00:00:00', $actual->getReceiveExpireAt());

        // reward2
        $actual = $result['reward2'];
        $this->assertInstanceOf(JumpPlusRewardBundle::class, $actual);
        //   DynJumpPlusReward
        $this->assertEquals($bnUserId, $actual->getDynJumpPlusReward()->getBnUserId());
        $this->assertEquals('reward2', $actual->getDynJumpPlusReward()->getMngJumpPlusRewardScheduleId());
        $this->assertEquals(DynJumpPlusRewardStatus::NOT_RECEIVED->value, $actual->getDynJumpPlusReward()->getStatus());
        //   JumpPlusRewards
        $this->assertCount(2, $actual->getJumpPlusRewards());
        //     1つ目
        $actual = $result['reward2']->getJumpPlusRewards()->first();
        $this->assertInstanceOf(JumpPlusReward::class, $actual);
        $this->assertEquals(RewardType::COIN->value, $actual->getType());
        $this->assertNull($actual->getResourceId());
        $this->assertEquals('reward2', $actual->getMngJumpPlusRewardScheduleId());
        $this->assertEquals('2038-01-01 00:00:00', $actual->getReceiveExpireAt());
        //     2つ目
        $actual = $result['reward2']->getJumpPlusRewards()->last();
        $this->assertInstanceOf(JumpPlusReward::class, $actual);
        $this->assertEquals(RewardType::COIN->value, $actual->getType());
        $this->assertNull($actual->getResourceId());
        $this->assertEquals('reward2', $actual->getMngJumpPlusRewardScheduleId());
        $this->assertEquals('2038-01-01 00:00:00', $actual->getReceiveExpireAt());
        //     報酬数を確認
        $expectedAmounts = [10, 100];
        $actualAmounts = $result['reward2']->getJumpPlusRewards()->map->getAmount()->all();
        sort($expectedAmounts);
        sort($actualAmounts);
        $this->assertEquals($expectedAmounts, $actualAmounts);
    }
}
