<?php

namespace Tests\Feature\Log;

use App\Domain\Outpost\Models\UsrOutpost;
use App\Domain\Party\Models\Eloquent\UsrParty;
use App\Domain\Resource\Mst\Models\MstQuest;
use App\Domain\Resource\Mst\Models\MstStage;
use App\Domain\Resource\Mst\Models\MstUnit;
use App\Domain\Resource\Mst\Models\MstUserLevel;
use App\Domain\Stage\Enums\LogStageResult;
use App\Domain\Stage\Enums\StageAutoLapType;
use App\Domain\Stage\Models\Eloquent\UsrStage;
use App\Domain\Stage\Models\LogStageAction;
use App\Domain\Stage\Models\UsrStageSession;
use App\Domain\Unit\Models\Eloquent\UsrUnit;
use App\Domain\User\Models\UsrUserParameter;
use App\Exceptions\HttpStatusCode;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\Feature\Http\Controllers\BaseControllerTestCase;
use Tests\Support\Traits\TestLogTrait;

class LogStageActionTest extends BaseControllerTestCase
{
    use TestLogTrait;

    protected string $baseUrl = '/api/stage/';

    public function setUp(): void
    {
        parent::setUp();
    }

    private function createTestData(string $usrUserId, string $mstQuestId, string $mstStageId): array
    {
        // ステージデータ用意
        MstQuest::factory()->create([
            'id' => $mstQuestId,
        ]);
        MstStage::factory()->create([
            'id' => $mstStageId,
            'mst_quest_id' => $mstQuestId,
            'cost_stamina' => 5,
            'auto_lap_type' => StageAutoLapType::INITIAL->value,
            'max_auto_lap_count' => 10,
        ])->toEntity();
        UsrStage::factory()->create([
            'usr_user_id' => $usrUserId,
            'mst_stage_id' => $mstStageId,
            'clear_count' => 0,
        ]);

        // パーティデータ用意
        UsrParty::factory()->createMany([
            [
                'usr_user_id' => $usrUserId,
                'party_no' => 1,
                // パーティ内の順序も考慮して保存できるようにusrUnit2を1番目にしている
                'usr_unit_id_1' => 'usrUnit2',
                'usr_unit_id_2' => 'usrUnit1'
            ],
        ]);
        MstUnit::factory()->createMany([
            ['id' => 'unit1'],
            ['id' => 'unit2'],
        ]);
        $usrUnits = UsrUnit::factory()->createManyAndConvert([
            ['id' => 'usrUnit1', 'usr_user_id' => $usrUserId, 'mst_unit_id' => 'unit1'],
            ['id' => 'usrUnit2', 'usr_user_id' => $usrUserId, 'mst_unit_id' => 'unit2'],
        ])->keyBy(fn($usrUnit) => $usrUnit->getId());

        // ゲートデータ用意
        $mstOutpostId = 'outpost1';
        $usrOutpost = UsrOutpost::factory()->create([
            'usr_user_id' => $usrUserId,
            'mst_outpost_id' => $mstOutpostId,
            'mst_artwork_id' => 'artwork1',
            'is_used' => 1,
        ]);

        UsrUserParameter::factory()->create([
            'usr_user_id' => $usrUserId,
            'level' => 1,
            'stamina' => 100,
        ]);
        $this->createDiamond($usrUserId);
        MstUserLevel::factory()->createMany([
            ['level' => 1]
        ]);

        return [$usrUnits, $usrOutpost];
    }

    public function params_test_start_ステージに挑戦しステージアクションログが保存される()
    {
        return [
            'スタミナブーストなし' => ['lapCount' => 1],
            'スタミナブーストあり' => ['lapCount' => 5],
        ];
    }

    #[DataProvider('params_test_start_ステージに挑戦しステージアクションログが保存される')]
    public function test_start_ステージに挑戦しステージアクションログが保存される(
        int $lapCount,
    ) {
        // Setup
        $now = $this->fixTime('2023-01-01 00:00:00');
        $nginxRequestId = __FUNCTION__;
        $this->setNginxRequestId($nginxRequestId);

        $usrUserId = $this->createUsrUser()->getId();

        $mstStageId = 'stage1';
        $mstQuestId = 'quest1';
        [
            $usrUnits,
            $usrOutpost,
        ] = $this->createTestData($usrUserId, $mstQuestId, $mstStageId);

        // Exercise
        $requestData = [
            'mstStageId' => $mstStageId,
            'partyNo' => 1,
            'isChallengeAd' => false,
            'lapCount' => $lapCount
        ];
        $response = $this->sendRequest('start', $requestData);

        // Verify
        $response->assertStatus(HttpStatusCode::SUCCESS);

        $logModels = LogStageAction::query()
            ->where('usr_user_id', $usrUserId)
            ->where('nginx_request_id', $nginxRequestId)
            ->get();
        $this->assertCount(1, $logModels);

        $actual = $logModels->first();
        $this->assertEquals($mstStageId, $actual->mst_stage_id);
        $this->assertEquals('api/stage/start', $actual->api_path);
        $this->assertEquals(LogStageResult::UNDETERMINED->value, $actual->result);
        $this->assertEquals($lapCount, $actual->auto_lap_count);
    }

    public function params_test_end_ステージクリアしてステージアクションログが保存される()
    {
        return [
            'スタミナブーストなし' => ['lapCount' => 1],
            'スタミナブーストあり' => ['lapCount' => 5],
        ];
    }

    #[DataProvider('params_test_end_ステージクリアしてステージアクションログが保存される')]
    public function test_end_ステージクリアしてステージアクションログが保存される(
        int $lapCount,
    ) {
        // Setup
        $now = $this->fixTime('2023-01-01 00:00:00');
        $nginxRequestId = 'test_nginx_request_id';
        $this->setNginxRequestId($nginxRequestId);

        $usrUserId = $this->createUsrUser()->getId();

        $mstStageId = 'stage1';
        $mstQuestId = 'quest1';
        [
            $usrUnits,
            $usrOutpost,
        ] = $this->createTestData($usrUserId, $mstQuestId, $mstStageId);

        UsrStageSession::factory()->create([
            'usr_user_id' => $usrUserId,
            'mst_stage_id' => $mstStageId,
            'is_valid' => 1,
            'party_no' => 1,
            'auto_lap_count' => $lapCount,
        ]);

        // Exercise
        $requestData = [
            'mstStageId' => $mstStageId,
            'inGameBattleLog' => [
                'defeatEnemyCount' => 50,
                'defeatBossEnemyCount' => 2,
                'score' => 999,
                'clearTimeMs' => 10000,
                'discoveredEnemies' => [
                    ['mstEnemyCharacterId' => 'enemy1', 'count' => 5],
                    ['mstEnemyCharacterId' => 'enemy2', 'count' => 3],
                ],
                'partyStatus' => [
                    [
                        'usrUnitId' => 'userUnit1',
                        'mstUnitId' => 'unit1',
                        'color' => 'Red',
                        'roleType' => 'Attack',
                        'hp' => 100,
                        'atk' => 101,
                        'moveSpeed' => 102,
                        'summonCost' => 103,
                        'summonCoolTime' => 104,
                        'damageKnockBackCount' => 1,
                        'specialAttackMstAttackId' => 'attack1',
                        'attackDelay' => 105,
                        'nextAttackInterval' => 106,
                        'mstUnitAbility1' => 'ability1',
                        'mstUnitAbility2' => 'ability2',
                        'mstUnitAbility3' => 'ability3',
                    ],
                    [
                        'usrUnitId' => 'userUnit2',
                        'mstUnitId' => 'unit2',
                        'color' => 'Blue',
                        'roleType' => 'Attack',
                        'hp' => 107,
                        'atk' => 108,
                        'moveSpeed' => 109,
                        'summonCost' => 110,
                        'summonCoolTime' => 111,
                        'damageKnockBackCount' => 1,
                        'specialAttackMstAttackId' => 'attack2',
                        'attackDelay' => 112,
                        'nextAttackInterval' => 113,
                        'mstUnitAbility1' => 'ability1',
                        'mstUnitAbility2' => 'ability2',
                        'mstUnitAbility3' => 'ability3',
                    ],
                ],
            ]
        ];
        $response = $this->sendRequest('end', $requestData);

        // Verify
        $response->assertStatus(HttpStatusCode::SUCCESS);

        $logModels = LogStageAction::query()
            ->where('usr_user_id', $usrUserId)
            ->where('nginx_request_id', $nginxRequestId)
            ->get();
        $this->assertCount(1, $logModels);

        $actual = $logModels->first();
        $this->assertEquals($mstStageId, $actual->mst_stage_id);
        $this->assertEquals('api/stage/end', $actual->api_path);
        $this->assertEquals(LogStageResult::VICTORY->value, $actual->result);
        $this->assertEquals('outpost1', $actual->mst_outpost_id);
        $this->assertEquals('artwork1', $actual->mst_artwork_id);
        $this->assertEquals(50, $actual->defeat_enemy_count);
        $this->assertEquals(2, $actual->defeat_boss_enemy_count);
        $this->assertEquals(999, $actual->score);
        $this->assertEquals(10000, $actual->clear_time_ms);
        $this->assertEquals(
            [
                ['mst_enemy_character_id' => 'enemy1', 'count' => 5],
                ['mst_enemy_character_id' => 'enemy2', 'count' => 3],
            ],
            json_decode($actual->discovered_enemies, true) ?? []
        );
        $this->assertEquals(
            [
                [
                    'usr_unit_id' => 'userUnit1',
                    'mst_unit_id' => 'unit1',
                    'color' => 'Red',
                    'role_type' => 'Attack',
                    'hp' => 100,
                    'atk' => 101,
                    'move_speed' => 102,
                    'summon_cost' => 103,
                    'summon_cool_time' => 104,
                    'damage_knock_back_count' => 1,
                    'special_attack_mst_attack_id' => 'attack1',
                    'attack_delay' => 105,
                    'next_attack_interval' => 106,
                    'mst_unit_ability1' => 'ability1',
                    'mst_unit_ability2' => 'ability2',
                    'mst_unit_ability3' => 'ability3',
                ],
                [
                    'usr_unit_id' => 'userUnit2',
                    'mst_unit_id' => 'unit2',
                    'color' => 'Blue',
                    'role_type' => 'Attack',
                    'hp' => 107,
                    'atk' => 108,
                    'move_speed' => 109,
                    'summon_cost' => 110,
                    'summon_cool_time' => 111,
                    'damage_knock_back_count' => 1,
                    'special_attack_mst_attack_id' => 'attack2',
                    'attack_delay' => 112,
                    'next_attack_interval' => 113,
                    'mst_unit_ability1' => 'ability1',
                    'mst_unit_ability2' => 'ability2',
                    'mst_unit_ability3' => 'ability3',
                ]
            ],
            json_decode($actual->party_status, true) ?? []
        );
        $this->assertEquals($lapCount, $actual->auto_lap_count);
    }

    #[DataProvider('params_test_abort_ステージ中断してステージアクションログが保存される')]
    public function test_abort_ステージ中断してステージアクションログが保存される(
        ?int $stageAbortType,
        int $logStageResultValue,
    ) {
        // Setup
        $now = $this->fixTime('2023-01-01 00:00:00');
        $nginxRequestId = 'test_nginx_request_id';
        $this->setNginxRequestId($nginxRequestId);

        $usrUserId = $this->createUsrUser()->getId();

        $mstStageId = 'stage1';
        $mstQuestId = 'quest1';
        [
            $usrUnits,
            $usrOutpost,
        ] = $this->createTestData($usrUserId, $mstQuestId, $mstStageId);
        $lapCount = 3;

        UsrStageSession::factory()->create([
            'usr_user_id' => $usrUserId,
            'mst_stage_id' => $mstStageId,
            'is_valid' => 1,
            'party_no' => 1,
            'auto_lap_count' => $lapCount,
        ]);

        // Exercise
        $requestData = [];
        if ($stageAbortType !== null) {
            $requestData['abortType'] = $stageAbortType;
        }
        $response = $this->sendRequest('abort', $requestData);

        // Verify
        $response->assertStatus(HttpStatusCode::SUCCESS);

        $logModels = LogStageAction::query()
            ->where('usr_user_id', $usrUserId)
            ->where('nginx_request_id', $nginxRequestId)
            ->get();
        $this->assertCount(1, $logModels);

        $actual = $logModels->first();
        $this->assertEquals($mstStageId, $actual->mst_stage_id);
        $this->assertEquals('api/stage/abort', $actual->api_path);
        $this->assertEquals($logStageResultValue, $actual->result);
        $this->assertEquals($lapCount, $actual->auto_lap_count);
    }

    public static function params_test_abort_ステージ中断してステージアクションログが保存される()
    {
        return [
            '有効 敗北(DEFEAT)' => [
                'stageAbortType' => LogStageResult::DEFEAT->value,
                'logStageResultValue' => LogStageResult::DEFEAT->value,
            ],
            '有効 リタイア(RETIRE)' => [
                'stageAbortType' => LogStageResult::RETIRE->value,
                'logStageResultValue' => LogStageResult::RETIRE->value,
            ],
            '有効 中断復帰(CANCEL)' => [
                'stageAbortType' => LogStageResult::CANCEL->value,
                'logStageResultValue' => LogStageResult::CANCEL->value,
            ],
            '無効 指定なし' => [
                'stageAbortType' => null,
                'logStageResultValue' => LogStageResult::NONE->value,
            ],
            '無効 未実装なケース' => [
                'stageAbortType' => 9999999999,
                'logStageResultValue' => LogStageResult::NONE->value,
            ],
            '無効 勝利(VICTORY)' => [
                'stageAbortType' => LogStageResult::VICTORY->value,
                'logStageResultValue' => LogStageResult::NONE->value,
            ],
        ];
    }
}
