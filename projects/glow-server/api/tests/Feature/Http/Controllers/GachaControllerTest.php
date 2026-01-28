<?php

namespace Feature\Http\Controllers;

use App\Domain\Common\Enums\Language;
use App\Domain\Common\Utils\StringUtil;
use App\Domain\Gacha\Entities\GachaHistory;
use App\Domain\Gacha\Enums\CostType;
use App\Domain\Gacha\Enums\GachaType;
use App\Domain\Gacha\UseCases\GachaHistoryUseCase;
use App\Domain\Resource\Dtos\RewardDto;
use App\Domain\Resource\Entities\Rewards\GachaReward;
use App\Domain\Resource\Enums\RewardType;
use App\Domain\Resource\Mst\Models\MstItem;
use App\Domain\Resource\Mst\Models\MstUnit;
use App\Domain\Resource\Mst\Models\OprGacha;
use App\Domain\Resource\Mst\Models\OprGachaI18n;
use App\Domain\Resource\Mst\Models\OprGachaPrize;
use App\Domain\Resource\Mst\Models\OprGachaUseResource;
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
}
