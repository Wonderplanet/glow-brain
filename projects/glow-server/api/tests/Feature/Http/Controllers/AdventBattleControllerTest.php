<?php

namespace Tests\Feature\Http\Controllers;

use App\Domain\AdventBattle\Enums\AdventBattleRewardCategory;
use App\Domain\AdventBattle\Enums\AdventBattleType;
use App\Domain\AdventBattle\Models\UsrAdventBattle;
use App\Domain\AdventBattle\UseCases\AdventBattleAbortUseCase;
use App\Domain\AdventBattle\UseCases\AdventBattleCleanupUseCase;
use App\Domain\AdventBattle\UseCases\AdventBattleEndUseCase;
use App\Domain\AdventBattle\UseCases\AdventBattleReceiveMaxScoreRewardUseCase;
use App\Domain\AdventBattle\UseCases\AdventBattleStartUseCase;
use App\Domain\AdventBattle\UseCases\AdventBattleTopUseCase;
use App\Domain\Common\Constants\System;
use App\Domain\Common\Utils\CacheKeyUtil;
use App\Domain\Emblem\Models\UsrEmblem;
use App\Domain\Item\Models\Eloquent\UsrItem;
use App\Domain\Resource\Entities\Rewards\AdventBattleAlwaysClearReward;
use App\Domain\Resource\Entities\Rewards\AdventBattleFirstClearReward;
use App\Domain\Resource\Entities\Rewards\AdventBattleMaxScoreReward;
use App\Domain\Resource\Entities\Rewards\AdventBattleRaidTotalScoreReward;
use App\Domain\Resource\Entities\Rewards\AdventBattleRandomClearReward;
use App\Domain\Resource\Entities\Rewards\AdventBattleRankReward;
use App\Domain\Resource\Enums\EncyclopediaCollectStatus;
use App\Domain\Resource\Enums\RewardType;
use App\Domain\Resource\Enums\UnreceivedRewardReason;
use App\Domain\Resource\Mst\Models\MstAdventBattle;
use App\Domain\User\Models\UsrUser;
use App\Domain\User\Models\UsrUserProfile;
use App\Exceptions\HttpStatusCode;
use App\Http\Responses\Data\UserLevelUpData;
use App\Http\Responses\Data\UsrParameterData;
use App\Http\Responses\ResultData\AdventBattleAbortResultData;
use App\Http\Responses\ResultData\AdventBattleCleanupResultData;
use App\Http\Responses\ResultData\AdventBattleEndResultData;
use App\Http\Responses\ResultData\AdventBattleReceiveMaxScoreRewardResultData;
use App\Http\Responses\ResultData\AdventBattleStartResultData;
use App\Http\Responses\ResultData\AdventBattleTopResultData;
use Illuminate\Support\Facades\Redis;
use Mockery\MockInterface;
use Tests\Support\Traits\TestLogTrait;

class AdventBattleControllerTest extends BaseControllerTestCase
{
    use TestLogTrait;

    protected string $baseUrl = '/api/advent_battle/';

    private function createMstTestData(): void
    {
        MstAdventBattle::factory()->createMany([
            [
                'id' => '10',
                'advent_battle_type' => AdventBattleType::SCORE_CHALLENGE->value,
                'start_at' => '2024-01-01 00:00:00',
                'end_at' => '2024-03-01 00:00:00',
            ],
            [
                'id' => '11',
                'advent_battle_type' => AdventBattleType::RAID->value,
                'start_at' => '2024-03-01 00:00:00',
                'end_at' => '2024-06-01 00:00:00',
            ],
        ]);
    }

    public function test_top_リクエストを送ると200OKが返ることを確認する()
    {
        $usrUser = $this->createUsrUser();
        $adventBattleRaidTotalScoreRewards = collect();
        $adventBattleRaidTotalScoreRewards->add(new AdventBattleRaidTotalScoreReward(
            RewardType::ITEM->value,
            '1',
            1,
            'advent_battle1',
            'advent_battle_reward_group1',
            'advent_battle_reward1',
            AdventBattleRewardCategory::RAID_TOTAL_SCORE->value,
        ));
        $adventBattleMaxScoreRewards = collect();
        $adventBattleMaxScoreRewards->add(new AdventBattleMaxScoreReward(
            RewardType::ITEM->value,
            '2',
            2,
            'advent_battle1',
            'advent_battle_reward_group2',
            'advent_battle_reward2',
            AdventBattleRewardCategory::MAX_SCORE->value,
        ));
        $usrParameter = new UsrParameterData(1, 2, 3, 4, null, 6, 7, 8,);
        $usrItems = collect();
        $usrEmblems = collect();
        for ($i = 1; $i <= 3; $i++) {
            $usrItems->push(
                UsrItem::factory()->create([
                    'usr_user_id' => $usrUser->getId(),
                    'mst_item_id' => (string)$i,
                    'amount' => $i,
                ])
            );
            $usrEmblems->push(
                UsrEmblem::factory()->create([
                    'usr_user_id' => $usrUser->getId(),
                    'mst_emblem_id' => (string)$i,
                    'is_new_encyclopedia' => EncyclopediaCollectStatus::IS_NEW->value,
                ])
            );
        }

        // Setup
        $resultData = new AdventBattleTopResultData(
            $adventBattleRaidTotalScoreRewards,
            $adventBattleMaxScoreRewards,
            $usrParameter,
            $usrItems,
            $usrEmblems,
        );
        $this->mock(AdventBattleTopUseCase::class, function (MockInterface $mock) use ($resultData) {
            $mock->shouldReceive('exec')->andReturn($resultData);
        });

        // Exercise
        $requestData = ['mstAdventBattleId' => '10'];
        $response = $this->withHeaders([
            System::HEADER_PLATFORM => System::PLATFORM_IOS,
        ])->sendRequest('top', $requestData);

        // Verify
        $response->assertStatus(HttpStatusCode::SUCCESS);
        $this->assertEquals(AdventBattleRewardCategory::RAID_TOTAL_SCORE->value, $response['adventBattleRaidTotalScoreRewards'][0]['rewardCategory']);
        $this->assertEquals(RewardType::ITEM->value, $response['adventBattleRaidTotalScoreRewards'][0]['reward']['resourceType']);
        $this->assertEquals('1', $response['adventBattleRaidTotalScoreRewards'][0]['reward']['resourceId']);
        $this->assertEquals(1, $response['adventBattleRaidTotalScoreRewards'][0]['reward']['resourceAmount']);
        $this->assertEquals(null, $response['adventBattleRaidTotalScoreRewards'][0]['reward']['preConversionResource']);

        $this->assertEquals(AdventBattleRewardCategory::MAX_SCORE->value, $response['adventBattleMaxScoreRewards'][0]['rewardCategory']);
        $this->assertEquals(RewardType::ITEM->value, $response['adventBattleMaxScoreRewards'][0]['reward']['resourceType']);
        $this->assertEquals('2', $response['adventBattleMaxScoreRewards'][0]['reward']['resourceId']);
        $this->assertEquals(2, $response['adventBattleMaxScoreRewards'][0]['reward']['resourceAmount']);
        $this->assertEquals(null, $response['adventBattleMaxScoreRewards'][0]['reward']['preConversionResource']);

        $this->assertEquals(1, $response['usrParameter']['level']);
        $this->assertEquals(2, $response['usrParameter']['exp']);
        $this->assertEquals(3, $response['usrParameter']['coin']);
        $this->assertEquals(4, $response['usrParameter']['stamina']);
        $this->assertEquals(null, $response['usrParameter']['staminaUpdatedAt']);
        $this->assertEquals(6, $response['usrParameter']['freeDiamond']);
        $this->assertEquals(7, $response['usrParameter']['paidDiamondIos']);
        $this->assertEquals(8, $response['usrParameter']['paidDiamondAndroid']);

        $this->assertEquals(1, $response['usrItems'][0]['mstItemId']);
        $this->assertEquals(1, $response['usrItems'][0]['amount']);
        $this->assertEquals(2, $response['usrItems'][1]['mstItemId']);
        $this->assertEquals(2, $response['usrItems'][1]['amount']);
        $this->assertEquals(3, $response['usrItems'][2]['mstItemId']);
        $this->assertEquals(3, $response['usrItems'][2]['amount']);

        $this->assertEquals(1, $response['usrEmblems'][0]['mstEmblemId']);
        $this->assertEquals(2, $response['usrEmblems'][1]['mstEmblemId']);
        $this->assertEquals(3, $response['usrEmblems'][2]['mstEmblemId']);
    }

    public function test_start_リクエストを送ると200OKが返ることを確認する()
    {
        // Setup
        $resultData = new AdventBattleStartResultData();
        $this->mock(AdventBattleStartUseCase::class, function (MockInterface $mock) use ($resultData) {
            $mock->shouldReceive('exec')->andReturn($resultData);
        });

        // Exercise
        $requestData = [
            'mstAdventBattleId' => '10',
            'partyNo' => 1,
            'isChallengeAd' => false,
            'inGameBattleLog' => [
                'partyStatus' => [
                    [
                        'usrUnitId' => 'usrUnit1',
                        'mstUnitId' => 'unit1',
                        'color' => 'Red',
                        'roleType' => 'Attack',
                        'hp' => 1,
                        'atk' => 1,
                        'moveSpeed' => 1,
                        'summonCost' => 1,
                        'summonCoolTime' => 1,
                        'damageKnockBackCount' => 1,
                        'specialAttackMstAttackId' => '1001',
                        'attackDelay' => 1,
                        'nextAttackInterval' => 1,
                        'mstUnitAbility1' => '2001',
                        'mstUnitAbility2' => '3001',
                        'mstUnitAbility3' => '4001',
                    ]
                ],
            ],
        ];
        $response = $this->sendRequest('start', $requestData);

        // Verify
        $response->assertStatus(HttpStatusCode::SUCCESS);
    }

    public function test_end_リクエストを送ると200OKが返ることを確認する()
    {
        // Setup
        $resultData = new AdventBattleEndResultData(
            UsrAdventBattle::factory()->create([
                'mst_advent_battle_id' => '10',
                'max_score' => 1000,
                'total_score' => 10000,
                'reset_challenge_count' => 2,
                'reset_ad_challenge_count' => 3,
                'clear_count' => 5,
            ]),
            1000000,
            new UsrParameterData(1, 2, 3, 4, null, 6, 7, 8),
            collect(),
            new UserLevelUpData(0, 0, collect()),
            collect([new AdventBattleAlwaysClearReward(
                RewardType::EXP->value,
                'exp',
                10,
                'advent_battle1'
            )]),
            collect([new AdventBattleRandomClearReward(
                RewardType::EXP->value,
                'exp',
                10,
                'advent_battle1'
            )]),
            collect([new AdventBattleFirstClearReward(
                RewardType::COIN->value,
                'coin',
                10,
                'advent_battle1',
            )]),
            collect(),
            collect([new AdventBattleRankReward(
                RewardType::ITEM->value,
                'item1',
                10,
                'advent_battle1',
                'advent_battle_reward_group1',
                'advent_battle_reward1',
                AdventBattleRewardCategory::RANK->value,
            )]),
            collect(),
            collect(),
        );
        $this->mock(AdventBattleEndUseCase::class, function (MockInterface $mock) use ($resultData) {
            $mock->shouldReceive('exec')->andReturn($resultData);
        });

        // Exercise
        $requestData = [
            'mstAdventBattleId' => '10',
            'inGameBattleLog' => [
                'defeatEnemyCount' => 50,
                'defeatBossEnemyCount' => 2,
                'score' => 999,
                'partyStatus' => [
                    [
                        'usrUnitId' => 'usrUnit1',
                        'mstUnitId' => 'unit1',
                        'color' => 'Red',
                        'roleType' => 'Attack',
                        'hp' => 1,
                        'atk' => 1,
                        'moveSpeed' => 1,
                        'summonCost' => 1,
                        'summonCoolTime' => 1,
                        'damageKnockBackCount' => 1,
                        'specialAttackMstAttackId' => '1001',
                        'attackDelay' => 1,
                        'nextAttackInterval' => 1,
                        'mstUnitAbility1' => '2001',
                        'mstUnitAbility2' => '3001',
                        'mstUnitAbility3' => '4001',
                    ]
                ],
                'maxDamage' => 999999,
            ],
        ];
        $response = $this->sendRequest('end', $requestData);

        // Verify
        $response->assertStatus(HttpStatusCode::SUCCESS);
        $this->assertEquals('10', $response['usrAdventBattle']['mstAdventBattleId']);
        $this->assertEquals(1000, $response['usrAdventBattle']['maxScore']);
        $this->assertEquals(10000, $response['usrAdventBattle']['totalScore']);
        $this->assertEquals(2, $response['usrAdventBattle']['resetChallengeCount']);
        $this->assertEquals(3, $response['usrAdventBattle']['resetAdChallengeCount']);
        $this->assertEquals(5, $response['usrAdventBattle']['clearCount']);
        $this->assertEquals(1000000, $response['totalDamage']);
        $this->assertEquals(1, $response['usrParameter']['level']);
        $this->assertEquals(2, $response['usrParameter']['exp']);
        $this->assertEquals(3, $response['usrParameter']['coin']);
        $this->assertEquals(4, $response['usrParameter']['stamina']);
        $this->assertEquals(null, $response['usrParameter']['staminaUpdatedAt']);
        $this->assertEquals(6, $response['usrParameter']['freeDiamond']);
        $this->assertEquals(7, $response['usrParameter']['paidDiamondIos']);
        $this->assertEquals(8, $response['usrParameter']['paidDiamondAndroid']);

        $this->assertArrayHasKey('userLevel', $response);
        $this->assertArrayHasKey('usrConditionPacks', $response);
        $this->assertArrayHasKey('adventBattleClearRewards', $response);
        $this->assertArrayHasKey('adventBattleDropRewards', $response);
        $this->assertArrayHasKey('adventBattleRankRewards', $response);

        $this->assertEquals([[
            'reward' => [
                'resourceType' => 'Item',
                'resourceId' => 'item1',
                'resourceAmount' => 10,
                'preConversionResource' => null,
                'unreceivedRewardReasonType' => UnreceivedRewardReason::NONE->value,
            ],
            'rewardCategory' => AdventBattleRewardCategory::RANK->value,
        ]], $response['adventBattleRankRewards']);
    }

    public function test_abort_リクエストを送ると200OKが返ることを確認する()
    {
        // Setup
        $resultData = new AdventBattleAbortResultData(
            1000000,
        );
        $this->mock(AdventBattleAbortUseCase::class, function (MockInterface $mock) use ($resultData) {
            $mock->shouldReceive('exec')->andReturn($resultData);
        });

        // Exercise
        $requestData = [
            'abortType' => 1,
        ];
        $response = $this->sendRequest('abort', $requestData);

        // Verify
        $response->assertStatus(HttpStatusCode::SUCCESS);
        $this->assertEquals(1000000, $response['totalDamage']);
    }

    public function testRanking_リクエストを送ると200OKが返ることを確認する()
    {
        // Setup
        $mstAdventBattleId = MstAdventBattle::factory()->create()->toEntity()->getId();

        $usrUserId = $this->createUsrUser()->getId();
        // 自身のランキング用データの生成
        UsrUserProfile::factory()->create([
            'usr_user_id' => $usrUserId,
            'name' => 'test',
            'mst_unit_id' => 'unit1',
            'mst_emblem_id' => 'emblem1',
        ]);

        $score = 10000;
        UsrAdventBattle::factory()->create([
            'usr_user_id' => $usrUserId,
            'mst_advent_battle_id' => $mstAdventBattleId,
            'max_score' => $score,
            'total_score' => $score,
            'is_ranking_reward_received' => false,
        ]);
        $key = CacheKeyUtil::getAdventBattleRankingKey($mstAdventBattleId);
        Redis::connection()->zadd($key, [$usrUserId => $score]);

        // 自身以外のランキング用ユーザーデータ生成
        $usrUsers = UsrUser::factory(3)->create();
        $usrUsers->each(function (UsrUser $usrUser, $index) use ($mstAdventBattleId, $key) {
            $index++;
            UsrUserProfile::factory()->create([
                'usr_user_id' => $usrUser->getId(),
                'name' => (string)$index,
                'mst_unit_id' => 'unit1',
                'mst_emblem_id' => 'emblem1',
            ]);
            UsrAdventBattle::factory()->create([
                'usr_user_id' => $usrUser->getId(),
                'mst_advent_battle_id' => $mstAdventBattleId,
                'max_score' => 1000 * $index,
                'total_score' => 1000 * $index,
                'is_ranking_reward_received' => false,
            ]);
            Redis::connection()->zadd($key, [$usrUser->getId() => 1000 * $index]);
        });

        // Exercise
        $response = $this->sendGetRequest(
            'ranking',
            [
                'mstAdventBattleId' => $mstAdventBattleId,
                'isPrevious' => false,
            ]);

        // Verify
        $response->assertStatus(HttpStatusCode::SUCCESS);

        $this->assertArrayHasKey('ranking', $response);
        $ranking = $response['ranking'];
        $this->assertCount(4, $ranking);
        $this->assertEquals(1, $ranking[0]['rank']);
        $this->assertEquals('test', $ranking[0]['name']);
        $this->assertEquals(10000, $ranking[0]['score']);

        $this->assertEquals(2, $ranking[1]['rank']);
        $this->assertEquals('3', $ranking[1]['name']);
        $this->assertEquals(3000, $ranking[1]['score']);

        $this->assertEquals(3, $ranking[2]['rank']);
        $this->assertEquals('2', $ranking[2]['name']);
        $this->assertEquals(2000, $ranking[2]['score']);

        $this->assertEquals(4, $ranking[3]['rank']);
        $this->assertEquals('1', $ranking[3]['name']);
        $this->assertEquals(1000, $ranking[3]['score']);


        $this->assertArrayHasKey('myRanking', $response);
        $myRanking = $response['myRanking'];
        $this->assertEquals(1, $myRanking['rank']);
        $this->assertEquals(10000, $myRanking['score']);
        $this->assertFalse($myRanking['isExcludeRanking']);
    }

    public function testInfo_リクエストを送ると200OKが返ることを確認する()
    {
        // Setup
        $now = $this->fixTime();
        $mstAdventBattleId = MstAdventBattle::factory()->create([
            'start_at' => $now->subWeeks(2),
            'end_at' => $now->subWeek()
        ])->toEntity()->getId();

        $usrUserId = $this->createUsrUser()->getId();
        $key = CacheKeyUtil::getAdventBattleRankingKey($mstAdventBattleId);
        Redis::connection()->zadd($key, [$usrUserId => 1000]);

        $key = CacheKeyUtil::getAdventBattleRaidTotalScoreKey($mstAdventBattleId);
        Redis::connection()->set($key, 10000);

        UsrAdventBattle::factory()->create([
            'usr_user_id' => $usrUserId,
            'mst_advent_battle_id' => $mstAdventBattleId,
            'max_score' => 1000,
            'total_score' => 1000,
            'is_ranking_reward_received' => false,
        ]);

        // Exercise
        $response = $this->sendGetRequest('info');

        // Verify
        $response->assertStatus(HttpStatusCode::SUCCESS);

        $this->assertArrayHasKey('adventBattleResult', $response);
        $result = $response['adventBattleResult'];
        $this->assertEquals($mstAdventBattleId, $result['mstAdventBattleId']);
        $this->assertEquals(1, $result['myRanking']['rank']);
        $this->assertEquals(1000, $result['myRanking']['score']);
        $this->assertFalse($result['myRanking']['isExcludeRanking']);
        $this->assertEquals(10000, $result['totalDamage']);
    }

    public function testCleanup_リクエストを送ると200OKが返ることを確認する()
    {
        // Setup
        $usrAdventBattle = UsrAdventBattle::factory()->create();

        $resultData = new AdventBattleCleanupResultData(
            $usrAdventBattle,
        );
        $this->mock(AdventBattleCleanupUseCase::class, function (MockInterface $mock) use ($resultData) {
            $mock->shouldReceive('exec')->andReturn($resultData);
        });

        // Exercise
        $requestData = []; // リクエストパラメータは不要
        $response = $this->sendRequest('cleanup', $requestData);

        // Verify
        $response->assertStatus(HttpStatusCode::SUCCESS);
    }
}
