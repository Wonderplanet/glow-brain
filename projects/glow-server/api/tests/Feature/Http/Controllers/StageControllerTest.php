<?php

namespace Tests\Feature\Http\Controllers;

use App\Domain\Emblem\Constants\EmblemConstant;
use App\Domain\Resource\Enums\RewardConvertedReason;
use App\Domain\Resource\Enums\RewardType;
use App\Domain\Resource\Log\Enums\LogResourceTriggerSource;
use App\Domain\Resource\Mst\Models\MstEmblem;
use App\Domain\Resource\Mst\Models\MstItem;
use App\Domain\Resource\Mst\Models\MstQuest;
use App\Domain\Resource\Mst\Models\MstStage;
use App\Domain\Resource\Mst\Models\MstStageReward;
use App\Domain\Resource\Mst\Models\MstUserLevel;
use App\Domain\Resource\Mst\Models\OprMasterReleaseControl;
use App\Domain\Stage\Enums\StageRewardCategory;
use App\Domain\Stage\Models\Eloquent\UsrStage;
use App\Domain\Stage\Models\UsrStageSession;
use App\Domain\Stage\UseCases\StageAbortUseCase;
use App\Domain\Stage\UseCases\StageCleanupUseCase;
use App\Domain\Stage\UseCases\StageContinueAdUseCase;
use App\Domain\Stage\UseCases\StageContinueDiamondUseCase;
use App\Domain\Stage\UseCases\StageEndUseCase;
use App\Domain\Stage\UseCases\StageStartUseCase;
use App\Domain\User\Models\UsrUserParameter;
use App\Exceptions\HttpStatusCode;
use App\Http\Responses\Data\UserLevelUpData;
use App\Http\Responses\Data\UsrParameterData;
use App\Http\Responses\Data\UsrStageEnhanceStatusData;
use App\Http\Responses\Data\UsrStageStatusData;
use App\Http\Responses\ResultData\StageAbortResultData;
use App\Http\Responses\ResultData\StageCleanupResultData;
use App\Http\Responses\ResultData\StageContinueAdResultData;
use App\Http\Responses\ResultData\StageContinueResultData;
use App\Http\Responses\ResultData\StageEndResultData;
use App\Http\Responses\ResultData\StageStartResultData;
use Mockery\MockInterface;
use Tests\Support\Traits\TestLogTrait;

class StageControllerTest extends BaseControllerTestCase
{
    use TestLogTrait;

    protected string $baseUrl = '/api/stage/';

    public function test_start_リクエストを送ると200OKが返ることを確認する()
    {
        // Setup
        $usrUserId = $this->createUsrUser()->getId();

        $usrUserParameter = UsrUserParameter::factory()->create([
            'usr_user_id' => $usrUserId,
            'level' => 2,
            'exp' => 3,
            'coin' => 4,
            'stamina' => 5,
            'stamina_updated_at' => now()->sub('1 hour'),
        ]);

        $usrStageSession = UsrStageSession::factory()->create([
            'usr_user_id' => $usrUserId,
            'mst_stage_id' => '1',
            'is_valid' => 1,
            'party_no' => 0,
            'continue_count' => 0,
        ]);

        $usrParameterData = new UsrParameterData(
            $usrUserParameter->level,
            $usrUserParameter->exp,
            $usrUserParameter->coin,
            $usrUserParameter->stamina,
            $usrUserParameter->stamina_updated_at,
            6,
            7,
            8,
        );

        $resultData = new StageStartResultData(
            $usrParameterData,
            new UsrStageStatusData($usrStageSession)
        );
        $this->mock(StageStartUseCase::class, function (MockInterface $mock) use ($resultData) {
            $mock->shouldReceive('exec')->andReturn($resultData);
        });

        // Exercise
        $requestData = [
            'mstStageId' => '1',
            'partyNo' => 0,
            'isChallengeAd' => false,
            'lapCount' => 5,
        ];
        $response = $this->sendRequest('start', $requestData);

        // Verify
        $response->assertStatus(HttpStatusCode::SUCCESS);
    }

    public function test_end_リクエストを送ると200OKが返ることを確認する()
    {
        // Setup
        $resultData = new StageEndResultData(
            new UserLevelUpData(0, 0, collect()),
            collect(),
            collect(),
            collect(),
            collect(),
            collect(),
            collect(),
            collect(),
            collect(),
            collect(),
            collect(),
            collect(),
        );
        $this->mock(StageEndUseCase::class, function (MockInterface $mock) use ($resultData) {
            $mock->shouldReceive('exec')->andReturn($resultData);
        });

        // Exercise
        $requestData = [
            'mstStageId' => '1',
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
        $response = $this->sendRequest('end', $requestData);

        // Verify
        $response->assertStatus(HttpStatusCode::SUCCESS);
    }

    public function test_end_ログ保存ができていることを確認()
    {
        // Setup
        $now = $this->fixTime('2023-01-01 00:00:00');
        $nginxRequestId = 'test_nginx_request_id';
        $this->setNginxRequestId($nginxRequestId);

        $usrUserId = $this->createUsrUser()->getUsrUserId();
        $mstStageId = 'stage1';
        MstQuest::factory()->create([
            'id' => 'quest1',
            'quest_type' => 'normal',
            'start_date' => '2023-01-01 00:00:00',
            'end_date' => '2037-01-01 00:00:00',
        ])->toEntity();
        MstStage::factory()->create([
            'id' => $mstStageId,
            'mst_quest_id' => 'quest1',
            'exp' => 10,
            'coin' => 100,
        ])->toEntity();
        UsrStage::factory()->create([
            'usr_user_id' => $usrUserId,
            'mst_stage_id' => $mstStageId,
            'clear_count' => 0,
        ]);
        UsrStageSession::factory()->create([
            'usr_user_id' => $usrUserId,
            'mst_stage_id' => $mstStageId,
            'is_valid' => 1,
        ]);

        $firstClearRewardData = [
            'mst_stage_id' => $mstStageId,
            'reward_category' => StageRewardCategory::FIRST_CLEAR,
        ];
        MstStageReward::factory()->createMany([
            // 初クリア報酬
            // coin
            $firstClearRewardData + [
                'id' => 'reward1',
                'resource_type' => RewardType::COIN->value,
                'resource_id' => null,
                'resource_amount' => 200,
                'percentage' => 100,
            ],
            $firstClearRewardData + [
                'id' => 'reward2',
                'resource_type' => RewardType::COIN->value,
                'resource_id' => null,
                'resource_amount' => 300,
                'percentage' => 100,
            ],
            // item
            $firstClearRewardData + [
                'id' => 'reward4',
                'resource_type' => RewardType::ITEM->value,
                'resource_id' => 'item1',
                'resource_amount' => 500,
                'percentage' => 100,
            ],
            // exp
            $firstClearRewardData + [
                'id' => 'reward5',
                'resource_type' => RewardType::EXP->value,
                'resource_id' => null,
                'resource_amount' => 600,
                'percentage' => 100,
            ],
            // emblem
            $firstClearRewardData + [
                'id' => 'reward6',
                'resource_type' => RewardType::EMBLEM->value,
                'resource_id' => 'emblem1',
                'resource_amount' => 700,
                'percentage' => 100,
            ],
        ]);

        OprMasterReleaseControl::factory()->create([
            'release_key' => 100,
        ]);

        // other
        MstItem::factory()->createMany([
            ['id' => 'item1'],
        ]);
        MstEmblem::factory()->createMany([
            ['id' => 'emblem1'],
        ]);
        MstUserLevel::factory()->createMany([
            ['level' => 1, 'stamina' => 10, 'exp' => 0],
        ]);
        UsrUserParameter::factory()->create([
            'usr_user_id' => $usrUserId,
            'level' => 1,
            'exp' => 0,
            'coin' => 0,
            'stamina' => 0,
        ]);
        $this->createDiamond($usrUserId);

        // Exercise
        $requestData = [
            'mstStageId' => $mstStageId,
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
        $response = $this->sendRequest('end', $requestData);

        // Verify
        $response->assertStatus(HttpStatusCode::SUCCESS);

        $this->checkLogResourcesByGet(
            usrUserId: $usrUserId,
            nginxRequestId: $nginxRequestId,
            rewardType: RewardType::COIN,
            expectedAmounts: [
                ['before_amount' => 0, 'after_amount' => 100],
                ['before_amount' => 100, 'after_amount' => 300],
                ['before_amount' => 300, 'after_amount' => 600],
                ['before_amount' => 600, 'after_amount' => 600 + ((700 - 1) * EmblemConstant::DUPLICATE_EMBLEM_CONVERT_COIN)],
            ],
            expectedTriggers: [
            [
                'trigger_source' => LogResourceTriggerSource::STAGE_ALWAYS_CLEAR_REWARD->value,
                'trigger_value' => $mstStageId,
                'trigger_option' => '',
            ],
            [
                'trigger_source' => LogResourceTriggerSource::STAGE_FIRST_CLEAR_REWARD->value,
                'trigger_value' => $mstStageId,
                'trigger_option' => '',
            ],
            [
                'trigger_source' => LogResourceTriggerSource::STAGE_FIRST_CLEAR_REWARD->value,
                'trigger_value' => $mstStageId,
                'trigger_option' => '',
            ],
            [
                'trigger_source' => LogResourceTriggerSource::STAGE_FIRST_CLEAR_REWARD->value,
                'trigger_value' => $mstStageId,
                'trigger_option' => RewardConvertedReason::DUPLICATED_EMBLEM->value,
            ],
        ]);
        $this->checkLogResourcesByGet(
            usrUserId: $usrUserId,
            nginxRequestId: $nginxRequestId,
            rewardType: RewardType::EXP,
            expectedAmounts: [
                ['before_amount' => 0, 'after_amount' => 10],
                ['before_amount' => 10, 'after_amount' => 610],
            ],
            expectedTriggers: [
                [
                    'trigger_source' => LogResourceTriggerSource::STAGE_ALWAYS_CLEAR_REWARD->value,
                    'trigger_value' => $mstStageId,
                    'trigger_option' => '',
                ],
                [
                    'trigger_source' => LogResourceTriggerSource::STAGE_FIRST_CLEAR_REWARD->value,
                    'trigger_value' => $mstStageId,
                    'trigger_option' => '',
                ],
            ]
        );
        $this->checkLogResourcesByGet(
            usrUserId: $usrUserId,
            nginxRequestId: $nginxRequestId,
            rewardType: RewardType::ITEM,
            expectedAmounts: [
                'item1' => [['before_amount' => 0, 'after_amount' => 500]],
            ],
            expectedTriggers: [
            [
                'trigger_source' => LogResourceTriggerSource::STAGE_FIRST_CLEAR_REWARD->value,
                'trigger_value' => $mstStageId,
                'trigger_option' => '',
            ],
        ]);
        $this->checkLogResourcesByGet(
            usrUserId: $usrUserId,
            nginxRequestId: $nginxRequestId,
            rewardType: RewardType::EMBLEM,
            expectedAmounts: [
                'emblem1' => [['before_amount' => 0, 'after_amount' => 1]],
            ],
            expectedTriggers: [
            [
                'trigger_source' => LogResourceTriggerSource::STAGE_FIRST_CLEAR_REWARD->value,
                'trigger_value' => $mstStageId,
                'trigger_option' => '',
            ],
        ]);
    }

    public function test_end_スタミナブーストにより報酬に周回番号が振られているか確認()
    {
        // Setup
        $now = $this->fixTime('2023-01-01 00:00:00');
        $nginxRequestId = 'test_nginx_request_id';
        $this->setNginxRequestId($nginxRequestId);

        $lapCount = 3;
        $usrUserId = $this->createUsrUser()->getUsrUserId();
        $mstStageId = 'stage1';
        MstQuest::factory()->create([
            'id' => 'quest1',
            'quest_type' => 'normal',
            'start_date' => '2023-01-01 00:00:00',
            'end_date' => '2037-01-01 00:00:00',
        ])->toEntity();
        MstStage::factory()->create([
            'id' => $mstStageId,
            'mst_quest_id' => 'quest1',
            'exp' => 10,
            'coin' => 100,
        ])->toEntity();
        UsrStage::factory()->create([
            'usr_user_id' => $usrUserId,
            'mst_stage_id' => $mstStageId,
            'clear_count' => 0,
        ]);
        UsrStageSession::factory()->create([
            'usr_user_id' => $usrUserId,
            'mst_stage_id' => $mstStageId,
            'is_valid' => 1,
            'auto_lap_count' => $lapCount,
        ]);

        $firstClearRewardData = [
            'mst_stage_id' => $mstStageId,
            'reward_category' => StageRewardCategory::FIRST_CLEAR,
        ];
        $alwaysClearRewardData = [
            'mst_stage_id' => $mstStageId,
            'reward_category' => StageRewardCategory::ALWAYS,
        ];
        $randomClearRewardData = [
            'mst_stage_id' => $mstStageId,
            'reward_category' => StageRewardCategory::RANDOM,
        ];
        MstStageReward::factory()->createMany([
            // 初クリア報酬
            // coin
            $firstClearRewardData + [
                'id' => 'reward1',
                'resource_type' => RewardType::COIN->value,
                'resource_id' => null,
                'resource_amount' => 200,
                'percentage' => 100,
            ],
            $firstClearRewardData + [
                'id' => 'reward2',
                'resource_type' => RewardType::COIN->value,
                'resource_id' => null,
                'resource_amount' => 300,
                'percentage' => 100,
            ],
            // item
            $firstClearRewardData + [
                'id' => 'reward4',
                'resource_type' => RewardType::ITEM->value,
                'resource_id' => 'item1',
                'resource_amount' => 500,
                'percentage' => 100,
            ],
            // exp
            $firstClearRewardData + [
                'id' => 'reward5',
                'resource_type' => RewardType::EXP->value,
                'resource_id' => null,
                'resource_amount' => 600,
                'percentage' => 100,
            ],
            // emblem
            $firstClearRewardData + [
                'id' => 'reward6',
                'resource_type' => RewardType::EMBLEM->value,
                'resource_id' => 'emblem1',
                'resource_amount' => 700,
                'percentage' => 100,
            ],

            // 常時報酬
            $alwaysClearRewardData + [
                'id' => 'reward10',
                'resource_type' => RewardType::ITEM->value,
                'resource_id' => 'item100',
                'resource_amount' => 1,
                'percentage' => 100,
            ],

            // ランダム報酬
            $randomClearRewardData + [
                'id' => 'reward20',
                'resource_type' => RewardType::ITEM->value,
                'resource_id' => 'item1',
                'resource_amount' => 1,
                'percentage' => 100,
            ],
        ]);

        OprMasterReleaseControl::factory()->create([
            'release_key' => 100,
        ]);

        // other
        MstItem::factory()->createMany([
            ['id' => 'item1'],
            ['id' => 'item100'],
        ]);
        MstEmblem::factory()->createMany([
            ['id' => 'emblem1'],
        ]);
        MstUserLevel::factory()->createMany([
            ['level' => 1, 'stamina' => 10, 'exp' => 0],
        ]);
        UsrUserParameter::factory()->create([
            'usr_user_id' => $usrUserId,
            'level' => 1,
            'exp' => 0,
            'coin' => 0,
            'stamina' => 0,
        ]);
        $this->createDiamond($usrUserId);

        // Exercise
        $requestData = [
            'mstStageId' => $mstStageId,
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
        $response = $this->sendRequest('end', $requestData);

        // Verify
        $response->assertStatus(HttpStatusCode::SUCCESS);
        $original = $response->getOriginalContent();
        $this->assertArrayHasKey('stageRewards', $original);
        $expectedRewards = [
            // lapNumber, rewardCategory, type, id, amount
            [1, 'FirstClear', 'Coin', null, 200],
            [1, 'FirstClear', 'Coin', null, 300],
            [1, 'FirstClear', 'Coin', null, 699000], // emblem重複監禁分
            [1, 'FirstClear', 'Exp', null, 600],
            [1, 'FirstClear', 'Item', 'item1', 500],
            [1, 'FirstClear', 'Emblem', 'emblem1', 1],
            [1, 'Always', 'Coin', null, 100],
            [2, 'Always', 'Coin', null, 100],
            [3, 'Always', 'Coin', null, 100],
            [1, 'Always', 'Exp', null, 10],
            [2, 'Always', 'Exp', null, 10],
            [3, 'Always', 'Exp', null, 10],
            [1, 'Always', 'Item', 'item100', 1],
            [2, 'Always', 'Item', 'item100', 1],
            [3, 'Always', 'Item', 'item100', 1],
            [1, 'Random', 'Item', 'item1', 1],
            [2, 'Random', 'Item', 'item1', 1],
            [3, 'Random', 'Item', 'item1', 1],
        ];

        foreach ($expectedRewards as $index => [$lap, $category, $type, $id, $amount]) {
            $reward = $original['stageRewards'][$index];
            $this->assertSame($lap, $reward['lapNumber']);
            $this->assertSame($category, $reward['rewardCategory']);
            $this->assertSame($type, $reward['reward']['resourceType']);
            $this->assertSame($id, $reward['reward']['resourceId']);
            $this->assertSame($amount, $reward['reward']['resourceAmount']);
        }
    }

    public function test_continueDiamond_リクエストを送ると200OKが返ることを確認する()
    {
        // Setup
        $usrUserId = $this->createUsrUser()->getId();

        $usrUserParameter = UsrUserParameter::factory()->create([
            'usr_user_id' => $usrUserId,
            'level' => 2,
            'exp' => 3,
            'coin' => 4,
            'stamina' => 5,
            'stamina_updated_at' => now()->sub('1 hour'),
        ]);

        $usrStageSession = UsrStageSession::factory()->create([
            'usr_user_id' => $usrUserId,
            'mst_stage_id' => '1',
            'is_valid' => 1,
            'party_no' => 0,
            'continue_count' => 0,
        ]);

        $usrParameterData = new UsrParameterData(
            $usrUserParameter->level,
            $usrUserParameter->exp,
            $usrUserParameter->coin,
            $usrUserParameter->stamina,
            $usrUserParameter->stamina_updated_at,
            6,
            7,
            8,
        );

        $resultData = new StageContinueResultData(
            $usrParameterData,
            new UsrStageStatusData($usrStageSession)
        );
        $this->mock(StageContinueDiamondUseCase::class, function (MockInterface $mock) use ($resultData) {
            $mock->shouldReceive('exec')->andReturn($resultData);
        });

        // Exercise
        $requestData = [
            'mstStageId' => '1',
        ];
        $response = $this->sendRequest('continue_diamond', $requestData);

        // Verify
        $response->assertStatus(HttpStatusCode::SUCCESS);
    }

    public function test_continueADd_リクエストを送ると200OKが返ることを確認する()
    {
        // Setup
        $usrUserId = $this->createUsrUser()->getId();

        $usrStageSession = UsrStageSession::factory()->create([
            'usr_user_id' => $usrUserId,
            'mst_stage_id' => '1',
            'is_valid' => 1,
            'party_no' => 0,
            'continue_count' => 0,
            'daily_continue_ad_count' => 0,
            'latest_reset_at' => now()->toDateTimeString(),
        ]);

        $resultData = new StageContinueAdResultData(new UsrStageStatusData($usrStageSession));
        $this->mock(StageContinueAdUseCase::class, function (MockInterface $mock) use ($resultData) {
            $mock->shouldReceive('exec')->andReturn($resultData);
        });

        // Exercise
        $requestData = [
            'mstStageId' => '1',
        ];
        $response = $this->sendRequest('continue_ad', $requestData);

        // Verify
        $response->assertStatus(HttpStatusCode::SUCCESS);
    }

    public function test_abort_リクエストを送ると200OKが返ることを確認する()
    {
        // Setup
        $this->mock(StageAbortUseCase::class, function (MockInterface $mock) {
            $mock->shouldReceive('exec')->andReturn(new StageAbortResultData());
        });

        // Exercise
        $response = $this->sendRequest('abort');

        // Verify
        $response->assertStatus(HttpStatusCode::SUCCESS);
    }

    public function test_cleanup_リクエストを送ると200OKが返ることを確認する()
    {
        // Setup
        $usrUserId = $this->createUsrUser()->getId();

        $usrStageSession = UsrStageSession::factory()->create([
            'usr_user_id' => $usrUserId,
            'mst_stage_id' => '1',
            'is_valid' => 1,
            'party_no' => 0,
            'continue_count' => 0,
        ]);

        $usrStageEnhanceStatusData = new UsrStageEnhanceStatusData(
            $usrStageSession->getMstStageId(),
            10,
            5,
            2,
        );

        $resultData = new StageCleanupResultData(
            $usrStageEnhanceStatusData,
        );
        $this->mock(StageCleanupUseCase::class, function (MockInterface $mock) use ($resultData) {
            $mock->shouldReceive('exec')->andReturn($resultData);
        });

        // Exercise
        $requestData = []; // パラメータは不要
        $response = $this->sendRequest('cleanup', $requestData);

        // Verify
        $response->assertStatus(HttpStatusCode::SUCCESS);
    }
}
