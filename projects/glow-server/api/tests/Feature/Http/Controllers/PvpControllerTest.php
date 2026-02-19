<?php

namespace Tests\Feature\Http\Controllers;

use App\Domain\Common\Constants\System;
use App\Domain\Pvp\Entities\PvpEncyclopediaEffect;
use App\Domain\Pvp\Entities\PvpResultPoints;
use App\Domain\Pvp\Enums\PvpRankClassType;
use App\Domain\Pvp\Enums\PvpSessionStatus;
use App\Domain\Pvp\Models\SysPvpSeason;
use App\Domain\Pvp\Models\UsrPvp;
use App\Domain\Pvp\Models\UsrPvpSession;
use App\Domain\Pvp\UseCases\PvpAbortUseCase;
use App\Domain\Pvp\UseCases\PvpEndUseCase;
use App\Domain\Pvp\UseCases\PvpResumeUseCase;
use App\Domain\Pvp\UseCases\PvpStartUseCase;
use App\Domain\Pvp\UseCases\PvpTopUseCase;
use App\Domain\Resource\Entities\Rewards\PvpTotalScoreReward;
use App\Domain\Resource\Enums\RewardType;
use App\Domain\Resource\Mst\Models\MstDummyOutpost;
use App\Domain\Resource\Mst\Models\MstDummyUser;
use App\Domain\Resource\Mst\Models\MstDummyUserI18n;
use App\Domain\Resource\Mst\Models\MstDummyUserUnit;
use App\Domain\Resource\Mst\Models\MstEmblem;
use App\Domain\Resource\Mst\Models\MstPvp;
use App\Domain\Resource\Mst\Models\MstPvpDummy;
use App\Domain\Resource\Mst\Models\MstPvpMatchingScoreRange;
use App\Domain\Resource\Mst\Models\MstPvpRank;
use App\Domain\Resource\Mst\Models\MstUnit;
use App\Domain\Resource\Usr\Entities\UsrOutpostEnhancementEntity;
use App\Domain\User\Models\UsrUserProfile;
use App\Http\Responses\Data\OpponentPvpStatusData;
use App\Http\Responses\Data\OpponentSelectStatusData;
use App\Http\Responses\Data\OpponentSelectStatusResponseData;
use App\Http\Responses\Data\PvpMyRankingData;
use App\Http\Responses\Data\PvpRankingData;
use App\Http\Responses\Data\PvpUnitData;
use App\Http\Responses\Data\UsrParameterData;
use App\Http\Responses\Data\UsrPvpStatusData;
use App\Http\Responses\ResultData\PvpAbortResultData;
use App\Http\Responses\ResultData\PvpEndResultData;
use App\Http\Responses\ResultData\PvpRankingResultData;
use App\Http\Responses\ResultData\PvpResumeResultData;
use App\Http\Responses\ResultData\PvpStartResultData;
use Mockery\MockInterface;
use PHPUnit\Framework\Attributes\DataProvider;

class PvpControllerTest extends BaseControllerTestCase
{
    protected string $baseUrl = '/api/pvp/';

    public function testTop_リクエストを送ると200OKが返り想定通りのレスポンスが返ることを確認する()
    {
        // setup
        $url = 'top';
        $params = [];
        $user = $this->createDummyUser();
        UsrUserProfile::factory()->create([
            'usr_user_id' => $user->id,
        ]);

        // PVP系のマスタデータをセットアップ
        $this->setDummyUser();
        $this->setPvpData();
        $this->setMstPvpMatchingScoreRange();

        // Act: リクエスト送信
        $response = $this->sendRequest($url, $params);

        // Assert: ステータスとレスポンス構造
        $response->assertStatus(200);
        $response->assertJsonStructure([
            'pvpHeldStatus',
            'usrPvpStatus',
            'opponentSelectStatuses' => [
                '*' => [
                    'myId',
                    'name',
                    'mstUnitId',
                    'mstEmblemId',
                    'score',
                    'partyPvpUnits' => [
                        '*' => [
                            'mstUnitId',
                            'level',
                            'rank',
                            'gradeLevel',
                        ]
                    ],
                    'winAddPoint',
                    'opponentPvpStatus' => [
                        'pvpUnits' => [
                            '*' => [
                                'mstUnitId',
                                'level',
                                'rank',
                                'gradeLevel',
                            ]
                        ],
                        'usrOutpostEnhancements' => [
                            '*' => [
                                'mstOutpostId',
                                'mstOutpostEnhancementId',
                                'level',
                            ]
                        ],
                        'usrEncyclopediaEffects' => [
                            '*' => [
                                'mstEncyclopediaEffectId',
                            ]
                        ],
                        'mstArtworkIds',
                    ]
                ]
            ],
            'pvpPreviousSeasonResult',
            'isViewableRanking',
        ]);

        // opponentSelectStatusesの各要素でpartyPvpUnitsとopponentPvpStatusが含まれていることを確認
        $responseData = $response->json();
        $this->assertArrayHasKey('opponentSelectStatuses', $responseData);
        $this->assertIsArray($responseData['opponentSelectStatuses']);
        $this->assertNotEmpty($responseData['opponentSelectStatuses'], 'opponentSelectStatuses should not be empty');

        foreach ($responseData['opponentSelectStatuses'] as $opponentStatus) {
            // partyPvpUnitsの確認
            $this->assertArrayHasKey('partyPvpUnits', $opponentStatus);
            $this->assertIsArray($opponentStatus['partyPvpUnits']);

            // opponentPvpStatusの確認
            $this->assertArrayHasKey('opponentPvpStatus', $opponentStatus, 'opponentPvpStatus should exist in response');
            $this->assertIsArray($opponentStatus['opponentPvpStatus']);

            $opponentPvpStatus = $opponentStatus['opponentPvpStatus'];

            // pvpUnitsの確認
            $this->assertArrayHasKey('pvpUnits', $opponentPvpStatus);
            $this->assertIsArray($opponentPvpStatus['pvpUnits']);
            foreach ($opponentPvpStatus['pvpUnits'] as $pvpUnit) {
                $this->assertArrayHasKey('mstUnitId', $pvpUnit);
                $this->assertArrayHasKey('level', $pvpUnit);
                $this->assertArrayHasKey('rank', $pvpUnit);
                $this->assertArrayHasKey('gradeLevel', $pvpUnit);
            }

            // usrOutpostEnhancementsの確認
            $this->assertArrayHasKey('usrOutpostEnhancements', $opponentPvpStatus);
            $this->assertIsArray($opponentPvpStatus['usrOutpostEnhancements']);
            foreach ($opponentPvpStatus['usrOutpostEnhancements'] as $usrOutpostEnhancement) {
                $this->assertArrayHasKey('mstOutpostId', $usrOutpostEnhancement);
                $this->assertArrayHasKey('mstOutpostEnhancementId', $usrOutpostEnhancement);
                $this->assertArrayHasKey('level', $usrOutpostEnhancement);
            }

            // usrEncyclopediaEffectsの確認
            $this->assertArrayHasKey('usrEncyclopediaEffects', $opponentPvpStatus);
            $this->assertIsArray($opponentPvpStatus['usrEncyclopediaEffects']);
            foreach ($opponentPvpStatus['usrEncyclopediaEffects'] as $usrEncyclopediaEffect) {
                $this->assertArrayHasKey('mstEncyclopediaEffectId', $usrEncyclopediaEffect);
            }

            // mstArtworkIdsの確認
            $this->assertArrayHasKey('mstArtworkIds', $opponentPvpStatus);
            $this->assertIsArray($opponentPvpStatus['mstArtworkIds']);
        }
    }

    public function testchangeOpponent_リクエストを送ると200OKが返り想定通りのレスポンスが返ることを確認する()
    {
        // setup
        $url = 'change_opponent';
        $params = [];
        $user = $this->createDummyUser();
        UsrUserProfile::factory()->create([
            'usr_user_id' => $user->id,
        ]);
        $now = $this->fixTime();
        $sysPvpSeasonId = sprintf(
            '%04d0%02d',
            $now->isoWeekYear,
            $now->isoWeek
        );
        SysPvpSeason::factory()->create(['id' => $sysPvpSeasonId]);
        MstUnit::factory()->create([
            'id' => 'unit1',
            'color' => 'Red',
            'mst_unit_ability_id1' => '2001',
            'mst_unit_ability_id2' => '3001',
            'mst_unit_ability_id3' => '4001',
            'move_speed' => '1.11',
        ]);
        MstUnit::factory()->create([
            'id' => 'unit2',
            'color' => 'Blue',
            'mst_unit_ability_id1' => '2002',
            'mst_unit_ability_id2' => '0',
            'mst_unit_ability_id3' => '4002',
            'move_speed' => '1.22',
        ]);
        MstUnit::factory()->create([
            'id' => 'unit3',
            'color' => 'Green',
            'mst_unit_ability_id1' => '0',
            'mst_unit_ability_id2' => '3003',
            'mst_unit_ability_id3' => '0',
            'move_speed' => '1.33',
        ]);
        MstEmblem::factory()->create([
            'id' => '2_2_1',
        ]);

        UsrPvp::factory()->create([
            'usr_user_id' => $user->id,
            'sys_pvp_season_id' => $sysPvpSeasonId,
            'score' => 20,
            'pvp_rank_class_type' => PvpRankClassType::BRONZE->value,
            'pvp_rank_class_level' => 3,
            'latest_reset_at' => $now->subDay()->toDateTimeString(), // 昨日の日付
        ]);

        UsrPvpSession::factory()->create([
            'usr_user_id' => $user->id,
            'sys_pvp_season_id' => $sysPvpSeasonId,
            'is_valid' => PvpSessionStatus::STARTED,
        ]);

        // PVP系のマスタデータをセットアップ
        $this->setDummyUser();
        $this->setPvpData();
        $this->setMstPvpMatchingScoreRange();

        // Act: リクエスト送信
        $response = $this->sendRequest($url, $params);

        // Assert: ステータスとレスポンス構造
        $response->assertStatus(200);
        $response->assertJsonStructure([
            'opponentSelectStatuses' => [
                '*' => [
                    'myId',
                    'name',
                    'mstUnitId',
                    'mstEmblemId',
                    'score',
                    'partyPvpUnits' => [
                        '*' => [
                            'mstUnitId',
                            'level',
                            'rank',
                            'gradeLevel',
                        ]
                    ],
                    'winAddPoint',
                    'opponentPvpStatus' => [
                        'pvpUnits' => [
                            '*' => [
                                'mstUnitId',
                                'level',
                                'rank',
                                'gradeLevel',
                            ]
                        ],
                        'usrOutpostEnhancements' => [
                            '*' => [
                                'mstOutpostId',
                                'mstOutpostEnhancementId',
                                'level',
                            ]
                        ],
                        'usrEncyclopediaEffects' => [
                            '*' => [
                                'mstEncyclopediaEffectId',
                            ]
                        ],
                        'mstArtworkIds',
                    ]
                ]
            ],
        ]);

        // opponentSelectStatusesの各要素でpartyPvpUnitsとopponentPvpStatusが含まれていることを確認
        $responseData = $response->json();
        $this->assertArrayHasKey('opponentSelectStatuses', $responseData);
        $this->assertIsArray($responseData['opponentSelectStatuses']);
        $this->assertNotEmpty($responseData['opponentSelectStatuses'], 'opponentSelectStatuses should not be empty');

        foreach ($responseData['opponentSelectStatuses'] as $opponentStatus) {
            // partyPvpUnitsの確認
            $this->assertArrayHasKey('partyPvpUnits', $opponentStatus);
            $this->assertIsArray($opponentStatus['partyPvpUnits']);

            // opponentPvpStatusの確認
            $this->assertArrayHasKey('opponentPvpStatus', $opponentStatus, 'opponentPvpStatus should exist in response');
            $this->assertIsArray($opponentStatus['opponentPvpStatus']);

            $opponentPvpStatus = $opponentStatus['opponentPvpStatus'];

            // pvpUnitsの確認
            $this->assertArrayHasKey('pvpUnits', $opponentPvpStatus);
            $this->assertIsArray($opponentPvpStatus['pvpUnits']);
            foreach ($opponentPvpStatus['pvpUnits'] as $pvpUnit) {
                $this->assertArrayHasKey('mstUnitId', $pvpUnit);
                $this->assertArrayHasKey('level', $pvpUnit);
                $this->assertArrayHasKey('rank', $pvpUnit);
                $this->assertArrayHasKey('gradeLevel', $pvpUnit);
            }

            // usrOutpostEnhancementsの確認
            $this->assertArrayHasKey('usrOutpostEnhancements', $opponentPvpStatus);
            $this->assertIsArray($opponentPvpStatus['usrOutpostEnhancements']);
            foreach ($opponentPvpStatus['usrOutpostEnhancements'] as $usrOutpostEnhancement) {
                $this->assertArrayHasKey('mstOutpostId', $usrOutpostEnhancement);
                $this->assertArrayHasKey('mstOutpostEnhancementId', $usrOutpostEnhancement);
                $this->assertArrayHasKey('level', $usrOutpostEnhancement);
            }

            // usrEncyclopediaEffectsの確認
            $this->assertArrayHasKey('usrEncyclopediaEffects', $opponentPvpStatus);
            $this->assertIsArray($opponentPvpStatus['usrEncyclopediaEffects']);
            foreach ($opponentPvpStatus['usrEncyclopediaEffects'] as $usrEncyclopediaEffect) {
                $this->assertArrayHasKey('mstEncyclopediaEffectId', $usrEncyclopediaEffect);
            }

            // mstArtworkIdsの確認
            $this->assertArrayHasKey('mstArtworkIds', $opponentPvpStatus);
            $this->assertIsArray($opponentPvpStatus['mstArtworkIds']);
        }
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
            [
                'id' => 'mstPvpId',
                'release_key' => 1,
                'mst_dummy_user_id' => 'dummyUserId1',
                'rank_class_type' => 'Bronze',
                'rank_class_level' => 0,
                'matching_type' => 'Same',
            ],
            [
                'id' => 'mstPvpId2',
                'release_key' => 1,
                'mst_dummy_user_id' => 'dummyUserId2',
                'rank_class_type' => 'Bronze',
                'rank_class_level' => 0,
                'matching_type' => 'Upper',
            ],
            [
                'id' => 'mstPvpId3',
                'release_key' => 1,
                'mst_dummy_user_id' => 'dummyUserId3',
                'rank_class_type' => 'Bronze',
                'rank_class_level' => 0,
                'matching_type' => 'Lower',
            ],
            [
                'id' => 'mstPvpId4',
                'release_key' => 1,
                'mst_dummy_user_id' => 'dummyUserId1',
                'rank_class_type' => 'Bronze',
                'rank_class_level' => 3,
                'matching_type' => 'Same',
            ],
            [
                'id' => 'mstPvpId5',
                'release_key' => 1,
                'mst_dummy_user_id' => 'dummyUserId2',
                'rank_class_type' => 'Bronze',
                'rank_class_level' => 3,
                'matching_type' => 'Upper',
            ],
            [
                'id' => 'mstPvpId6',
                'release_key' => 1,
                'mst_dummy_user_id' => 'dummyUserId3',
                'rank_class_type' => 'Bronze',
                'rank_class_level' => 3,
                'matching_type' => 'Lower',
            ],
            [
                'id' => 'mstPvpId7',
                'release_key' => 1,
                'mst_dummy_user_id' => 'dummyUserId4',
                'rank_class_type' => 'Bronze',
                'rank_class_level' => 2,
                'matching_type' => 'Same',
            ],
            [
                'id' => 'mstPvpId8',
                'release_key' => 1,
                'mst_dummy_user_id' => 'dummyUserId5',
                'rank_class_type' => 'Silver',
                'rank_class_level' => 1,
                'matching_type' => 'Upper',
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
                'id' => PvpRankClassType::BRONZE->value . '_0',
                'release_key' => '2025024',
                'rank_class_type' => PvpRankClassType::BRONZE->value,
                'rank_class_level' => 0,
                'required_lower_score' => 0,
            ],
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
                'id' => 'Bronze_0',
                'rank_class_type' => PvpRankClassType::BRONZE->value,
                'rank_class_level' => 0,
                'upper_rank_max_score' => 15,
                'upper_rank_min_score' => 10,
                'same_rank_max_score' => 2,
                'same_rank_min_score' => -2,
                'lower_rank_max_score' => -1,
                'lower_rank_min_score' => -5,
                'release_key' => 1,
            ],
            [
                'id' => 'Bronze_1',
                'rank_class_type' => PvpRankClassType::BRONZE->value,
                'rank_class_level' => 1,
                'upper_rank_max_score' => 15,
                'upper_rank_min_score' => 10,
                'same_rank_max_score' => 2,
                'same_rank_min_score' => -2,
                'lower_rank_max_score' => -1,
                'lower_rank_min_score' => -5,
                'release_key' => 1,
            ],
            [
                'id' => 'Bronze_2',
                'rank_class_type' => PvpRankClassType::BRONZE->value,
                'rank_class_level' => 2,
                'upper_rank_max_score' => 15,
                'upper_rank_min_score' => 10,
                'same_rank_max_score' => 2,
                'same_rank_min_score' => -2,
                'lower_rank_max_score' => -1,
                'lower_rank_min_score' => -5,
                'release_key' => 1,
            ],
            [
                'id' => 'Bronze_3',
                'rank_class_type' => PvpRankClassType::BRONZE->value,
                'rank_class_level' => 3,
                'upper_rank_max_score' => 15,
                'upper_rank_min_score' => 10,
                'same_rank_max_score' => 2,
                'same_rank_min_score' => -2,
                'lower_rank_max_score' => -1,
                'lower_rank_min_score' => -5,
                'release_key' => 1,
            ],
            [
                'id' => 'Silver_1',
                'rank_class_type' => PvpRankClassType::SILVER->value,
                'rank_class_level' => 1,
                'upper_rank_max_score' => 15,
                'upper_rank_min_score' => 10,
                'same_rank_max_score' => 2,
                'same_rank_min_score' => -2,
                'lower_rank_max_score' => -1,
                'lower_rank_min_score' => -5,
                'release_key' => 1,
            ],
            [
                'id' => 'Silver_2',
                'rank_class_type' => PvpRankClassType::SILVER->value,
                'rank_class_level' => 2,
                'upper_rank_max_score' => 15,
                'upper_rank_min_score' => 10,
                'same_rank_max_score' => 2,
                'same_rank_min_score' => -2,
                'lower_rank_max_score' => -1,
                'lower_rank_min_score' => -5,
                'release_key' => 1,
            ],
            [
                'id' => 'Silver_3',
                'rank_class_type' => PvpRankClassType::SILVER->value,
                'rank_class_level' => 3,
                'upper_rank_max_score' => 15,
                'upper_rank_min_score' => 10,
                'same_rank_max_score' => 2,
                'same_rank_min_score' => -2,
                'lower_rank_max_score' => -1,
                'lower_rank_min_score' => -5,
                'release_key' => 1,
            ],
        ]);
    }

    public function testStart_リクエストを送ると200OKが返り想定通りのレスポンスが返ることを確認する()
    {
        // setup
        $url = 'start';
        $params = [
            'sysPvpSeasonId' => '1',
            'isUseItem' => 0,
            'opponentMyId' => 'test_user',
            'partyNo' => 1,
            'inGameBattleLog' => [
                'partyStatus' => [
                    [
                        'usrUnitId' => 'usrUnit1',
                        'mstUnitId' => 'unit1',
                        'color' => 'Red',
                        'roleType' => 'Attack',
                    ]
                ]
            ],
        ];
        $this->createUsrUser();

        // テスト用のPVPユニットデータを作成
        $pvpUnits = collect([
            new PvpUnitData(
                mstUnitId: 'unit_001',
                level: 50,
                rank: 5,
                gradeLevel: 3
            ),
            new PvpUnitData(
                mstUnitId: 'unit_002',
                level: 45,
                rank: 4,
                gradeLevel: 2
            ),
        ]);

        // テスト用の前哨基地強化データを作成
        $usrOutpostEnhancements = collect([
            new UsrOutpostEnhancementEntity(
                mstOutpostId: 'outpost_001',
                mstOutpostEnhancementId: 'enhancement_001',
                level: 3
            ),
            new UsrOutpostEnhancementEntity(
                mstOutpostId: 'outpost_001',
                mstOutpostEnhancementId: 'enhancement_002',
                level: 2
            ),
        ]);

        // テスト用の図鑑効果IDデータを作成
        $usrEncyclopediaEffects = collect([
            new PvpEncyclopediaEffect('encyclopedia_effect_001'),
            new PvpEncyclopediaEffect('encyclopedia_effect_002'),
        ]);

        // テスト用のアートワークIDデータを作成
        $mstArtworkIds = collect([
            'artwork_001',
            'artwork_002',
        ]);

        // ユーザープロフィールデータを作成
        $opponentSelectStatusData = new OpponentSelectStatusData(
            myId: 'test_user',
            name: 'Test User',
            mstUnitId: 'unit_001',
            mstEmblemId: 'emblem_001',
            score: 1000,
            partyPvpUnitDatas: collect([
                new PvpUnitData('unit_001', 50, 5, 3),
                new PvpUnitData('unit_002', 45, 4, 2),
            ])
        );

        // OpponentPvpStatusDataを作成
        $opponentPvpStatusData = new OpponentPvpStatusData(
            $opponentSelectStatusData,
            $pvpUnits,
            $usrOutpostEnhancements,
            $usrEncyclopediaEffects,
            $mstArtworkIds
        );

        // PvpStartResultDataを作成
        $resultData = new PvpStartResultData($opponentPvpStatusData);

        $this->mock(PvpStartUseCase::class, function (MockInterface $mock) use ($resultData) {
            $mock->shouldReceive('exec')
                ->withArgs(function ($user, $sysPvpSeasonId, $isUseItem, $opponentMyId, $partyNo, $inGameBattleLog) {
                    return $sysPvpSeasonId === '1'
                        && $isUseItem === false
                        && $opponentMyId === 'test_user'
                        && $partyNo === 1
                        && is_array($inGameBattleLog);
                })
                ->andReturn($resultData);
        });

        // Act: リクエスト送信
        $response = $this->sendRequest($url, $params);

        // Assert: ステータスとレスポンス構造
        $response->assertStatus(200);
        $response->assertJsonStructure([
            'opponentPvpStatus' => [
                'pvpUnits' => [
                    '*' => [
                        'mstUnitId',
                        'level',
                        'rank',
                        'gradeLevel',
                    ]
                ],
                'usrOutpostEnhancements' => [
                    '*' => [
                        'mstOutpostId',
                        'mstOutpostEnhancementId',
                        'level',
                    ]
                ],
                'usrEncyclopediaEffects' => [
                    '*' => [
                        'mstEncyclopediaEffectId',
                    ]
                ],
                'mstArtworkIds',
            ]
        ]);

        // Assert: レスポンス内容の検証
        $responseData = $response->json()['opponentPvpStatus'];

        // PVPユニットデータの検証
        $this->assertCount(2, $responseData['pvpUnits']);
        $this->assertEquals('unit_001', $responseData['pvpUnits'][0]['mstUnitId']);
        $this->assertEquals(50, $responseData['pvpUnits'][0]['level']);
        $this->assertEquals(5, $responseData['pvpUnits'][0]['rank']);
        $this->assertEquals(3, $responseData['pvpUnits'][0]['gradeLevel']);

        $this->assertEquals('unit_002', $responseData['pvpUnits'][1]['mstUnitId']);
        $this->assertEquals(45, $responseData['pvpUnits'][1]['level']);
        $this->assertEquals(4, $responseData['pvpUnits'][1]['rank']);
        $this->assertEquals(2, $responseData['pvpUnits'][1]['gradeLevel']);

        // 前哨基地強化データの検証
        $this->assertCount(2, $responseData['usrOutpostEnhancements']);

        // 図鑑効果の検証
        $this->assertCount(2, $responseData['usrEncyclopediaEffects']);
        $this->assertContains('encyclopedia_effect_001', $responseData['usrEncyclopediaEffects'][0]);
        $this->assertContains('encyclopedia_effect_002', $responseData['usrEncyclopediaEffects'][1]);

        // アートワークIDの検証
        $this->assertCount(2, $responseData['mstArtworkIds']);
        $this->assertContains('artwork_001', $responseData['mstArtworkIds']);
        $this->assertContains('artwork_002', $responseData['mstArtworkIds']);
    }

    #[DataProvider('isUseItemValidationProvider')]
    public function testStart_isUseItemパラメータのバリデーション(
        $isUseItemValue,
        bool $shouldBeValid,
        ?string $expectedError = null
    ): void {
        // Setup
        $url = 'start';
        $this->createUsrUser();

        $params = [
            'sysPvpSeasonId' => '1',
            'opponentMyId' => 'test_my_id',
            'partyNo' => 1,
            'inGameBattleLog' => [
                'partyStatus' => [
                    [
                        'usrUnitId' => 'usrUnit1',
                        'mstUnitId' => 'unit1',
                        'color' => 'Red',
                        'roleType' => 'Attack',
                    ]
                ]
            ],
        ];

        // isUseItemを条件に応じて設定
        if ($isUseItemValue !== '__UNSET__') {
            $params['isUseItem'] = $isUseItemValue;
        }

        if (!$shouldBeValid) {
            // バリデーションエラーの場合のモック設定
            $this->mock(PvpStartUseCase::class, function (MockInterface $mock) {
                // モックは呼び出されない
            });

            // Exercise
            $response = $this->sendRequest($url, $params);

            // Verify - バリデーションエラー
            $response->assertStatus(422);
            if ($expectedError) {
                $response->assertJsonValidationErrors(['isUseItem']);
            }
        } else {
            // 正常な場合のモック設定
            $pvpUnits = collect([
                new PvpUnitData('unit_001', 50, 5, 3),
                new PvpUnitData('unit_002', 45, 4, 2),
            ]);

            // ユーザープロフィールデータを作成
            $opponentSelectStatusData = new OpponentSelectStatusData(
                myId: 'test_user',
                name: 'Test User',
                mstUnitId: 'unit_001',
                mstEmblemId: 'emblem_001',
                score: 1000,
                partyPvpUnitDatas: collect([
                    new PvpUnitData('unit_001', 50, 5, 3),
                    new PvpUnitData('unit_002', 45, 4, 2),
                ])
            );

            $opponentPvpStatusData = new OpponentPvpStatusData(
                $opponentSelectStatusData,
                $pvpUnits,
                collect([]),
                collect([new PvpEncyclopediaEffect('effect_001')]),
                collect(['artwork_001'])
            );

            $resultData = new PvpStartResultData($opponentPvpStatusData);

            $this->mock(PvpStartUseCase::class, function (MockInterface $mock) use ($resultData, $isUseItemValue) {
                $mock->shouldReceive('exec')
                    ->once()
                    ->withArgs(function ($user, $sysPvpSeasonId, $isUseItem, $opponentMyId, $partyNo, $inGameBattleLog) use ($isUseItemValue) {
                        return $isUseItem === (bool)$isUseItemValue;
                    })
                    ->andReturn($resultData);
            });

            // Exercise
            $response = $this->sendRequest($url, $params);

            // Verify - 成功
            $response->assertStatus(200);
        }
    }

    public static function isUseItemValidationProvider(): array
    {
        return [
            'isUseItem_1' => [1, true, null],
            'isUseItem_0' => [0, true, null],
        ];
    }

    public function testEnd_リクエストを送ると200OKが返り想定通りのレスポンスが返ることを確認する()
    {
        // setup
        $url = 'end';
        $sysPvpSeasonId = '2025001';
        $params = [
            'sysPvpSeasonId' => $sysPvpSeasonId,
            'inGameBattleLog' => [
                'clearTimeMs' => 12345,
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
            'isWin' => true,
        ];
        $this->createUsrUser();

        $usrPvpStatusData = new UsrPvpStatusData(
            1,
            1,
            PvpRankClassType::BRONZE,
            1,
            3,
            2,
        );
        $pvpResultPoints = new PvpResultPoints(1, 1, 1);
        $pvpTotalScoreRewards = collect([
            new PvpTotalScoreReward(
                RewardType::FREE_DIAMOND->value,
                null,
                100,
                $sysPvpSeasonId,
                'pvp_reward_group_001',
            ),
            new PvpTotalScoreReward(
                RewardType::COIN->value,
                null,
                100,
                $sysPvpSeasonId,
                'pvp_reward_group_002',
            ),
        ]);
        $usrParameter = new UsrParameterData(1, 2, 3, 4, null, 6, 7, 8,);
        $usrItems = collect();
        $usrEmblems = collect();
        $resultData = new PvpEndResultData(
            $usrPvpStatusData,
            $usrParameter,
            $usrItems,
            $usrEmblems,
            $pvpResultPoints,
            $pvpTotalScoreRewards,
        );
        $this->mock(PvpEndUseCase::class, function (MockInterface $mock) use ($resultData) {
            $mock->shouldReceive('exec')->andReturn($resultData);
        });

        // Act: リクエスト送信
        $response = $this->withHeaders([
            System::HEADER_PLATFORM => System::PLATFORM_IOS,
        ])->sendRequest($url, $params);

        // Assert: ステータスとレスポンス構造
        $response->assertStatus(200);
        $response->assertJsonStructure([
            'usrPvpStatus',
            'usrItems',
            'pvpEndResultBonusPoint',
            'pvpTotalScoreRewards',
        ]);
    }

    public function testRanking_リクエストを送ると200OKが返り想定通りのレスポンスが返ることを確認する()
    {
        // setup
        $url = 'ranking';
        $params = [
            'isPreviousSeason' => false,
        ];
        $this->createUsrUser();

        $now = $this->fixTime('2024-01-10 12:00:00');
        $isoWeek = $now->isoWeek();
        $isoWeekYear = $now->isoWeekYear();
        $sysPvpSeasonId = sprintf('%04d0%02d', $isoWeekYear, $isoWeek);
        SysPvpSeason::factory()->create([
            'id' => $sysPvpSeasonId,
            'start_at' => $now->setISODate($isoWeekYear, $isoWeek)->setTime(3, 0, 0),
            'end_at' => $now->setISODate($isoWeekYear, $isoWeek)->addDays(6)->setTime(14, 59, 59),
            'closed_at' => $now->setISODate($isoWeekYear, $isoWeek)->addDays(7)->setTime(2, 59, 59),
        ]);

        $pvpRankingData = new PvpRankingData(
            collect(),
            new PvpMyRankingData(
                1,
                1,
                false,
            ),
        );
        $resultData = new PvpRankingResultData(
            $pvpRankingData,
        );
        $this->mock(PvpTopUseCase::class, function (MockInterface $mock) use ($resultData) {
            $mock->shouldReceive('exec')->andReturn($resultData);
        });

        // Act: リクエスト送信
        $response = $this->sendGetRequest($url, $params);

        // Assert: ステータスとレスポンス構造
        $response->assertStatus(200);
        $response->assertJsonStructure([
            'ranking',
            'myRanking',
        ]);
    }

    public function testResume_リクエストを送ると200OK()
    {
        // setup
        $url = 'resume';
        $params = [];
        $this->createUsrUser();

        $pvpUnits = collect([
            new PvpUnitData('unit_001', 50, 5, 3),
            new PvpUnitData('unit_002', 45, 4, 2),
        ]);

        // ユーザープロフィールデータを作成
        $opponentSelectStatusData = new OpponentSelectStatusData(
            myId: 'test_user',
            name: 'Test User',
            mstUnitId: 'unit_001',
            mstEmblemId: 'emblem_001',
            score: 1000,
            partyPvpUnitDatas: collect([
                new PvpUnitData('unit_001', 50, 5, 3),
                new PvpUnitData('unit_002', 45, 4, 2),
            ]),
            winAddPoint: 10,
        );

        $opponentPvpStatusData = new OpponentPvpStatusData(
            $opponentSelectStatusData,
            $pvpUnits,
            collect([]),
            collect([new PvpEncyclopediaEffect('effect_001')]),
            collect(['artwork_001'])
        );

        $opponentSelectStatusResponse = new OpponentSelectStatusResponseData(
            'test_user',
            'Test User',
            'unit_001',
            'emblem_001',
            1000,
            $opponentPvpStatusData,
            10
        );

        $resultData = new PvpResumeResultData($opponentPvpStatusData, $opponentSelectStatusResponse);

        $this->mock(PvpResumeUseCase::class, function (MockInterface $mock) use ($resultData) {
            $mock->shouldReceive('exec')->andReturn($resultData);
        });

        // Act: リクエスト送信
        $response = $this->sendRequest($url, $params);

        // Assert: ステータスとレスポンス構造
        $response->assertStatus(200);
        $response->assertJsonStructure([
            'opponentSelectStatus' => [
                'myId',
                'name',
                'mstUnitId',
                'mstEmblemId',
                'score',
                'partyPvpUnits' => [
                    '*' => [
                        'mstUnitId',
                        'level',
                        'rank',
                        'gradeLevel',
                    ]
                ],
                'winAddPoint',
            ],
            'opponentPvpStatus' => [
                'pvpUnits' => [
                    '*' => [
                        'mstUnitId',
                        'level',
                        'rank',
                        'gradeLevel',
                    ]
                ],
                'usrOutpostEnhancements' => [
                    '*' => [
                        'mstOutpostId',
                        'mstOutpostEnhancementId',
                        'level',
                    ]
                ],
                'usrEncyclopediaEffects' => [
                    '*' => [
                        'mstEncyclopediaEffectId',
                    ]
                ],
                'mstArtworkIds',
            ]
        ]);

        // partyPvpUnitsが配列として存在し、期待するデータが含まれているかを確認
        $responseData = $response->json();
        $this->assertArrayHasKey('partyPvpUnits', $responseData['opponentSelectStatus']);
        $this->assertIsArray($responseData['opponentSelectStatus']['partyPvpUnits']);
        $this->assertCount(2, $responseData['opponentSelectStatus']['partyPvpUnits']); // テストデータで2つのユニットを設定

        // 各ユニットデータの構造確認
        foreach ($responseData['opponentSelectStatus']['partyPvpUnits'] as $unit) {
            $this->assertArrayHasKey('mstUnitId', $unit);
            $this->assertArrayHasKey('level', $unit);
            $this->assertArrayHasKey('rank', $unit);
            $this->assertArrayHasKey('gradeLevel', $unit);
        }
    }


    public function testAbort_リクエストを送ると200OK()
    {
        // setup
        $url = 'abort';
        $params = [];
        $this->createUsrUser();

        $resultData = new PvpAbortResultData(
            new UsrPvpStatusData(
                1,
                1,
                PvpRankClassType::BRONZE,
                1,
                3,
                2,
            ),
            collect()
        );
        $this->mock(PvpAbortUseCase::class, function (MockInterface $mock) use ($resultData) {
            $mock->shouldReceive('exec')->andReturn($resultData);
        });

        // Act: リクエスト送信
        $response = $this->sendRequest($url, $params);

        // Assert: ステータスとレスポンス構造
        $response->assertStatus(200);
        $response->assertJsonStructure([
            'usrPvpStatus',
            'usrItems',
        ]);
    }

    public function testCleanup_リクエストを送ると200OKが返り想定通りのレスポンスが返ることを確認する()
    {
        // Setup
        $url = 'cleanup';
        $user = $this->createDummyUser();
        $now = $this->fixTime();

        // PVPシーズンIDを生成
        $sysPvpSeasonId = sprintf(
            '%04d0%02d',
            $now->isoWeekYear,
            $now->isoWeek
        );

        // PVPセッションを作成（cleanup対象）
        UsrPvpSession::factory()->create([
            'usr_user_id' => $user->id,
            'sys_pvp_season_id' => $sysPvpSeasonId,
            'created_at' => $now->subMinutes(30),
            'is_valid' => 1,
        ]);

        // UserProfileを作成
        UsrUserProfile::factory()->create([
            'usr_user_id' => $user->id,
        ]);

        $params = []; // パラメータは不要

        // Exercise
        $response = $this->sendRequest($url, $params);

        // Verify
        $response->assertStatus(200);
    }
}
