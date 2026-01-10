<?php

declare(strict_types=1);

namespace Tests\Feature\Domain\Pvp;

use App\Domain\Message\Constants\MessageConstant;
use App\Domain\Pvp\Enums\PvpRankClassType;
use App\Domain\Pvp\Enums\PvpRewardCategory;
use App\Domain\Pvp\Models\SysPvpSeason;
use App\Domain\Pvp\Models\UsrPvp;
use App\Domain\Pvp\Services\PvpCacheService;
use App\Domain\Pvp\Services\PvpRewardService;
use App\Domain\Resource\Mst\Models\MstPvpRank;
use App\Domain\Resource\Mst\Models\MstPvpReward;
use App\Domain\Resource\Mst\Models\MstPvpRewardGroup;
use App\Domain\User\Models\UsrUserParameter;
use App\Http\Responses\Data\PvpPreviousSeasonResultData;
use Tests\TestCase;

class PvpRewardServiceTest extends TestCase
{
    private PvpRewardService $pvpRewardService;

    protected function setUp(): void
    {
        parent::setUp();
        $this->pvpRewardService = $this->app->make(PvpRewardService::class);
    }

    public function test_get_season_result_前回シーズンのランク報酬がレスポンスされる(): void
    {
        // 前提データ作成
        $usrUserId = $this->createUsrUser()->getId();
        UsrUserParameter::factory()->create([
            'usr_user_id' => $usrUserId,
        ]);
        $now = $this->fixTime();
        $season = SysPvpSeason::factory()->create(['id' => '2025003']);
        $currentSeason = SysPvpSeason::factory()->create(['id' => '2025004']);
        // 最終プレイはシーズン3
        $usrPvp = UsrPvp::factory()->create([
            'usr_user_id' => $usrUserId,
            'sys_pvp_season_id' => $season->getId(),
            'pvp_rank_class_type' => PvpRankClassType::BRONZE->value,
            'pvp_rank_class_level' => 2,
            'last_played_at' => $now->subDays(10),
            'ranking' => 0,
            'is_season_reward_received' => false,
        ]);
        $mstPvpRank = MstPvpRank::factory()->create([
            'rank_class_type' => PvpRankClassType::BRONZE->value,
            'rank_class_level' => 2,
        ])->toEntity();
        // 報酬グループ・報酬データも必要に応じて作成
        $group = MstPvpRewardGroup::factory()->create([
            'id' => 'test_group',
            'reward_category' => PvpRewardCategory::RANK_ClASS->value,
            'condition_value' => $mstPvpRank->getId(),
        ])->toEntity();
        MstPvpReward::factory()->create([
            'mst_pvp_reward_group_id' => $group->getId(),
        ]);
        // 実行
        $result = $this->pvpRewardService->getSeasonResult(
            $usrUserId,
            $currentSeason->getId(),
            $usrPvp,
        );
        // 検証
        $this->assertInstanceOf(PvpPreviousSeasonResultData::class, $result);
        $response = $result->formatToResponse();
        $this->assertArrayHasKey('pvpRankClassType', $response);
        $this->assertArrayHasKey('rankClassLevel', $response);
        $this->assertArrayHasKey('score', $response);
        $this->assertArrayHasKey('ranking', $response);
        $this->assertArrayHasKey('pvpRewards', $response);
        $this->assertArrayHasKey('rewardCategory', $response['pvpRewards'][0]);
        $this->assertArrayHasKey('reward', $response['pvpRewards'][0]);
        $this->assertArrayHasKey('unreceivedRewardReasonType', $response['pvpRewards'][0]['reward']);
        $this->assertArrayHasKey('resourceType', $response['pvpRewards'][0]['reward']);
        $this->assertArrayHasKey('resourceId', $response['pvpRewards'][0]['reward']);
        $this->assertArrayHasKey('resourceAmount', $response['pvpRewards'][0]['reward']);
        $this->assertArrayHasKey('preConversionResource', $response['pvpRewards'][0]['reward']);
        $this->assertEquals($usrPvp->getPvpRankClassType(), $result->rankClassType);
        $this->assertEquals($usrPvp->getPvpRankClassLevel(), $result->rankClassLevel);
        $this->assertEquals($usrPvp->getRanking(), $result->ranking);
        $this->assertEquals($usrPvp->getScore(), $result->score);
        $this->assertEquals(1, $result->rewards->count());
    }

    public function test_get_season_result_前回シーズンのランキング報酬がレスポンスされる(): void
    {
        // 前提データ作成
        $usrUserId = $this->createUsrUser()->getId();
        UsrUserParameter::factory()->create([
            'usr_user_id' => $usrUserId,
        ]);
        $now = $this->fixTime();
        $season = SysPvpSeason::factory()->create(['id' => '2025003']);
        $currentSeason = SysPvpSeason::factory()->create(['id' => '2025004']);
        // 最終プレイはシーズン3
        $usrPvp = UsrPvp::factory()->create([
            'usr_user_id' => $usrUserId,
            'sys_pvp_season_id' => $season->getId(),
            'pvp_rank_class_type' => PvpRankClassType::BRONZE->value,
            'pvp_rank_class_level' => 2,
            'last_played_at' => $now->subDays(10),
            'ranking' => 1,
            'is_season_reward_received' => false,
        ]);
        $pvpCacheService = $this->app->make(PvpCacheService::class);
        $pvpCacheService->addRankingScore(
            $season->getId(),
            $usrUserId,
            1000
        );
        // 報酬グループ・報酬データも必要に応じて作成
        $group = MstPvpRewardGroup::factory()->create([
            'id' => 'test_group1',
            'reward_category' => PvpRewardCategory::RANKING->value,
            'condition_value' => '100',
        ])->toEntity();
        MstPvpReward::factory()->create([
            'mst_pvp_reward_group_id' => $group->getId(),
            'resource_amount' => 100,
        ]);
        $group = MstPvpRewardGroup::factory()->create([
            'id' => 'test_group2',
            'reward_category' => PvpRewardCategory::RANKING->value,
            'condition_value' => '200',
        ])->toEntity();
        MstPvpReward::factory()->create([
            'mst_pvp_reward_group_id' => $group->getId(),
            'resource_amount' => 50,
        ]);
        // 実行
        $result = $this->pvpRewardService->getSeasonResult(
            $usrUserId,
            $currentSeason->getId(),
            $usrPvp,
        );
        // 検証
        $this->assertInstanceOf(PvpPreviousSeasonResultData::class, $result);
        $this->assertEquals($usrPvp->getPvpRankClassType(), $result->rankClassType);
        $this->assertEquals($usrPvp->getPvpRankClassLevel(), $result->rankClassLevel);
        $this->assertEquals($usrPvp->getRanking(), $result->ranking);
        $this->assertEquals($usrPvp->getScore(), $result->score);
        $this->assertEquals(1, $result->rewards->count());
        $this->assertEquals(100, $result->rewards->first()->getAmount());
    }

    public function test_get_season_result_間隔の開いたシーズン情報を取得できる(): void
    {
        // 前提データ作成
        $usrUserId = $this->createUsrUser()->getId();
        UsrUserParameter::factory()->create([
            'usr_user_id' => $usrUserId,
        ]);
        $now = $this->fixTime();

        // シーズンデータ作成
        $seasons = [];
        foreach (range(1, 6) as $i) {
            $seasons[$i] = SysPvpSeason::factory()->create([
                'id' => '202500' . $i,
            ]);
        }
        $currentSeason = $seasons[6];
        $lastPlayedSeason = $seasons[4];

        // 最終プレイシーズンのUsrPvp
        $usrPvp = UsrPvp::factory()->create([
            'usr_user_id' => $usrUserId,
            'sys_pvp_season_id' => $lastPlayedSeason->getId(),
            'pvp_rank_class_type' => PvpRankClassType::GOLD->value,
            'pvp_rank_class_level' => 2,
            'last_played_at' => $now->subDays(10),
            'ranking' => 11,
            'is_season_reward_received' => false,
        ]);

        // 報酬グループ・報酬データ作成用ヘルパー
        $createRewardGroupAndRewards = function ($season, $rankType, $rankLevel, $amounts, $category = PvpRewardCategory::RANK_ClASS->value, $condValue = null) {
            try {
                $mstPvpRank = MstPvpRank::factory()->create([
                    'rank_class_type' => $rankType,
                    'rank_class_level' => $rankLevel,
                ])->toEntity();
            } catch (\Exception $e) {
                $mstPvpRank = MstPvpRank::query()
                    ->where('rank_class_type', $rankType)
                    ->where('rank_class_level', $rankLevel)
                    ->firstOrFail()
                    ->toEntity();
            }
            $group = MstPvpRewardGroup::factory()->create([
                'mst_pvp_id' => $season->id,
                'reward_category' => $category,
                'condition_value' => $condValue ?? $mstPvpRank->getId(),
            ])->toEntity();
            foreach ($amounts as $amt) {
                MstPvpReward::factory()->create([
                    'mst_pvp_reward_group_id' => $group->getId(),
                    'resource_amount' => $amt,
                ]);
            }
        };

        $pvpCacheService = $this->app->make(PvpCacheService::class);
        $pvpCacheService->addRankingScore(
            $lastPlayedSeason->getId(),
            $usrUserId,
            1000
        );

        // 最終プレイシーズンのランク報酬・ランキング報酬
        $createRewardGroupAndRewards($seasons[1], PvpRankClassType::GOLD->value, 2, [1000, 900]);
        $createRewardGroupAndRewards($seasons[1], PvpRankClassType::GOLD->value, 2, [100, 200], PvpRewardCategory::RANKING->value, '100');
        // シーズン2のランク報酬
        $createRewardGroupAndRewards($seasons[2], PvpRankClassType::SILVER->value, 1, [500]);
        // シーズン3のランク報酬
        $createRewardGroupAndRewards($seasons[3], PvpRankClassType::BRONZE->value, 1, [100]);

        // 実行
        $result = $this->pvpRewardService->getSeasonResult(
            $usrUserId,
            $currentSeason->getId(),
            $usrPvp,
        );

        // 検証
        [
            $rankClassType,
            $rankClassLevel,
        ] = $usrPvp->getPvpRankClassTypeEnum()->getLowerWithLevel(1, $usrPvp->getPvpRankClassLevel());
        $this->assertInstanceOf(PvpPreviousSeasonResultData::class, $result);
        $this->assertEquals($rankClassType->value, $result->rankClassType);
        $this->assertEquals($rankClassLevel, $result->rankClassLevel);
        $this->assertEquals(0, $result->ranking);
        $this->assertEquals(0, $result->score);
        $this->assertEquals(0, $result->rewards->count());
    }

    public function test_get_season_result_間隔の開きすぎた場合nullが取得される(): void
    {
        // 前提データ作成
        $usrUserId = $this->createUsrUser()->getId();
        UsrUserParameter::factory()->create([
            'usr_user_id' => $usrUserId,
        ]);
        $now = $this->fixTime();

        // シーズンデータ作成
        $seasons = [];
        foreach (range(1, 6) as $i) {
            $seasons[$i] = SysPvpSeason::factory()->create([
                'id' => '202500' . $i,
            ]);
        }
        $currentSeason = $seasons[6];
        $lastPlayedSeason = $seasons[1];

        // 最終プレイシーズンのUsrPvp
        $usrPvp = UsrPvp::factory()->create([
            'usr_user_id' => $usrUserId,
            'sys_pvp_season_id' => $lastPlayedSeason->getId(),
            'pvp_rank_class_type' => PvpRankClassType::GOLD->value,
            'pvp_rank_class_level' => 2,
            'last_played_at' => $now->subDays(10),
            'ranking' => 11,
            'is_season_reward_received' => false,
        ]);

        // 報酬グループ・報酬データ作成用ヘルパー
        $createRewardGroupAndRewards = function ($season, $rankType, $rankLevel, $amounts, $category = PvpRewardCategory::RANK_ClASS->value, $condValue = null) {
            try {
                $mstPvpRank = MstPvpRank::factory()->create([
                    'rank_class_type' => $rankType,
                    'rank_class_level' => $rankLevel,
                ])->toEntity();
            } catch (\Exception $e) {
                $mstPvpRank = MstPvpRank::query()
                    ->where('rank_class_type', $rankType)
                    ->where('rank_class_level', $rankLevel)
                    ->firstOrFail()
                    ->toEntity();
            }
            $group = MstPvpRewardGroup::factory()->create([
                'mst_pvp_id' => $season->id,
                'reward_category' => $category,
                'condition_value' => $condValue ?? $mstPvpRank->getId(),
            ])->toEntity();
            foreach ($amounts as $amt) {
                MstPvpReward::factory()->create([
                    'mst_pvp_reward_group_id' => $group->getId(),
                    'resource_amount' => $amt,
                ]);
            }
        };

        $pvpCacheService = $this->app->make(PvpCacheService::class);
        $pvpCacheService->addRankingScore(
            $lastPlayedSeason->getId(),
            $usrUserId,
            1000
        );

        // 最終プレイシーズンのランク報酬・ランキング報酬
        $createRewardGroupAndRewards($seasons[1], PvpRankClassType::GOLD->value, 2, [1000, 900]);
        $createRewardGroupAndRewards($seasons[1], PvpRankClassType::GOLD->value, 2, [100, 200], PvpRewardCategory::RANKING->value, '100');
        // シーズン2のランク報酬
        $createRewardGroupAndRewards($seasons[2], PvpRankClassType::SILVER->value, 1, [500]);
        // シーズン3のランク報酬
        $createRewardGroupAndRewards($seasons[3], PvpRankClassType::BRONZE->value, 1, [100]);

        // 実行
        $result = $this->pvpRewardService->getSeasonResult(
            $usrUserId,
            $currentSeason->getId(),
            $usrPvp,
        );

        // 検証
        $this->assertNull($result);
    }

    public function test_get_season_result_今シーズンがランキング集計中の場合nullが返される(): void
    {
        // 前提データ作成
        $usrUserId = $this->createUsrUser()->getId();
        UsrUserParameter::factory()->create([
            'usr_user_id' => $usrUserId,
        ]);
        $now = $this->fixTime();

        // 前シーズン情報を追加
        $previousSeason = SysPvpSeason::factory()->create(['id' => '2025003']);
        // 現在のシーズンを作成
        $currentSeason = SysPvpSeason::factory()->create(['id' => '2025004']);

        // 前シーズンのプレイ情報
         UsrPvp::factory()->create([
            'usr_user_id' => $usrUserId,
            'sys_pvp_season_id' => $previousSeason->getId(), // 現在のシーズンでプレイ
            'pvp_rank_class_type' => PvpRankClassType::GOLD->value,
            'pvp_rank_class_level' => 3,
            'last_played_at' => $now->subDays(1),
            'ranking' => 5,
            'is_season_reward_received' => false,
         ]);
        // 現在のシーズンでプレイしているUsrPvp（ランキング集計中）
        $usrPvp = UsrPvp::factory()->create([
            'usr_user_id' => $usrUserId,
            'sys_pvp_season_id' => $currentSeason->getId(), // 現在のシーズンでプレイ
            'pvp_rank_class_type' => PvpRankClassType::GOLD->value,
            'pvp_rank_class_level' => 3,
            'last_played_at' => $now->subDays(1),
            'ranking' => 5,
            'is_season_reward_received' => false,
        ]);

        // 実行（現在のシーズンと同じシーズンでプレイしている場合）
        $result = $this->pvpRewardService->getSeasonResult(
            $usrUserId,
            $currentSeason->getId(),
            $usrPvp,
        );

        // 検証（現在のシーズンでプレイしている場合はnullが返される）
        $this->assertNull($result);
    }

    public function test_get_season_result_シーズン2の集計が終了しシーズン3が開始している場合シーズン2結果が返される(): void
    {
        // 前提データ作成
        $usrUserId = $this->createUsrUser()->getId();
        UsrUserParameter::factory()->create([
            'usr_user_id' => $usrUserId,
        ]);
        $now = $this->fixTime();

        // 前シーズンと現在のシーズンと次シーズンを作成
        $season1 = SysPvpSeason::factory()->create(['id' => '2025002']);
        $season2 = SysPvpSeason::factory()->create(['id' => '2025003']);
        $season3 = SysPvpSeason::factory()->create(['id' => '2025004']);

        // 前シーズンのプレイ情報
        $usrPvp = UsrPvp::factory()->create([
            'usr_user_id' => $usrUserId,
            'sys_pvp_season_id' => $season1->getId(), // 前シーズンでプレイ
            'pvp_rank_class_type' => PvpRankClassType::GOLD->value,
            'pvp_rank_class_level' => 1,
            'last_played_at' => $now->subDays(5),
            'ranking' => 1,
            'is_season_reward_received' => false,
        ]);
        // 今シーズンでプレイして、現在は新シーズンが開始。報酬は未受取
        $usrPvp = UsrPvp::factory()->create([
            'usr_user_id' => $usrUserId,
            'sys_pvp_season_id' => $season2->getId(), // 前シーズンでプレイ
            'pvp_rank_class_type' => PvpRankClassType::SILVER->value,
            'pvp_rank_class_level' => 1,
            'last_played_at' => $now->subDays(5),
            'ranking' => 1,
            'is_season_reward_received' => false,
        ]);

        // ランキングスコアを設定
        $pvpCacheService = $this->app->make(PvpCacheService::class);
        $pvpCacheService->addRankingScore(
            $season2->getId(),
            $usrUserId,
            2000
        );

        // 報酬グループ・報酬データを作成
        $mstPvpRankSilver = MstPvpRank::factory()->create([
            'rank_class_type' => PvpRankClassType::SILVER->value,
            'rank_class_level' => 1,
        ])->toEntity();
        $mstPvpRankGold = MstPvpRank::factory()->create([
            'rank_class_type' => PvpRankClassType::GOLD->value,
            'rank_class_level' => 1,
        ])->toEntity();

        // ランク報酬
        $rankGroup1 = MstPvpRewardGroup::factory()->create([
            'id' => 'rank_reward_group1',
            'reward_category' => PvpRewardCategory::RANK_ClASS->value,
            'condition_value' => $mstPvpRankSilver->getId(),
        ])->toEntity();
        $mstPvpReward = MstPvpReward::factory()->create([
            'mst_pvp_reward_group_id' => $rankGroup1->getId(),
            'resource_amount' => 300,
        ]);
        $rankGroup2 = MstPvpRewardGroup::factory()->create([
            'id' => 'rank_reward_group2',
            'reward_category' => PvpRewardCategory::RANK_ClASS->value,
            'condition_value' => $mstPvpRankGold->getId(),
        ])->toEntity();
        MstPvpReward::factory()->create([
            'mst_pvp_reward_group_id' => $rankGroup2->getId(),
            'resource_amount' => 100,
        ]);


        // ランキング報酬
        $rankingGroup = MstPvpRewardGroup::factory()->create([
            'id' => 'ranking_reward_group',
            'reward_category' => PvpRewardCategory::RANKING->value,
            'condition_value' => '10', // 10位以内
        ])->toEntity();
        MstPvpReward::factory()->create([
            'mst_pvp_reward_group_id' => $rankingGroup->getId(),
            'resource_amount' => 300,
        ]);

        // 実行（シーズン2の結果を取得）
        $result = $this->pvpRewardService->getSeasonResult(
            $usrUserId,
            $season3->getId(),
            $usrPvp,
        );

        // 検証
        $this->assertInstanceOf(PvpPreviousSeasonResultData::class, $result);
        $response = $result->formatToResponse();

        // シーズン２の結果が正しく返される
        $this->assertEquals(PvpRankClassType::SILVER->value, $result->rankClassType);
        $this->assertEquals(1, $result->rankClassLevel);
        $this->assertEquals($usrPvp->getScore(), $result->score);
        $this->assertEquals(1, $result->ranking); // myRankingが設定される

        // 報酬も正しく返される
        $this->assertEquals(2, $result->rewards->count());
        $this->assertEquals(600, $result->rewards->sum(fn ($r) => $r->getAmount()));
        $this->assertArrayHasKey('pvpRewards', $response);
    }

    public function test_get_season_result_シーズン2の報酬が受け取り済みでもデータが取得できる(): void
    {
        // 前提データ作成
        $usrUserId = $this->createUsrUser()->getId();
        UsrUserParameter::factory()->create([
            'usr_user_id' => $usrUserId,
        ]);
        $now = $this->fixTime();

        // 前シーズンと現在のシーズンと次シーズンを作成
        $season1 = SysPvpSeason::factory()->create(['id' => '2025002']);
        $season2 = SysPvpSeason::factory()->create(['id' => '2025003']);
        $season3 = SysPvpSeason::factory()->create(['id' => '2025004']);

        // 前シーズンのプレイ情報
        $usrPvp = UsrPvp::factory()->create([
            'usr_user_id' => $usrUserId,
            'sys_pvp_season_id' => $season1->getId(), // 前シーズンでプレイ
            'pvp_rank_class_type' => PvpRankClassType::GOLD->value,
            'pvp_rank_class_level' => 1,
            'last_played_at' => $now->subDays(5),
            'ranking' => 1,
            'is_season_reward_received' => true,
        ]);
        // 今シーズンでプレイして、現在は新シーズンが開始。報酬は未受取
        $usrPvp = UsrPvp::factory()->create([
            'usr_user_id' => $usrUserId,
            'sys_pvp_season_id' => $season2->getId(), // 前シーズンでプレイ
            'pvp_rank_class_type' => PvpRankClassType::SILVER->value,
            'pvp_rank_class_level' => 1,
            'last_played_at' => $now->subDays(5),
            'ranking' => 1,
            'is_season_reward_received' => true, // 受取済み
        ]);

        // ランキングスコアを設定
        $pvpCacheService = $this->app->make(PvpCacheService::class);
        $pvpCacheService->addRankingScore(
            $season2->getId(),
            $usrUserId,
            2000
        );

        // 報酬グループ・報酬データを作成
        $mstPvpRankSilver = MstPvpRank::factory()->create([
            'rank_class_type' => PvpRankClassType::SILVER->value,
            'rank_class_level' => 1,
        ])->toEntity();
        $mstPvpRankGold = MstPvpRank::factory()->create([
            'rank_class_type' => PvpRankClassType::GOLD->value,
            'rank_class_level' => 1,
        ])->toEntity();

        // ランク報酬
        $rankGroup1 = MstPvpRewardGroup::factory()->create([
            'id' => 'rank_reward_group1',
            'reward_category' => PvpRewardCategory::RANK_ClASS->value,
            'condition_value' => $mstPvpRankSilver->getId(),
        ])->toEntity();
        $mstPvpReward = MstPvpReward::factory()->create([
            'mst_pvp_reward_group_id' => $rankGroup1->getId(),
            'resource_amount' => 300,
        ]);
        $rankGroup2 = MstPvpRewardGroup::factory()->create([
            'id' => 'rank_reward_group2',
            'reward_category' => PvpRewardCategory::RANK_ClASS->value,
            'condition_value' => $mstPvpRankGold->getId(),
        ])->toEntity();
        MstPvpReward::factory()->create([
            'mst_pvp_reward_group_id' => $rankGroup2->getId(),
            'resource_amount' => 100,
        ]);


        // ランキング報酬
        $rankingGroup = MstPvpRewardGroup::factory()->create([
            'id' => 'ranking_reward_group',
            'reward_category' => PvpRewardCategory::RANKING->value,
            'condition_value' => '10', // 10位以内
        ])->toEntity();
        MstPvpReward::factory()->create([
            'mst_pvp_reward_group_id' => $rankingGroup->getId(),
            'resource_amount' => 300,
        ]);

        // 実行（シーズン2の結果を取得）
        $result = $this->pvpRewardService->getSeasonResult(
            $usrUserId,
            $season3->getId(),
            $usrPvp,
        );

        // 検証
        $this->assertInstanceOf(PvpPreviousSeasonResultData::class, $result);
        $response = $result->formatToResponse();

        // シーズン２の結果が正しく返される
        $this->assertEquals(PvpRankClassType::SILVER->value, $result->rankClassType);
        $this->assertEquals(1, $result->rankClassLevel);
        $this->assertEquals($usrPvp->getScore(), $result->score);
        $this->assertEquals(1, $result->ranking); // myRankingが設定される

        // 報酬も正しく返される
        $this->assertCount(0, $result->rewards);
    }

    public function test_get_season_result_報酬のメッセージgroupIdが取得できる(): void
    {
        // 前提データ作成
        $usrUserId = $this->createUsrUser()->getId();
        UsrUserParameter::factory()->create([
            'usr_user_id' => $usrUserId,
        ]);
        $now = $this->fixTime();
        $season = SysPvpSeason::factory()->create(['id' => '2025003']);
        $currentSeason = SysPvpSeason::factory()->create(['id' => '2025004']);
        // 最終プレイはシーズン3
        $usrPvp = UsrPvp::factory()->create([
            'usr_user_id' => $usrUserId,
            'sys_pvp_season_id' => $season->getId(),
            'pvp_rank_class_type' => PvpRankClassType::BRONZE->value,
            'pvp_rank_class_level' => 2,
            'last_played_at' => $now->subDays(10),
            'ranking' => 0,
            'is_season_reward_received' => false,
        ]);
        $mstPvpRank = MstPvpRank::factory()->create([
            'rank_class_type' => PvpRankClassType::BRONZE->value,
            'rank_class_level' => 2,
        ])->toEntity();
        // 報酬グループ・報酬データも必要に応じて作成
        $group = MstPvpRewardGroup::factory()->create([
            'id' => 'test_group',
            'reward_category' => PvpRewardCategory::RANK_ClASS->value,
            'condition_value' => $mstPvpRank->getId(),
        ])->toEntity();
        MstPvpReward::factory()->create([
            'mst_pvp_reward_group_id' => $group->getId(),
        ]);
        // 実行
        $result = $this->pvpRewardService->getSeasonResult(
            $usrUserId,
            $currentSeason->getId(),
            $usrPvp,
        );
        // 検証
        $this->assertInstanceOf(PvpPreviousSeasonResultData::class, $result);
        $response = $result->formatToResponse();
        $reward = $result->rewards->first();
        $this->assertEquals($group->getId() . '_' . $usrPvp->getSysPvpSeasonId(), $reward->getRewardGroupId());
    }

    public function test_get_old_season_rewards_間隔の開いた最後にプレイしたシーズンの報酬を得られる(): void
    {
        // 前提データ作成
        $usrUserId = $this->createUsrUser()->getId();
        UsrUserParameter::factory()->create([
            'usr_user_id' => $usrUserId,
        ]);
        $now = $this->fixTime('2025-09-08 00:00:00');

        // シーズンデータ作成
        $seasons = [];
        $seasonCount = 3;
        foreach (range(1, $seasonCount) as $i) {
            $seasons[$i] = SysPvpSeason::factory()->create([
                'id' => '202500' . $i,
                'start_at' => $now->subWeek(($seasonCount - $i))->addHours(12),
                'end_at' => $now->subWeek(($seasonCount - $i + 1))->subSecond(),
                'closed_at' => $now->subWeek(($seasonCount - $i + 1))->addHours(12)->subSecond(),
            ]);
        }
        $lastPlayedSeason = $seasons[1];

        // 最終プレイシーズンのUsrPvp
        $usrPvp = UsrPvp::factory()->create([
            'usr_user_id' => $usrUserId,
            'sys_pvp_season_id' => $lastPlayedSeason->getId(),
            'pvp_rank_class_type' => PvpRankClassType::GOLD->value,
            'pvp_rank_class_level' => 2,
            'last_played_at' => $now->subDays(10),
            'ranking' => 11,
            'is_season_reward_received' => false,
        ]);

        // 報酬グループ・報酬データ作成用ヘルパー
        $createRewardGroupAndRewards = function ($season, $rankType, $rankLevel, $amounts, $category = PvpRewardCategory::RANK_ClASS->value, $condValue = null) {
            try {
                $mstPvpRank = MstPvpRank::factory()->create([
                    'rank_class_type' => $rankType,
                    'rank_class_level' => $rankLevel,
                ])->toEntity();
            } catch (\Exception $e) {
                $mstPvpRank = MstPvpRank::query()
                    ->where('rank_class_type', $rankType)
                    ->where('rank_class_level', $rankLevel)
                    ->firstOrFail()
                    ->toEntity();
            }
            $group = MstPvpRewardGroup::factory()->create([
                'mst_pvp_id' => $season->id,
                'reward_category' => $category,
                'condition_value' => $condValue ?? $mstPvpRank->getId(),
            ])->toEntity();
            foreach ($amounts as $amt) {
                MstPvpReward::factory()->create([
                    'mst_pvp_reward_group_id' => $group->getId(),
                    'resource_amount' => $amt,
                ]);
            }
        };

        $pvpCacheService = $this->app->make(PvpCacheService::class);
        $pvpCacheService->addRankingScore(
            $lastPlayedSeason->getId(),
            $usrUserId,
            1000
        );

        // 最終プレイシーズンのランク報酬・ランキング報酬
        $createRewardGroupAndRewards($seasons[1], PvpRankClassType::GOLD->value, 2, [1000, 900]);
        $createRewardGroupAndRewards($seasons[1], PvpRankClassType::GOLD->value, 2, [100, 200], PvpRewardCategory::RANKING->value, '100');
        // シーズン2のランク報酬
        $createRewardGroupAndRewards($seasons[2], PvpRankClassType::SILVER->value, 1, [500]);
        // シーズン3のランク報酬
        $createRewardGroupAndRewards($seasons[3], PvpRankClassType::BRONZE->value, 1, [100]);

        // 実行
        $result = $this->pvpRewardService->getOldSeasonRewards(
            $usrUserId,
            $now,
        );

        // 検証
        $this->assertCount(1, $result);
        $this->assertEquals($seasons[1]->getId(), $result->keys()->first());
        $rewards = $result->first();
        $rankRewards = $rewards->filter(function ($reward) {
            return $reward->getTitle() === MessageConstant::PVP_RANK_REWARD_TITLE;
        })->sortByDesc(fn ($reward) => $reward->getAmount())->values();
        $this->assertCount(2, $rankRewards);
        $this->assertEquals(1000, $rankRewards[0]->getAmount());
        $this->assertEquals(900, $rankRewards[1]->getAmount());

        $rankingRewards = $rewards->filter(function ($reward) {
            return $reward->getTitle() === MessageConstant::PVP_RANKING_REWARD_TITLE;
        })->sortByDesc(fn ($reward) => $reward->getAmount())->values();
        $this->assertCount(2, $rankingRewards);
        $this->assertEquals(200, $rankingRewards[0]->getAmount());
        $this->assertEquals(100, $rankingRewards[1]->getAmount());
    }

    public function test_get_old_season_rewards_4シーズン以上前のプレイしたシーズンの報酬は得られない(): void
    {
        // 前提データ作成
        $usrUserId = $this->createUsrUser()->getId();
        UsrUserParameter::factory()->create([
            'usr_user_id' => $usrUserId,
        ]);
        $now = $this->fixTime('2025-09-08 00:00:00');

        // シーズンデータ作成
        $seasons = [];
        $seasonCount = 6;
        foreach (range(1, $seasonCount) as $i) {
            $seasons[$i] = SysPvpSeason::factory()->create([
                'id' => '202500' . $i,
                'start_at' => $now->subWeek(($seasonCount - $i))->addHours(12),
                'end_at' => $now->subWeek(($seasonCount - $i + 1))->subSecond(),
                'closed_at' => $now->subWeek(($seasonCount - $i + 1))->addHours(12)->subSecond(),
            ]);
        }
        $lastPlayedSeason = $seasons[1];

        // 最終プレイシーズンのUsrPvp
        $usrPvp = UsrPvp::factory()->create([
            'usr_user_id' => $usrUserId,
            'sys_pvp_season_id' => $lastPlayedSeason->getId(),
            'pvp_rank_class_type' => PvpRankClassType::GOLD->value,
            'pvp_rank_class_level' => 2,
            'last_played_at' => $now->subDays(10),
            'ranking' => 11,
            'is_season_reward_received' => false,
        ]);

        // 報酬グループ・報酬データ作成用ヘルパー
        $createRewardGroupAndRewards = function ($season, $rankType, $rankLevel, $amounts, $category = PvpRewardCategory::RANK_ClASS->value, $condValue = null) {
            try {
                $mstPvpRank = MstPvpRank::factory()->create([
                    'rank_class_type' => $rankType,
                    'rank_class_level' => $rankLevel,
                ])->toEntity();
            } catch (\Exception $e) {
                $mstPvpRank = MstPvpRank::query()
                    ->where('rank_class_type', $rankType)
                    ->where('rank_class_level', $rankLevel)
                    ->firstOrFail()
                    ->toEntity();
            }
            $group = MstPvpRewardGroup::factory()->create([
                'mst_pvp_id' => $season->id,
                'reward_category' => $category,
                'condition_value' => $condValue ?? $mstPvpRank->getId(),
            ])->toEntity();
            foreach ($amounts as $amt) {
                MstPvpReward::factory()->create([
                    'mst_pvp_reward_group_id' => $group->getId(),
                    'resource_amount' => $amt,
                ]);
            }
        };

        $pvpCacheService = $this->app->make(PvpCacheService::class);
        $pvpCacheService->addRankingScore(
            $lastPlayedSeason->getId(),
            $usrUserId,
            1000
        );

        // 最終プレイシーズンのランク報酬・ランキング報酬
        $createRewardGroupAndRewards($seasons[1], PvpRankClassType::GOLD->value, 2, [1000, 900]);
        $createRewardGroupAndRewards($seasons[1], PvpRankClassType::GOLD->value, 2, [100, 200], PvpRewardCategory::RANKING->value, '100');
        // シーズン2のランク報酬
        $createRewardGroupAndRewards($seasons[2], PvpRankClassType::SILVER->value, 1, [500]);
        // シーズン3のランク報酬
        $createRewardGroupAndRewards($seasons[3], PvpRankClassType::BRONZE->value, 1, [100]);

        // 実行
        $result = $this->pvpRewardService->getOldSeasonRewards(
            $usrUserId,
            $now,
        );

        // 検証
        $this->assertCount(0, $result);
    }

    public function test_get_old_season_rewards_年越しケースで前々シーズン以降の報酬を得られる(): void
    {
        // 前提データ作成
        $usrUserId = $this->createUsrUser()->getId();
        UsrUserParameter::factory()->create([
            'usr_user_id' => $usrUserId,
        ]);
        $now = $this->fixTime('2026-01-03 00:00:00'); // ISO week 1

        // シーズンデータ作成（年越しケース - 週ベースのシーズンID）
        // 現在シーズン: 2026001（2026年第1週）
        // closed済みシーズン: 2025052（前回シーズン）, 2025051, 2025050（年越し前）
        $seasons = [];

        // 2025年第50週
        $seasons['2025050'] = SysPvpSeason::factory()->create([
            'id' => '2025050',
            'start_at' => $now->subWeeks(3)->addHours(12),
            'end_at' => $now->subWeeks(3)->addDays(7)->subSecond(),
            'closed_at' => $now->subWeeks(3)->addDays(7)->addHours(12)->subSecond(),
        ]);

        // 2025年第51週
        $seasons['2025051'] = SysPvpSeason::factory()->create([
            'id' => '2025051',
            'start_at' => $now->subWeeks(2)->addHours(12),
            'end_at' => $now->subWeeks(2)->addDays(7)->subSecond(),
            'closed_at' => $now->subWeeks(2)->addDays(7)->addHours(12)->subSecond(),
        ]);

        // 2025年第52週（前回シーズン）
        $seasons['2025052'] = SysPvpSeason::factory()->create([
            'id' => '2025052',
            'start_at' => $now->subWeeks(1)->addHours(12),
            'end_at' => $now->subWeeks(1)->addDays(7)->subSecond(),
            'closed_at' => $now->subWeeks(1)->addDays(7)->addHours(12)->subSecond(),
        ]);

        // 現在シーズン: 2026年第1週
        SysPvpSeason::factory()->create([
            'id' => '2026001',
            'start_at' => $now->subDays(3),
            'end_at' => $now->addDays(4),
            'closed_at' => null,
        ]);

        // 2025050のUsrPvp（未受取）
        UsrPvp::factory()->create([
            'usr_user_id' => $usrUserId,
            'sys_pvp_season_id' => '2025050',
            'pvp_rank_class_type' => PvpRankClassType::GOLD->value,
            'pvp_rank_class_level' => 3,
            'last_played_at' => $now->subWeeks(3)->addDays(5),
            'ranking' => 50,
            'is_season_reward_received' => false,
        ]);

        // 2025051のUsrPvp（未受取）
        UsrPvp::factory()->create([
            'usr_user_id' => $usrUserId,
            'sys_pvp_season_id' => '2025051',
            'pvp_rank_class_type' => PvpRankClassType::SILVER->value,
            'pvp_rank_class_level' => 1,
            'last_played_at' => $now->subWeeks(2)->addDays(5),
            'ranking' => 100,
            'is_season_reward_received' => false,
        ]);

        // 報酬データ作成用ヘルパー
        $createRewardGroupAndRewards = function ($season, $rankType, $rankLevel, $amounts, $category = PvpRewardCategory::RANK_ClASS->value, $condValue = null) {
            try {
                $mstPvpRank = MstPvpRank::factory()->create([
                    'rank_class_type' => $rankType,
                    'rank_class_level' => $rankLevel,
                ])->toEntity();
            } catch (\Exception $e) {
                $mstPvpRank = MstPvpRank::query()
                    ->where('rank_class_type', $rankType)
                    ->where('rank_class_level', $rankLevel)
                    ->firstOrFail()
                    ->toEntity();
            }
            $group = MstPvpRewardGroup::factory()->create([
                'mst_pvp_id' => $season->id,
                'reward_category' => $category,
                'condition_value' => $condValue ?? $mstPvpRank->getId(),
            ])->toEntity();
            foreach ($amounts as $amt) {
                MstPvpReward::factory()->create([
                    'mst_pvp_reward_group_id' => $group->getId(),
                    'resource_amount' => $amt,
                ]);
            }
        };

        $pvpCacheService = $this->app->make(PvpCacheService::class);

        // 2025050のランキングスコア
        $pvpCacheService->addRankingScore(
            '2025050',
            $usrUserId,
            1000
        );

        // 2025051のランキングスコア
        $pvpCacheService->addRankingScore(
            '2025051',
            $usrUserId,
            800
        );

        // 2025050のランク報酬・ランキング報酬
        $createRewardGroupAndRewards($seasons['2025050'], PvpRankClassType::GOLD->value, 3, [2000, 1500]);
        $createRewardGroupAndRewards($seasons['2025050'], PvpRankClassType::GOLD->value, 3, [300, 200], PvpRewardCategory::RANKING->value, '100');

        // 2025051のランク報酬・ランキング報酬
        $createRewardGroupAndRewards($seasons['2025051'], PvpRankClassType::SILVER->value, 1, [1000, 800]);
        $createRewardGroupAndRewards($seasons['2025051'], PvpRankClassType::SILVER->value, 1, [150, 100], PvpRewardCategory::RANKING->value, '200');

        // 2025052のランク報酬（前回シーズンなので取得されない想定）
        $createRewardGroupAndRewards($seasons['2025052'], PvpRankClassType::BRONZE->value, 1, [500]);

        // 実行
        $result = $this->pvpRewardService->getOldSeasonRewards(
            $usrUserId,
            $now,
        );

        // 検証
        // 前回シーズン（2025052）を除く2シーズン（2025050, 2025051）の報酬が得られる
        $this->assertCount(2, $result);

        // 2025050の報酬を確認
        $this->assertTrue($result->has('2025050'));
        $rewards2025050 = $result->get('2025050');
        $this->assertCount(4, $rewards2025050); // ランク報酬2 + ランキング報酬2

        $rankRewards2025050 = $rewards2025050->filter(function ($reward) {
            return $reward->getTitle() === MessageConstant::PVP_RANK_REWARD_TITLE;
        })->sortByDesc(fn ($reward) => $reward->getAmount())->values();
        $this->assertCount(2, $rankRewards2025050);
        $this->assertEquals(2000, $rankRewards2025050[0]->getAmount());
        $this->assertEquals(1500, $rankRewards2025050[1]->getAmount());

        // 2025051の報酬を確認
        $this->assertTrue($result->has('2025051'));
        $rewards2025051 = $result->get('2025051');
        $this->assertCount(4, $rewards2025051); // ランク報酬2 + ランキング報酬2

        $rankRewards2025051 = $rewards2025051->filter(function ($reward) {
            return $reward->getTitle() === MessageConstant::PVP_RANK_REWARD_TITLE;
        })->sortByDesc(fn ($reward) => $reward->getAmount())->values();
        $this->assertCount(2, $rankRewards2025051);
        $this->assertEquals(1000, $rankRewards2025051[0]->getAmount());
        $this->assertEquals(800, $rankRewards2025051[1]->getAmount());

        // 前回シーズン（2025052）の報酬は含まれない
        $this->assertFalse($result->has('2025052'));
    }
}
