<?php

namespace Feature\Http\Controllers;

use App\Domain\Common\Enums\Language;
use App\Domain\Common\Utils\StringUtil;
use App\Domain\Gacha\Entities\GachaHistory;
use App\Domain\Gacha\Enums\CostType;
use App\Domain\Gacha\Enums\GachaType;
use App\Domain\Gacha\Models\UsrGacha;
use App\Domain\Gacha\UseCases\GachaHistoryUseCase;
use App\Domain\Item\Models\Eloquent\UsrItem;
use App\Domain\Resource\Dtos\RewardDto;
use App\Domain\Resource\Entities\Rewards\GachaReward;
use App\Domain\Resource\Enums\RewardType;
use App\Domain\Resource\Mst\Models\MstItem;
use App\Domain\Resource\Mst\Models\MstUnit;
use App\Domain\Resource\Mst\Models\OprGacha;
use App\Domain\Resource\Mst\Models\OprGachaI18n;
use App\Domain\Resource\Mst\Models\OprGachaPrize;
use App\Domain\Resource\Mst\Models\OprGachaUseResource;
use App\Domain\Resource\Mst\Models\OprStepUpGacha;
use App\Domain\Resource\Mst\Models\OprStepUpGachaStep;
use App\Domain\Resource\Mst\Models\OprStepUpGachaStepReward;
use App\Domain\User\Models\UsrUserParameter;
use App\Exceptions\HttpStatusCode;
use App\Http\Responses\ResultData\GachaHistoryResultData;
use Mockery\MockInterface;
use Tests\Feature\Http\Controllers\BaseControllerTestCase;
use Tests\Support\Traits\TestLogTrait;

class GachaControllerTest extends BaseControllerTestCase
{
    use TestLogTrait;

    protected string $baseUrl = '/api/gacha/';

    public function testDrawDiamond_リクエストを送ると200OKが返ることを確認する()
    {
        // Setup
        $oprGachaId = 'gacha1';
        $prizeGroupId = 'prize_group_1';
        $fragmentMstItemId = 'fragment1';

        $usrUser = $this->createUsrUser();
        $this->createDiamond($usrUser->getId(), 100);
        UsrUserParameter::factory()->create([
            'usr_user_id' => $usrUser->getId(),
        ]);

        MstItem::factory()->createMany([
            ['id' => $fragmentMstItemId],
        ]);
        $mstUnit = MstUnit::factory()
            ->create(['fragment_mst_item_id' => $fragmentMstItemId])
            ->toEntity();

        OprGacha::factory()->create([
            'id' => $oprGachaId,
            'gacha_type' => GachaType::PREMIUM->value,
            'enable_ad_play' => true,
            'multi_draw_count' => 10,
            'prize_group_id' => $prizeGroupId,
            'ad_play_interval_time' => 1,
            'daily_play_limit_count' => 30,
            'total_play_limit_count' => 100,
            'daily_ad_limit_count' => 3,
            'total_ad_limit_count' => 10,
        ]);
        OprGachaI18n::factory()->create([
            'opr_gacha_id' => $oprGachaId,
            'language' => Language::Ja->value,
        ]);

        OprGachaPrize::factory()->create([
            'group_id' => $prizeGroupId,
            'resource_type' => RewardType::UNIT,
            'resource_id' => $mstUnit->getId()
        ]);
        OprGachaUseResource::factory()->create([
            'opr_gacha_id' => $oprGachaId,
            'cost_type' => CostType::DIAMOND,
            'cost_id' => '',
            'cost_num' => 1,
            'draw_count' => 1,
            'cost_priority' => 2,
        ]);

        // Exercise
        $params = [
            'oprGachaId' => $oprGachaId,
            'drewCount' => 0,
            'playNum' => 1,
            'costNum' => 1,
        ];
        $response = $this->sendRequest('draw/diamond', $params);

        // Verify
        $response->assertStatus(HttpStatusCode::SUCCESS);
        $response->assertJsonStructure([
            'gachaResults' => [
                '*' => [
                    'reward' => [
                        'resourceType',
                        'resourceId',
                        'resourceAmount',
                        'preConversionResource' => [
                            'resourceType',
                            'resourceId',
                            'resourceAmount',
                        ],
                    ],
                ]
            ],
            'usrUnits' => [
                '*' => [
                    'usrUnitId',
                    'mstUnitId',
                    'level',
                    'rank' ,
                    'gradeLevel',
                    'isNewEncyclopedia',
                ]
            ],
            'usrItems' => [
                '*' => [
                    'mstItemId',
                    'amount',
                ]
            ],
            'usrParameter' => [
                'level',
                'exp',
                'coin',
                'stamina',
                'staminaUpdatedAt',
                'freeDiamond',
                'paidDiamondIos',
                'paidDiamondAndroid',
            ],
            'usrGachaUppers' => [
                '*' => [
                    'upperGroup',
                    'upperType',
                    'count'
                ]
            ],
            'usrGacha' => [
                'oprGachaId',
                'adPlayedAt',
                'playedAt',
                'adCount',
                'adDailyCount',
                'count',
                'dailyCount',
                'expiresAt',
            ],
        ]);
    }

    public function testHistory_リクエストを送ると200OKが返ることを確認する()
    {
        // Setup
        $now = $this->fixTime('2024-01-01 00:00:00');
        $oprGachaId = 'gacha_001';
        $costType = 'Item';
        $costId = 'ticket1';
        $costNum = 10;
        $drawCount = 10;
        $sortOrder = 0;
        $resourceType = 'Item';
        $resourceId = 'piece_001';
        $resourceAmount = 5;
        $preConversionResourceType = 'Unit';
        $preConversionResourceId = 'unit_001';
        $preConversionResourceAmount = 1;
        $gachaReward = new GachaReward(
            $preConversionResourceType,
            $preConversionResourceId,
            $preConversionResourceAmount,
            $oprGachaId,
            $sortOrder,
        );
        $gachaReward->setRewardData(new RewardDto(
            $resourceType,
            $resourceId,
            $resourceAmount,
        ));
        $resultData = new GachaHistoryResultData(
            collect([new GachaHistory(
                $oprGachaId,
                $costType,
                $costId,
                $costNum,
                $drawCount,
                $now,
                collect([
                    $gachaReward
                ])
            )]),
        );
        $this->mock(GachaHistoryUseCase::class, function (MockInterface $mock) use ($resultData) {
            $mock->shouldReceive('exec')->andReturn($resultData);
        });

        // Exercise
        $response = $this->sendGetRequest('history');

        // Verify
        $response->assertStatus(HttpStatusCode::SUCCESS);
        $this->assertArrayHasKey('gachaHistories', $response);
        $gachaHistory = $response['gachaHistories'][0];

        $this->assertEquals($oprGachaId, $gachaHistory['oprGachaId']);
        $this->assertEquals($costType, $gachaHistory['costType']);
        $this->assertEquals($costId, $gachaHistory['costId']);
        $this->assertEquals($costNum, $gachaHistory['costNum']);
        $this->assertEquals($drawCount, $gachaHistory['drawCount']);
        $this->assertEquals(StringUtil::convertToISO8601($now->toDateTimeString()), $gachaHistory['playedAt']);
        $result = $gachaHistory['results'][0];
        $this->assertEquals($sortOrder, $result['sortOrder']);
        $reward = $result['reward'];
        $this->assertEquals($resourceType, $reward['resourceType']);
        $this->assertEquals($resourceId, $reward['resourceId']);
        $this->assertEquals($resourceAmount, $reward['resourceAmount']);
        $preConversionResource = $reward['preConversionResource'];
        $this->assertEquals($preConversionResourceType, $preConversionResource['resourceType']);
        $this->assertEquals($preConversionResourceId, $preConversionResource['resourceId']);
        $this->assertEquals($preConversionResourceAmount, $preConversionResource['resourceAmount']);
    }

    public function testDrawItem_ステップアップガシャ_ステップ報酬が返ることを確認する()
    {
        // Setup
        $oprGachaId = 'stepup_gacha_1';
        $prizeGroupId = 'prize_group_stepup_1';
        $costId = 'ticket_stepup';
        $fragmentMstItemId = 'fragment_stepup';

        $usrUser = $this->createUsrUser();
        UsrUserParameter::factory()->create([
            'usr_user_id' => $usrUser->getId(),
        ]);
        $this->createDiamond($usrUser->getId(),);

        MstItem::factory()->createMany([
            ['id' => $fragmentMstItemId],
            ['id' => $costId],
        ]);
        $mstUnit = MstUnit::factory()
            ->create(['fragment_mst_item_id' => $fragmentMstItemId])
            ->toEntity();

        // ステップアップガシャの設定
        $stepUpGacha = OprStepUpGacha::factory()->create([
            'id' => 'stepup_1',
            'opr_gacha_id' => $oprGachaId,
            'max_step_number' => 5,
            'max_loop_count' => 3,
        ]);

        OprGacha::factory()->create([
            'id' => $oprGachaId,
            'gacha_type' => GachaType::STEPUP->value,
            'prize_group_id' => $prizeGroupId,
            'multi_fixed_prize_count' => 0,
            'fixed_prize_group_id' => null,
        ]);
        OprGachaI18n::factory()->create([
            'opr_gacha_id' => $oprGachaId,
            'language' => Language::Ja->value,
        ]);

        // ステップの設定
        OprStepUpGachaStep::factory()->create([
            'opr_gacha_id' => $oprGachaId,
            'step_number' => 1,
            'cost_type' => CostType::ITEM->value,
            'cost_id' => $costId,
            'cost_num' => 1,
            'draw_count' => 1,
            'prize_group_id' => $prizeGroupId,
            'fixed_prize_count' => 0,
            'fixed_prize_group_id' => null,
            'is_first_free' => false,
        ]);

        // ステップ報酬の設定
        OprStepUpGachaStepReward::factory()->create([
            'opr_gacha_id' => $oprGachaId,
            'step_number' => 1,
            'loop_count_target' => null,
            'resource_type' => RewardType::FREE_DIAMOND,
            'resource_id' => null,
            'resource_amount' => 100,
        ]);

        OprGachaPrize::factory()->create([
            'group_id' => $prizeGroupId,
            'resource_type' => RewardType::UNIT,
            'resource_id' => $mstUnit->getId()
        ]);

        // ユーザーアイテム作成
        UsrItem::factory()->create([
            'usr_user_id' => $usrUser->getId(),
            'mst_item_id' => $costId,
            'amount' => 10,
        ]);

        // Exercise
        $params = [
            'oprGachaId' => $oprGachaId,
            'drewCount' => 0,
            'playNum' => 1,
            'costId' => $costId,
            'costNum' => 1,
            'currentStepNumber' => 1,
        ];
        $response = $this->sendRequest('draw/item', $params);

        // Verify
        $response->assertStatus(HttpStatusCode::SUCCESS);
        $response->assertJsonStructure([
            'gachaResults' => [
                '*' => [
                    'reward' => [
                        'resourceType',
                        'resourceId',
                        'resourceAmount',
                    ],
                ]
            ],
            'stepRewards' => [
                '*' => [
                    'reward' => [
                        'resourceType',
                        'resourceAmount',
                    ],
                ]
            ],
        ]);

        // ステップ報酬が正しく返されることを確認
        $this->assertArrayHasKey('stepRewards', $response);
        $this->assertNotEmpty($response['stepRewards']);
        $this->assertEquals(RewardType::FREE_DIAMOND->value, $response['stepRewards'][0]['reward']['resourceType']);
        $this->assertEquals(100, $response['stepRewards'][0]['reward']['resourceAmount']);
    }

    public function testDrawDiamond_ステップアップガシャ_ループ限定のステップ報酬が返ることを確認する()
    {
        // Setup
        $oprGachaId = 'stepup_gacha_2';
        $prizeGroupId = 'prize_group_stepup_2';
        $fragmentMstItemId = 'fragment_stepup_2';

        $usrUser = $this->createUsrUser();
        $this->createDiamond($usrUser->getId(), 1000);
        UsrUserParameter::factory()->create([
            'usr_user_id' => $usrUser->getId(),
        ]);

        MstItem::factory()->create(['id' => $fragmentMstItemId]);
        $mstUnit = MstUnit::factory()
            ->create(['fragment_mst_item_id' => $fragmentMstItemId])
            ->toEntity();

        // ステップアップガシャの設定
        OprStepUpGacha::factory()->create([
            'id' => 'stepup_2',
            'opr_gacha_id' => $oprGachaId,
            'max_step_number' => 3,
            'max_loop_count' => 2,
        ]);

        OprGacha::factory()->create([
            'id' => $oprGachaId,
            'gacha_type' => GachaType::STEPUP->value,
            'prize_group_id' => $prizeGroupId,
        ]);
        OprGachaI18n::factory()->create([
            'opr_gacha_id' => $oprGachaId,
            'language' => Language::Ja->value,
        ]);

        // ステップの設定
        \App\Domain\Resource\Mst\Models\OprStepUpGachaStep::factory()->create([
            'opr_gacha_id' => $oprGachaId,
            'step_number' => 1,
            'cost_type' => CostType::DIAMOND->value,
            'cost_id' => '',
            'cost_num' => 100,
            'draw_count' => 1,
            'prize_group_id' => $prizeGroupId,
            'is_first_free' => false,
        ]);

        // 1周目のみのステップ報酬 (loop_count_target=0)
        \App\Domain\Resource\Mst\Models\OprStepUpGachaStepReward::factory()->create([
            'opr_gacha_id' => $oprGachaId,
            'step_number' => 1,
            'loop_count_target' => 0,
            'resource_type' => RewardType::FREE_DIAMOND,
            'resource_id' => null,
            'resource_amount' => 500,
        ]);

        OprGachaPrize::factory()->create([
            'group_id' => $prizeGroupId,
            'resource_type' => RewardType::UNIT,
            'resource_id' => $mstUnit->getId()
        ]);

        // Exercise
        $params = [
            'oprGachaId' => $oprGachaId,
            'drewCount' => 0,
            'playNum' => 1,
            'costNum' => 100,
            'currentStepNumber' => 1,
        ];
        $response = $this->sendRequest('draw/diamond', $params);

        // Verify
        $response->assertStatus(HttpStatusCode::SUCCESS);
        $this->assertArrayHasKey('stepRewards', $response);
        $this->assertNotEmpty($response['stepRewards']);
        $this->assertEquals(RewardType::FREE_DIAMOND->value, $response['stepRewards'][0]['reward']['resourceType']);
        $this->assertEquals(500, $response['stepRewards'][0]['reward']['resourceAmount']);
    }

    public function testDrawPaidDiamond_ステップアップガシャ_複数のステップ報酬が返ることを確認する()
    {
        // Setup
        $oprGachaId = 'stepup_gacha_3';
        $prizeGroupId = 'prize_group_stepup_3';
        $fragmentMstItemId = 'fragment_stepup_3';
        $itemId = 'bonus_item';

        $usrUser = $this->createUsrUser();
        $this->createDiamond($usrUser->getId(), 1000, 1000, 1000);
        UsrUserParameter::factory()->create([
            'usr_user_id' => $usrUser->getId(),
        ]);
        UsrGacha::factory()->create([
            'usr_user_id' => $usrUser->getId(),
            'opr_gacha_id' => $oprGachaId,
            'current_step_number' => 3,
            'loop_count' => 0,
        ]);

        MstItem::factory()->createMany([
            ['id' => $fragmentMstItemId],
            ['id' => $itemId],
        ]);
        $mstUnit = MstUnit::factory()
            ->create(['fragment_mst_item_id' => $fragmentMstItemId])
            ->toEntity();

        // ステップアップガシャの設定
        OprStepUpGacha::factory()->create([
            'id' => 'stepup_3',
            'opr_gacha_id' => $oprGachaId,
            'max_step_number' => 5,
            'max_loop_count' => 1,
        ]);

        OprGacha::factory()->create([
            'id' => $oprGachaId,
            'gacha_type' => GachaType::STEPUP->value,
            'prize_group_id' => $prizeGroupId,
        ]);
        OprGachaI18n::factory()->create([
            'opr_gacha_id' => $oprGachaId,
            'language' => Language::Ja->value,
        ]);

        // ステップの設定
        OprStepUpGachaStep::factory()->create([
            'opr_gacha_id' => $oprGachaId,
            'step_number' => 3,
            'cost_type' => CostType::PAID_DIAMOND->value,
            'cost_id' => '',
            'cost_num' => 300,
            'draw_count' => 10,
            'prize_group_id' => $prizeGroupId,
            'is_first_free' => false,
        ]);

        // 複数のステップ報酬
        OprStepUpGachaStepReward::factory()->createMany([
            [
                'opr_gacha_id' => $oprGachaId,
                'step_number' => 3,
                'loop_count_target' => null,
                'resource_type' => RewardType::FREE_DIAMOND,
                'resource_id' => null,
                'resource_amount' => 300,
            ],
            [
                'opr_gacha_id' => $oprGachaId,
                'step_number' => 3,
                'loop_count_target' => null,
                'resource_type' => RewardType::ITEM,
                'resource_id' => $itemId,
                'resource_amount' => 5,
            ],
        ]);

        OprGachaPrize::factory()->create([
            'group_id' => $prizeGroupId,
            'resource_type' => RewardType::UNIT,
            'resource_id' => $mstUnit->getId()
        ]);

        // Exercise
        $params = [
            'oprGachaId' => $oprGachaId,
            'drewCount' => 0,
            'playNum' => 10,
            'costNum' => 300,
            'currentStepNumber' => 3,
        ];
        $response = $this->sendRequest('draw/paid_diamond', $params);

        // Verify
        $response->assertStatus(HttpStatusCode::SUCCESS);
        $this->assertArrayHasKey('stepRewards', $response);
        $this->assertCount(2, $response['stepRewards']);

        // ダイヤモンド報酬
        $diamondReward = collect($response['stepRewards'])->firstWhere('reward.resourceType', RewardType::FREE_DIAMOND->value);
        $this->assertNotNull($diamondReward);
        $this->assertEquals(300, $diamondReward['reward']['resourceAmount']);

        // アイテム報酬
        $itemReward = collect($response['stepRewards'])->firstWhere('reward.resourceType', RewardType::ITEM->value);
        $this->assertNotNull($itemReward);
        $this->assertEquals($itemId, $itemReward['reward']['resourceId']);
        $this->assertEquals(5, $itemReward['reward']['resourceAmount']);
    }
}
