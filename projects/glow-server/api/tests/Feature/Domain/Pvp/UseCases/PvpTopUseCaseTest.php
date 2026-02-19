<?php

declare(strict_types=1);

namespace Tests\Feature\Domain\Pvp;

use Tests\Support\Entities\CurrentUser;
use App\Domain\Pvp\Entities\PvpEncyclopediaEffect;
use App\Domain\Pvp\Enums\PvpMatchingType;
use App\Domain\Pvp\Enums\PvpRankClassType;
use App\Domain\Pvp\Models\UsrPvp;
use App\Domain\Pvp\Services\PvpCacheService;
use App\Domain\Pvp\UseCases\PvpTopUseCase;
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

class PvpTopUseCaseTest extends TestCase
{
    private PvpCacheService $pvpCacheService;

    public function setUp(): void
    {
        parent::setUp();
        $this->pvpCacheService = app()->make(PvpCacheService::class);
    }

    public function test_exec_saves_selected_opponent_candidates_to_database(): void
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

        // UsrPvpを事前に作成（新シーズン処理をスキップするため）
        $usrPvp = UsrPvp::factory()->create([
            'usr_user_id' => $usrUserId,
            'sys_pvp_season_id' => $sysPvpSeasonId,
            'score' => 25,
            'pvp_rank_class_type' => PvpRankClassType::BRONZE->value,
            'pvp_rank_class_level' => 3,
            'daily_remaining_challenge_count' => 0,
            'latest_reset_at' => $now->toDateTimeString(),
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
        $useCase = app(PvpTopUseCase::class);

        $result = $useCase->exec($currentUser);

        // 基本的なレスポンス検証
        $this->assertNotEmpty($result->opponentSelectStatusResponses);
        $this->assertEquals(3, count($result->opponentSelectStatusResponses));

        // UsrPvpがDBに保存されていることを確認
        $usrPvp = UsrPvp::where('usr_user_id', $usrUserId)
            ->where('sys_pvp_season_id', $sysPvpSeasonId)
            ->first();
        
        $this->assertNotNull($usrPvp);
        $this->assertNotNull($usrPvp->getLatestResetAt());

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
            $this->assertArrayHasKey('winAddPoint', $pvpUserProfile);
            $this->assertArrayHasKey('matchingType', $pvpUserProfile);
            
            // partyPvpUnitsが含まれていることを確認
            $this->assertArrayHasKey('partyPvpUnits', $pvpUserProfile);
            $this->assertIsArray($pvpUserProfile['partyPvpUnits']);
            
            // キーとmyIdが一致することを確認
            $this->assertEquals($myId, $pvpUserProfile['myId']);
        }

        // レスポンスと保存されたデータの整合性確認
        for ($i = 0; $i < 3; $i++) {
            $responseData = $result->opponentSelectStatusResponses[$i];
            $myId = $responseData->getMyId();
            
            $this->assertArrayHasKey($myId, $savedCandidates);
            $savedData = $savedCandidates[$myId];

            $this->assertEquals($responseData->getMyId(), $savedData['pvpUserProfile']['myId']);
            $this->assertEquals($responseData->getName(), $savedData['pvpUserProfile']['name']);
            $this->assertEquals($responseData->getMstUnitId(), $savedData['pvpUserProfile']['mstUnitId']);
            $this->assertEquals($responseData->getScore(), $savedData['pvpUserProfile']['score']);
        }
    }

    public function test_exec_with_existing_usr_pvp_updates_selected_opponent_candidates(): void
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

        // 既存のUsrPvpデータを作成
        $existingUsrPvp = UsrPvp::factory()->create([
            'usr_user_id' => $usrUserId,
            'sys_pvp_season_id' => $sysPvpSeasonId,
            'score' => 25,
            'pvp_rank_class_type' => PvpRankClassType::BRONZE->value,
            'pvp_rank_class_level' => 3,
            'selected_opponent_candidates' => json_encode([
                ['myId' => 'old_opponent_1', 'name' => 'Old Opponent 1'],
                ['myId' => 'old_opponent_2', 'name' => 'Old Opponent 2'],
                ['myId' => 'old_opponent_3', 'name' => 'Old Opponent 3'],
            ]),
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

        // UseCase実行
        $useCase = app(PvpTopUseCase::class);
        $result = $useCase->exec($currentUser);

        // UsrPvpを再取得して更新されていることを確認
        $updatedUsrPvp = UsrPvp::where('usr_user_id', $usrUserId)
            ->where('sys_pvp_season_id', $sysPvpSeasonId)
            ->first();

        $this->assertNotNull($updatedUsrPvp);

        // 新しい対戦相手候補で更新されていることを確認
        $updatedCandidates = $updatedUsrPvp->getSelectedOpponentCandidatesToArray();
        $this->assertNotNull($updatedCandidates);
        $this->assertIsArray($updatedCandidates);
        $this->assertEquals(3, count($updatedCandidates));

        // 古いデータとは異なることを確認（キーベース構造で確認）
        $this->assertArrayNotHasKey('old_opponent_1', $updatedCandidates);
        $this->assertArrayNotHasKey('old_opponent_2', $updatedCandidates);
        $this->assertArrayNotHasKey('old_opponent_3', $updatedCandidates);

        // 新しいデータの構造が正しいことを確認（キーベース）
        foreach ($updatedCandidates as $myId => $candidate) {
            $this->assertIsString($myId); // キーがmyIdであることを確認
            $this->assertArrayHasKey('pvpUserProfile', $candidate);
            $this->assertArrayHasKey('unitStatuses', $candidate);
            $this->assertArrayHasKey('usrOutpostEnhancements', $candidate);
            $this->assertArrayHasKey('usrEncyclopediaEffects', $candidate);
            $this->assertArrayHasKey('mstArtworkIds', $candidate);
            
            // キーとmyIdが一致することを確認
            $this->assertEquals($myId, $candidate['pvpUserProfile']['myId']);
        }
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
        $this->pvpCacheService->addOpponentStatus($sysPvpSeasonId, $myId, $opponentPvpStatusData);
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
                'score' => 9,
            ],
            [
                'my_id' => 'cacheUserId3',
                'sys_pvp_season_id' => '2025026',
                'pvp_rank_class_type' => PvpRankClassType::BRONZE->value,
                'pvp_rank_class_level' => 3,
                'score' => 9,
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
                'grade_unit_level_total_count' => 1,
            ],
            [
                'id' => 'dummyUserId2',
                'release_key' => 1,
                'mst_unit_id' => 'dummyUnitId2',
                'mst_emblem_id' => 'dummyUEmblemd2',
                'grade_unit_level_total_count' => 1,
            ],
            [
                'id' => 'dummyUserId3',
                'release_key' => 1,
                'mst_unit_id' => 'dummyUnitId3',
                'mst_emblem_id' => 'dummyUEmblemd3',
                'grade_unit_level_total_count' => 1,
            ],
            [
                'id' => 'dummyUserId4',
                'release_key' => 1,
                'mst_unit_id' => 'dummyUnitId4',
                'mst_emblem_id' => 'dummyUEmblemd4',
                'grade_unit_level_total_count' => 1,
            ],
            [
                'id' => 'dummyUserId5',
                'release_key' => 1,
                'mst_unit_id' => 'dummyUnitId5',
                'mst_emblem_id' => 'dummyUEmblemd5',
                'grade_unit_level_total_count' => 1,
            ],
        ]);

        MstDummyUserI18n::factory()->createMany([
            [
                'id' => 'dummyUserId1',
                'mst_dummy_user_id' => 'dummyUserId1',
                'release_key' => 1,
                'name' => 'ダミー1',
            ],
            [
                'id' => 'dummyUserId2',
                'mst_dummy_user_id' => 'dummyUserId2',
                'release_key' => 1,
                'name' => 'ダミー2',
            ],
            [
                'id' => 'dummyUserId3',
                'mst_dummy_user_id' => 'dummyUserId3',
                'release_key' => 1,
                'name' => 'ダミー3',
            ],
            [
                'id' => 'dummyUserId4',
                'mst_dummy_user_id' => 'dummyUserId4',
                'release_key' => 1,
                'name' => 'ダミー4',
            ],
            [
                'id' => 'dummyUserId5',
                'mst_dummy_user_id' => 'dummyUserId5',
                'release_key' => 1,
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
                'mst_unit_id' => 'dummyUnitId1',
                'level' => 10,
                'rank' => 5,
                'grade_level' => 1,
            ],
            [
                'id' => 'mstPvpId2',
                'release_key' => 1,
                'mst_dummy_user_id' => 'dummyUserId1',
                'mst_unit_id' => 'dummyUnitId2',
                'level' => 10,
                'rank' => 5,
                'grade_level' => 1,
            ],
            [
                'id' => 'mstPvpId3',
                'release_key' => 1,
                'mst_dummy_user_id' => 'dummyUserId1',
                'mst_unit_id' => 'dummyUnitId3',
                'level' => 10,
                'rank' => 5,
                'grade_level' => 1,
            ],
            [
                'id' => 'mstPvpId4',
                'release_key' => 1,
                'mst_dummy_user_id' => 'dummyUserId1',
                'mst_unit_id' => 'dummyUnitId4',
                'level' => 10,
                'rank' => 5,
                'grade_level' => 1,
            ],
            [
                'id' => 'mstPvpId5',
                'release_key' => 1,
                'mst_dummy_user_id' => 'dummyUserId1',
                'mst_unit_id' => 'dummyUnitId5',
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
                'required_lower_score' => 40,
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
                'level' => 1,
            ],
            [
                'id' => 'dummyOutpostId2',
                'mst_dummy_user_id' => 'dummyUserId2',
                'mst_outpost_enhancement_id' => 'enhance_1_3',
                'level' => 1,
            ],
            [
                'id' => 'dummyOutpostId3',
                'mst_dummy_user_id' => 'dummyUserId3',
                'mst_outpost_enhancement_id' => 'enhance_1_3',
                'level' => 1,
            ],
            [
                'id' => 'dummyOutpostId4',
                'mst_dummy_user_id' => 'dummyUserId4',
                'mst_outpost_enhancement_id' => 'enhance_1_3',
                'level' => 1,
            ],
            [
                'id' => 'dummyOutpostId5',
                'mst_dummy_user_id' => 'dummyUserId5',
                'mst_outpost_enhancement_id' => 'enhance_1_3',
                'level' => 1,
            ],
        ]);

        MstPvpMatchingScoreRange::factory()->createMany([
            [
                'id' => 'Bronze_1',
                'rank_class_type' => PvpRankClassType::BRONZE->value,
                'rank_class_level' => 1,
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
                'rank_class_level' => 2,
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
                'rank_class_level' => 3,
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
                'rank_class_level' => 1,
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
                'rank_class_level' => 2,
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
                'rank_class_level' => 3,
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
