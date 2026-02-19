<?php

declare(strict_types=1);

namespace Tests\Feature\Domain\Pvp;

use App\Domain\Common\Constants\ErrorCode;
use App\Domain\Common\Exceptions\GameException;
use App\Domain\Pvp\Models\SysPvpSeason;
use Tests\Support\Entities\CurrentUser;
use App\Domain\Pvp\Entities\PvpEncyclopediaEffect;
use App\Domain\Pvp\Enums\PvpMatchingType;
use App\Domain\Pvp\Enums\PvpRankClassType;

use App\Domain\Pvp\Enums\PvpSessionStatus;
use App\Domain\Pvp\Models\UsrPvp;
use App\Domain\Pvp\Models\UsrPvpSession;
use App\Domain\Pvp\Services\PvpCacheService;

use App\Domain\Pvp\UseCases\PvpChangeOpponentUseCase;
use App\Domain\Resource\Mst\Models\MstDummyOutpost;
use App\Domain\Resource\Mst\Models\MstDummyUser;
use App\Domain\Resource\Mst\Models\MstDummyUserI18n;

use App\Domain\Resource\Mst\Models\MstDummyUserUnit;
use App\Domain\Resource\Mst\Models\MstPvp;
use App\Domain\Resource\Mst\Models\MstPvpDummy;
use App\Domain\Resource\Mst\Models\MstPvpMatchingScoreRange;
use App\Domain\Resource\Mst\Models\MstPvpRank;
use App\Domain\User\Models\UsrUserProfile;
use App\Http\Responses\Data\OpponentPvpStatusData;
use App\Http\Responses\Data\OpponentSelectStatusData;
use App\Http\Responses\Data\PvpUnitData;
use Tests\TestCase;

class PvpChangeOpponentUseCaseTest extends TestCase
{
    private PvpCacheService $pvpCacheService;

    public function setUp(): void
    {
        parent::setUp();
        $this->pvpCacheService = app()->make(PvpCacheService::class);
    }

    public function test_exec_matching(): void
    {
        // 実データ作成
        $usrUserId = $this->createUsrUser()->getId();
        $currentUser = new CurrentUser($usrUserId);
        $now = $this->fixTime();
        $sysPvpSeasonId = sprintf(
            '%04d0%02d',
            $now->isoWeekYear,
            $now->isoWeek
        );

        SysPvpSeason::factory()->create([
            'id' => $sysPvpSeasonId,
        ]);
        $usrPvp = UsrPvp::factory()->create([
            'usr_user_id' => $usrUserId,
            'sys_pvp_season_id' => $sysPvpSeasonId,
            'score' => 25,
            'pvp_rank_class_type' => PvpRankClassType::BRONZE->value,
            'pvp_rank_class_level' => 3,
        ]);

        $usrPvpSession = UsrPvpSession::factory()->create([
            'usr_user_id' => $usrUserId,
            'sys_pvp_season_id' => $sysPvpSeasonId,
            'is_valid' => PvpSessionStatus::STARTED,
        ]);

        // UsrUserProfile
        $usrUserProfile = UsrUserProfile::factory()->create([
            'usr_user_id' => $usrUserId,
            'name' => 'Test User',
        ]);

        $this->setDummyUser();
        $this->setPvpData();
        $this->setMstPvpMatchingScoreRange();

        // キャッシュに対戦相手候補を追加
        foreach ($this->setUserCacheBaseParam() as $value) {
            $this->pvpCacheService->addOpponentCandidate(
                $value['sys_pvp_season_id'],
                $value['my_id'],
                $value['pvp_rank_class_type'],
                $value['pvp_rank_class_level'],
                $value['score']
            );
            $this->setUserCache($value['sys_pvp_season_id'], $value['my_id'], $value['score']);
        }
        // UseCase生成（リポジトリやサービスはDIで解決）
        $useCase = app(PvpChangeOpponentUseCase::class);

        $result = $useCase->exec(
            $currentUser
        );

        $this->assertNotEmpty($result->opponentSelectStatusResponses);
        // PVPマッチングでは常に3件の対戦相手候補を返す仕様
        $this->assertCount(3, $result->opponentSelectStatusResponses);

        // UsrPvpがDBに保存されていることを確認
        $usrPvp = UsrPvp::where('usr_user_id', $usrUserId)
            ->where('sys_pvp_season_id', $sysPvpSeasonId)
            ->first();

        $this->assertNotNull($usrPvp);

        // selected_opponent_candidatesがJSONとして正しく保存されていることを確認
        $savedCandidates = $usrPvp->getSelectedOpponentCandidatesToArray();
        $this->assertNotNull($savedCandidates);
        $this->assertIsArray($savedCandidates);
        $this->assertEquals(3, count($savedCandidates));

        // 保存されたJSONの構造を検証（キーがmyIdになっていることを確認）
        foreach ($savedCandidates as $myId => $candidate) {
            $this->assertIsString($myId); // キーがmyIdであることを確認
            $this->assertArrayHasKey('pvpUserProfile', $candidate);
            $this->assertArrayHasKey('unitStatuses', $candidate);
            $this->assertArrayHasKey('usrOutpostEnhancements', $candidate);
            $this->assertArrayHasKey('usrEncyclopediaEffects', $candidate);
            $this->assertArrayHasKey('mstArtworkIds', $candidate);

            // ネストした配列の型チェック
            $this->assertIsArray($candidate['pvpUserProfile']);
            $this->assertIsArray($candidate['unitStatuses']);
            $this->assertIsArray($candidate['usrOutpostEnhancements']);
            $this->assertIsArray($candidate['usrEncyclopediaEffects']);
            $this->assertIsArray($candidate['mstArtworkIds']);

            // pvpUserProfileの構造チェック
            $pvpUserProfile = $candidate['pvpUserProfile'];
            $this->assertArrayHasKey('myId', $pvpUserProfile);
            $this->assertArrayHasKey('name', $pvpUserProfile);
            $this->assertArrayHasKey('mstUnitId', $pvpUserProfile);
            $this->assertArrayHasKey('score', $pvpUserProfile);

            // キーとmyIdが一致することを確認
            $this->assertEquals($myId, $pvpUserProfile['myId']);
        }

        // レスポンスデータの精査
        for ($i = 0; $i < 3; $i++) {
            $this->assertNotEmpty($result->opponentSelectStatusResponses[$i]->getMyId());
            $this->assertNotEmpty($result->opponentSelectStatusResponses[$i]->getName());
            $this->assertNotEmpty($result->opponentSelectStatusResponses[$i]->getMstUnitId());
            $this->assertNotEmpty($result->opponentSelectStatusResponses[$i]->getMstEmblemId());
            $this->assertIsInt($result->opponentSelectStatusResponses[$i]->getScore());
            $this->assertGreaterThanOrEqual(0, $result->opponentSelectStatusResponses[$i]->getScore());
            $this->assertIsInt($result->opponentSelectStatusResponses[$i]->getWinAddPoint());
            $this->assertGreaterThanOrEqual(0, $result->opponentSelectStatusResponses[$i]->getWinAddPoint());
        }
    }

    public function test_exec_期間外の場合はエラーになる(): void
    {
        // Setup
        $usrUserId = $this->createUsrUser()->getId();
        $currentUser = new CurrentUser($usrUserId);
        // UTC 2025-11-17 02:59:59 は JST 2025-11-17 11:59:59 で、PVPシーズン期間外
        $now = $this->fixTime('2025-11-17 02:59:59');

        $inSeasonDate = $now->subDay();
        $prevSysPvpSeasonId = sprintf(
            '%04d0%02d',
            $inSeasonDate->isoWeekYear,
            $inSeasonDate->isoWeek
        );
        $currentSysPvpSeasonId = sprintf(
            '%04d0%02d',
            $now->isoWeekYear,
            $now->isoWeek
        );
        SysPvpSeason::factory()->createMany([
            [
                'id' => $prevSysPvpSeasonId,
                'start_at' => '2025-11-10 03:00:00',
                'end_at' => '2025-11-16 14:59:59',
            ],
            [
                'id' => $currentSysPvpSeasonId,
                'start_at' => '2025-11-17 03:00:00',
                'end_at' => '2025-11-23 14:59:59',
            ]
        ]);

        // Verify
        $this->expectException(GameException::class);
        $this->expectExceptionCode(ErrorCode::PVP_SEASON_PERIOD_OUTSIDE);

        // Exercise
        $useCase = app(PvpChangeOpponentUseCase::class);
        $useCase->exec($currentUser);
    }

    public function test_exec_ユーザーデータがない場合はエラーとなる(): void
    {
        // Setup
        $usrUserId = $this->createUsrUser()->getId();
        $currentUser = new CurrentUser($usrUserId);
        $now = $this->fixTime();
        $sysPvpSeasonId = sprintf(
            '%04d0%02d',
            $now->isoWeekYear,
            $now->isoWeek
        );

        SysPvpSeason::factory()->create([
            'id' => $sysPvpSeasonId,
        ]);

        // Verify
        $this->expectException(GameException::class);
        $this->expectExceptionCode(ErrorCode::PVP_SESSION_NOT_FOUND);

        // Exercise
        $useCase = app(PvpChangeOpponentUseCase::class);
        $useCase->exec($currentUser);
    }

    private function setUserCache($sysPvpSeasonId, $myId, $score)
    {
        $pvpUnits = collect([
            new PvpUnitData('unit_001', 50, 5, 3),
            new PvpUnitData('unit_002', 45, 4, 2),
        ]);

        $opponentSelectStatusData = new OpponentSelectStatusData(
            $myId,
            'opponent_avatar',
            '1000',
            'opponent_123',
            $score,
             collect([]),
            100
        );

        $pvpEncyclopediaEffects = collect([
            new PvpEncyclopediaEffect('effect_001'),
            new PvpEncyclopediaEffect('effect_002'),
        ]);

        $opponentPvpStatusData = new OpponentPvpStatusData(
            $opponentSelectStatusData,
            $pvpUnits,
            collect([]),
            $pvpEncyclopediaEffects,
            collect(['artwork_001', 'artwork_002'])
        );
        $this->pvpCacheService->addOpponentStatus($sysPvpSeasonId,$myId,$opponentPvpStatusData);
    }

    private function setUserCacheBaseParam()
    {
        return [
            [
                'my_id' => 'cacheUserId1',
                'sys_pvp_season_id' => '2025026',
                'pvp_rank_class_type' => PvpRankClassType::BRONZE->value,
                'pvp_rank_class_level' => 2,
                'score' => 10,
            ],
            [
                'my_id' => 'cacheUserId2',
                'sys_pvp_season_id' => '2025026',
                'pvp_rank_class_type' => PvpRankClassType::BRONZE->value,
                'pvp_rank_class_level' => 2,
                'score' => 9, //12,
            ],
            [
                'my_id' => 'cacheUserId3',
                'sys_pvp_season_id' => '2025026',
                'pvp_rank_class_type' => PvpRankClassType::BRONZE->value,
                'pvp_rank_class_level' => 3,
                'score' => 9, //15,
            ],
            [
                'my_id' => 'cacheUserId4',
                'sys_pvp_season_id' => '2025026',
                'pvp_rank_class_type' => PvpRankClassType::BRONZE->value,
                'pvp_rank_class_level' => 3,
                'score' => 22,
            ],
            [
                'my_id' => 'cacheUserId5',
                'sys_pvp_season_id' => '2025026',
                'pvp_rank_class_type' => PvpRankClassType::SILVER->value,
                'pvp_rank_class_level' => 1,
                'score' => 33,
            ],
            [
                'my_id' => 'cacheUserId6',
                'sys_pvp_season_id' => '2025026',
                'pvp_rank_class_type' => PvpRankClassType::SILVER->value,
                'pvp_rank_class_level' => 2,
                'score' => 40,
            ],
            [
                'my_id' => 'cacheUserId7',
                'sys_pvp_season_id' => '2025026',
                'pvp_rank_class_type' => PvpRankClassType::SILVER->value,
                'pvp_rank_class_level' => 2,
                'score' => 40,
            ],
        ];
    }

    private function setDummyUser()
    {
        MstDummyUser::factory()->createMany([
            [
                'id' => 'dummyUserId1',
                'release_key' => 1,
                'mst_unit_id' => 'dummyUnitId1',
                'mst_emblem_id' => 'dummyUEmblemd1',
                'grade_unit_level_total_count'   => 1,
            ],
            [
                'id' => 'dummyUserId2',
                'release_key' => 1,
                'mst_unit_id' => 'dummyUnitId2',
                'mst_emblem_id' => 'dummyUEmblemd2',
                'grade_unit_level_total_count'   => 1,
            ],
            [
                'id' => 'dummyUserId3',
                'release_key' => 1,
                'mst_unit_id' => 'dummyUnitId3',
                'mst_emblem_id' => 'dummyUEmblemd3',
                'grade_unit_level_total_count'   => 1,
            ],
            [
                'id' => 'dummyUserId4',
                'release_key' => 1,
                'mst_unit_id' => 'dummyUnitId4',
                'mst_emblem_id' => 'dummyUEmblemd4',
                'grade_unit_level_total_count'   => 1,
            ],
            [
                'id' => 'dummyUserId5',
                'release_key' => 1,
                'mst_unit_id' => 'dummyUnitId5',
                'mst_emblem_id' => 'dummyUEmblemd5',
                'grade_unit_level_total_count'   => 1,
            ],
        ]);

        MstDummyUserI18n::factory()->createMany([
            [
                'id' => 'dummyUserId1',
                'mst_dummy_user_id' => 'dummyUserId1',
                'release_key'   => 1,
                'name' => 'ダミー1',
            ],
            [
                'id' => 'dummyUserId2',
                'mst_dummy_user_id' => 'dummyUserId2',
                'release_key'   => 1,
                'name' => 'ダミー2',
            ],
            [
                'id' => 'dummyUserId3',
                'mst_dummy_user_id' => 'dummyUserId3',
                'release_key'   => 1,
                'name' => 'ダミー3',
            ],
            [
                'id' => 'dummyUserId4',
                'mst_dummy_user_id' => 'dummyUserId4',
                'release_key'   => 1,
                'name' => 'ダミー4',
            ],
            [
                'id' => 'dummyUserId5',
                'mst_dummy_user_id' => 'dummyUserId5',
                'release_key'   => 1,
                'name' => 'ダミー5',
            ],
        ]);

        MstPvpDummy::factory()->createMany([
            // Upper用のダミーデータ
            [
                'id' => 'mstPvpId1',
                'release_key' => 1,
                'rank_class_type' => PvpRankClassType::BRONZE->value,
                'rank_class_level' => 3,
                'matching_type' => PvpMatchingType::Upper->value,
                'mst_dummy_user_id' => 'dummyUserId1',
            ],
            [
                'id' => 'mstPvpId2',
                'release_key' => 1,
                'rank_class_type' => PvpRankClassType::BRONZE->value,
                'rank_class_level' => 3,
                'matching_type' => PvpMatchingType::Upper->value,
                'mst_dummy_user_id' => 'dummyUserId4',
            ],
            // Same用のダミーデータ
            [
                'id' => 'mstPvpId3',
                'release_key' => 1,
                'rank_class_type' => PvpRankClassType::BRONZE->value,
                'rank_class_level' => 3,
                'matching_type' => PvpMatchingType::Same->value,
                'mst_dummy_user_id' => 'dummyUserId2',
            ],
            [
                'id' => 'mstPvpId4',
                'release_key' => 1,
                'rank_class_type' => PvpRankClassType::BRONZE->value,
                'rank_class_level' => 3,
                'matching_type' => PvpMatchingType::Same->value,
                'mst_dummy_user_id' => 'dummyUserId5',
            ],
            // Lower用のダミーデータ
            [
                'id' => 'mstPvpId5',
                'release_key' => 1,
                'rank_class_type' => PvpRankClassType::BRONZE->value,
                'rank_class_level' => 3,
                'matching_type' => PvpMatchingType::Lower->value,
                'mst_dummy_user_id' => 'dummyUserId3',
            ],
        ]);

        MstDummyUserUnit::factory()->createMany([
            [
                'id' => 'mstPvpId1',
                'release_key' => 1,
                'mst_dummy_user_id' => 'dummyUserId1',
                'mst_unit_id' =>  'dummyUnitId1',
                'level' => 10,
                'rank' => 5,
                'grade_level' => 1,
            ],
            [
                'id' => 'mstPvpId2',
                'release_key' => 1,
                'mst_dummy_user_id' => 'dummyUserId1',
                'mst_unit_id' =>  'dummyUnitId2',
                'level' => 10,
                'rank' => 5,
                'grade_level' => 1,
            ],
            [
                'id' => 'mstPvpId3',
                'release_key' => 1,
                'mst_dummy_user_id' => 'dummyUserId1',
                'mst_unit_id' =>  'dummyUnitId3',
                'level' => 10,
                'rank' => 5,
                'grade_level' => 1,
            ],
            [
                'id' => 'mstPvpId4',
                'release_key' => 1,
                'mst_dummy_user_id' => 'dummyUserId1',
                'mst_unit_id' =>  'dummyUnitId4',
                'level' => 10,
                'rank' => 5,
                'grade_level' => 1,
            ],
            [
                'id' => 'mstPvpId5',
                'release_key' => 1,
                'mst_dummy_user_id' => 'dummyUserId1',
                'mst_unit_id' =>  'dummyUnitId5',
                'level' => 10,
                'rank' => 5,
                'grade_level' => 1,
            ],
            [
                'id' => 'mstPvpId6',
                'release_key' => 1,
                'mst_dummy_user_id' => 'dummyUserId2',
                'mst_unit_id' =>  'dummyUnitId3',
                'level' => 10,
                'rank' => 5,
                'grade_level' => 1,
            ],
            [
                'id' => 'mstPvpId7',
                'release_key' => 1,
                'mst_dummy_user_id' => 'dummyUserId2',
                'mst_unit_id' =>  'dummyUnitId4',
                'level' => 10,
                'rank' => 5,
                'grade_level' => 1,
            ],
            [
                'id' => 'mstPvpId8',
                'release_key' => 1,
                'mst_dummy_user_id' => 'dummyUserId2',
                'mst_unit_id' =>  'dummyUnitId5',
                'level' => 10,
                'rank' => 5,
                'grade_level' => 1,
            ],

            [
                'id' => 'mstPvpId9',
                'release_key' => 1,
                'mst_dummy_user_id' => 'dummyUserId3',
                'mst_unit_id' =>  'dummyUnitId3',
                'level' => 10,
                'rank' => 5,
                'grade_level' => 1,
            ],
            [
                'id' => 'mstPvpId10',
                'release_key' => 1,
                'mst_dummy_user_id' => 'dummyUserId3',
                'mst_unit_id' =>  'dummyUnitId4',
                'level' => 10,
                'rank' => 5,
                'grade_level' => 1,
            ],
            [
                'id' => 'mstPvpId11',
                'release_key' => 1,
                'mst_dummy_user_id' => 'dummyUserId3',
                'mst_unit_id' =>  'dummyUnitId5',
                'level' => 10,
                'rank' => 5,
                'grade_level' => 1,
            ],

            [
                'id' => 'mstPvpId12',
                'release_key' => 1,
                'mst_dummy_user_id' => 'dummyUserId4',
                'mst_unit_id' =>  'dummyUnitId3',
                'level' => 10,
                'rank' => 5,
                'grade_level' => 1,
            ],
            [
                'id' => 'mstPvpId13',
                'release_key' => 1,
                'mst_dummy_user_id' => 'dummyUserId4',
                'mst_unit_id' =>  'dummyUnitId4',
                'level' => 10,
                'rank' => 5,
                'grade_level' => 1,
            ],
            [
                'id' => 'mstPvpId14',
                'release_key' => 1,
                'mst_dummy_user_id' => 'dummyUserId4',
                'mst_unit_id' =>  'dummyUnitId5',
                'level' => 10,
                'rank' => 5,
                'grade_level' => 1,
            ],
            
            [
                'id' => 'mstPvpId15',
                'release_key' => 1,
                'mst_dummy_user_id' => 'dummyUserId5',
                'mst_unit_id' =>  'dummyUnitId3',
                'level' => 10,
                'rank' => 5,
                'grade_level' => 1,
            ],
            [
                'id' => 'mstPvpId16',
                'release_key' => 1,
                'mst_dummy_user_id' => 'dummyUserId5',
                'mst_unit_id' =>  'dummyUnitId4',
                'level' => 10,
                'rank' => 5,
                'grade_level' => 1,
            ],
            [
                'id' => 'mstPvpId17',
                'release_key' => 1,
                'mst_dummy_user_id' => 'dummyUserId5',
                'mst_unit_id' =>  'dummyUnitId5',
                'level' => 10,
                'rank' => 5,
                'grade_level' => 1,
            ],
        ]);
    }

    private function setPvpData()
    {
        MstPvp::factory()->createMany([
            [
                'id' => 'mstPvpId',
                'item_challenge_cost_amount' => 1,
            ],
            [
                'id' => 'default_pvp',
                'item_challenge_cost_amount' => 1,
            ],
        ]);

        MstPvpRank::factory()->createMany([
            [
                'id' => PvpRankClassType::BRONZE->value . '_1',
                'release_key' => '2025024',
                'rank_class_type' => PvpRankClassType::BRONZE->value,
                'rank_class_level' => 1,
                'required_lower_score' => 1,
            ],
            [
                'id' => PvpRankClassType::BRONZE->value . '_2',
                'release_key' => '2025024',
                'rank_class_type' => PvpRankClassType::BRONZE->value,
                'rank_class_level' => 2,
                'required_lower_score' => 10,
            ],
            [
                'id' => PvpRankClassType::BRONZE->value . '_3',
                'release_key' => '2025024',
                'rank_class_type' => PvpRankClassType::BRONZE->value,
                'rank_class_level' => 3,
                'required_lower_score' => 20,
            ],
            [
                'id' => PvpRankClassType::SILVER->value . '_1',
                'release_key' => '2025024',
                'rank_class_type' => PvpRankClassType::SILVER->value,
                'rank_class_level' => 1,
                'required_lower_score' => 30,
            ],
            [
                'id' => PvpRankClassType::SILVER->value . '_2',
                'release_key' => '2025024',
                'rank_class_type' => PvpRankClassType::SILVER->value,
                'rank_class_level' => 2,
                'required_lower_score' => 39,
            ],
            [
                'id' => PvpRankClassType::SILVER->value . '_3',
                'release_key' => '2025024',
                'rank_class_type' => PvpRankClassType::SILVER->value,
                'rank_class_level' => 3,
                'required_lower_score' => 50,
            ],
        ]);
    }

    private function setMstPvpMatchingScoreRange()
    {
        MstDummyOutpost::factory()->createMany([
            [
                'id' => 'dummyOutpostId1',
                'mst_dummy_user_id' => 'dummyUserId1',
                'mst_outpost_enhancement_id' => 'enhance_1_3',
                'level'   => 1,
            ],
            [
                'id' => 'dummyOutpostId2',
                'mst_dummy_user_id' => 'dummyUserId2',
                'mst_outpost_enhancement_id' => 'enhance_1_3',
                'level'   => 1,
            ],
            [
                'id' => 'dummyOutpostId3',
                'mst_dummy_user_id' => 'dummyUserId3',
                'mst_outpost_enhancement_id' => 'enhance_1_3',
                'level'   => 1,
            ],
            [
                'id' => 'dummyOutpostId4',
                'mst_dummy_user_id' => 'dummyUserId4',
                'mst_outpost_enhancement_id' => 'enhance_1_3',
                'level'   => 1,
            ],
            [
                'id' => 'dummyOutpostId5',
                'mst_dummy_user_id' => 'dummyUserId5',
                'mst_outpost_enhancement_id' => 'enhance_1_3',
                'level'   => 1,
            ],
        ]);

        MstPvpMatchingScoreRange::factory()->createMany([
            [
                'id' => 'Bronze_1',
                'rank_class_type' => PvpRankClassType::BRONZE->value,
                'rank_class_level' => '1',
                'upper_rank_max_score' => 10,
                'upper_rank_min_score' => 5,
                'same_rank_max_score' => 2,
                'same_rank_min_score' => 0,
                'lower_rank_max_score' => 0,
                'lower_rank_min_score' => 0,
                'release_key' => 1,
            ],
            [
                'id' => 'Bronze_2',
                'rank_class_type' => PvpRankClassType::BRONZE->value,
                'rank_class_level' => '2',
                'upper_rank_max_score' => 10,
                'upper_rank_min_score' => 5,
                'same_rank_max_score' => 2,
                'same_rank_min_score' => -2,
                'lower_rank_max_score' => -6,
                'lower_rank_min_score' => -10,
                'release_key' => 1,
            ],
            [
                'id' => 'Bronze_3',
                'rank_class_type' => PvpRankClassType::BRONZE->value,
                'rank_class_level' => '3',
                'upper_rank_max_score' => 10,
                'upper_rank_min_score' => 5,
                'same_rank_max_score' => 2,
                'same_rank_min_score' => -2,
                'lower_rank_max_score' => -6,
                'lower_rank_min_score' => -10,
                'release_key' => 1,
            ],
            [
                'id' => 'SILVER_1',
                'rank_class_type' => PvpRankClassType::SILVER->value,
                'rank_class_level' => '1',
                'upper_rank_max_score' => 10,
                'upper_rank_min_score' => 5,
                'same_rank_max_score' => 2,
                'same_rank_min_score' => -2,
                'lower_rank_max_score' => -6,
                'lower_rank_min_score' => -10,
                'release_key' => 1,
            ],
            [
                'id' => 'SILVER_2',
                'rank_class_type' => PvpRankClassType::SILVER->value,
                'rank_class_level' => '2',
                'upper_rank_max_score' => 10,
                'upper_rank_min_score' => 5,
                'same_rank_max_score' => 2,
                'same_rank_min_score' => -2,
                'lower_rank_max_score' => -6,
                'lower_rank_min_score' => -10,
                'release_key' => 1,
            ],
            [
                'id' => 'SILVER_3',
                'rank_class_type' => PvpRankClassType::SILVER->value,
                'rank_class_level' => '3',
                'upper_rank_max_score' => 10,
                'upper_rank_min_score' => 5,
                'same_rank_max_score' => 2,
                'same_rank_min_score' => -2,
                'lower_rank_max_score' => -6,
                'lower_rank_min_score' => -10,
                'release_key' => 1,
            ],
        ]);
    }
}
