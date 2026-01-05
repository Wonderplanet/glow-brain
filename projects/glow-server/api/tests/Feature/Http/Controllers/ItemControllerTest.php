<?php

namespace Tests\Feature\Http\Controllers;

use App\Domain\Common\Utils\StringUtil;
use App\Domain\Item\Models\Eloquent\UsrItem;
use App\Domain\Item\Models\UsrItemTrade;
use App\Domain\Item\UseCases\ItemConsumeUseCase;
use App\Domain\Item\UseCases\ItemExchangeSelectItemUseCase;
use App\Domain\Resource\Entities\Rewards\ItemReward;
use App\Domain\Resource\Entities\Rewards\ItemTradeReward;
use App\Domain\Resource\Enums\RewardType;
use App\Domain\Resource\Log\Enums\LogResourceTriggerSource;
use App\Domain\User\Models\UsrUser;
use App\Domain\User\Models\UsrUserParameter;
use App\Exceptions\HttpStatusCode;
use App\Http\Responses\Data\UsrParameterData;
use App\Http\Responses\ResultData\ItemConsumeResultData;
use App\Http\Responses\ResultData\ItemExchangeSelectItemResultData;
use Mockery\MockInterface;

class ItemControllerTest extends BaseControllerTestCase
{
    protected string $baseUrl = '/api/item/';

    public function test_consume_ステータス200で想定通りのレスポンスが返ってくる()
    {
        $consumeMstItemId = 'test_item_2';
        $now = $this->fixTime();

        $usrUser = UsrUser::factory()->create([
            'id' => fake()->uuid(),
            'tutorial_status' => 0,
        ]);
        $usrUserParameter = UsrUserParameter::factory()->create([
            'usr_user_id' => $usrUser->getId(),
            'level' => 2,
            'exp' => 3,
            'coin' => 4,
            'stamina' => 5,
            'stamina_updated_at' => now()->sub('1 hour'),
        ]);
        $this->createDiamond($usrUser->getId(), 6, 7, 8);

        $usrItems = collect();
        for ($i = 1; $i <= 3; $i++) {
            $usrItems->push(UsrItem::factory()->create([
                    'usr_user_id' => $usrUser->getId(),
                    'mst_item_id' => 'test_item_'. $i,
                    'amount' => 10,
            ]));
        }

        $itemRewards = collect();
        for ($i = 1; $i <= 3; $i++) {
            $itemRewards->push(new ItemReward(
                RewardType::ITEM->value,
                'test_item_'. $i,
                $i,
                $consumeMstItemId,
            ));
        }
        $itemTradeRewards = collect([new ItemTradeReward(
            RewardType::ITEM->value,
            'test_item_4',
            4,
            LogResourceTriggerSource::ITEM_TRADE_CHARACTER_FRAGMENT_TO_SELECTION_FRAGMENT_BOX,
            $consumeMstItemId,
        )]);

        $usrParameter = new UsrParameterData(
            $usrUserParameter->level,
            $usrUserParameter->exp,
            $usrUserParameter->coin,
            $usrUserParameter->stamina,
            $usrUserParameter->stamina_updated_at,
            6,
            7,
            8,
        );

        $usrItemTrade = UsrItemTrade::factory()->create([
            'usr_user_id' => $usrUser->getId(),
            'mst_item_id' => 'tradedItem',
            'trade_amount' => 2,
            'reset_trade_amount' => 1,
            'trade_amount_reset_at' => $now->toDateTimeString(),
        ]);

        $resultData = new ItemConsumeResultData(
            $usrParameter,
            $usrItems,
            $itemRewards,
            $itemTradeRewards,
            $usrItemTrade,
        );

        $this->mock(ItemConsumeUseCase::class, function (MockInterface $mock) use ($resultData) {
            $mock->shouldReceive('exec')->andReturn($resultData);
        });

        $requestData = [
            'mstItemId' => $consumeMstItemId,
            'amount' => 1,
        ];
        $response = $this->sendRequest('consume', $requestData);
        $response->assertStatus(HttpStatusCode::SUCCESS);

        $this->assertEquals($usrUserParameter->getLevel(), $response['usrParameter']['level']);
        $this->assertEquals($usrUserParameter->getExp(), $response['usrParameter']['exp']);
        $this->assertEquals($usrUserParameter->getCoin(), $response['usrParameter']['coin']);
        $this->assertEquals($usrUserParameter->getStamina(), $response['usrParameter']['stamina']);
        $this->assertEquals(StringUtil::convertToISO8601($usrUserParameter->getStaminaUpdatedAt()), $response['usrParameter']['staminaUpdatedAt']);

        $this->assertEquals('test_item_1', $response['usrItems'][0]['mstItemId']);
        $this->assertEquals(10, $response['usrItems'][0]['amount']);
        $this->assertEquals($consumeMstItemId, $response['usrItems'][1]['mstItemId']);
        $this->assertEquals(10, $response['usrItems'][1]['amount']);
        $this->assertEquals('test_item_3', $response['usrItems'][2]['mstItemId']);
        $this->assertEquals(10, $response['usrItems'][2]['amount']);

        $this->assertEquals(RewardType::ITEM->value, $response['itemRewards'][0]['reward']['resourceType']);
        $this->assertEquals('test_item_1', $response['itemRewards'][0]['reward']['resourceId']);
        $this->assertEquals(1, $response['itemRewards'][0]['reward']['resourceAmount']);
        $this->assertEquals(RewardType::ITEM->value, $response['itemRewards'][1]['reward']['resourceType']);
        $this->assertEquals($consumeMstItemId, $response['itemRewards'][1]['reward']['resourceId']);
        $this->assertEquals(2, $response['itemRewards'][1]['reward']['resourceAmount']);
        $this->assertEquals(RewardType::ITEM->value, $response['itemRewards'][2]['reward']['resourceType']);
        $this->assertEquals('test_item_3', $response['itemRewards'][2]['reward']['resourceId']);
        $this->assertEquals(3, $response['itemRewards'][2]['reward']['resourceAmount']);
        $this->assertEquals('test_item_4', $response['itemRewards'][3]['reward']['resourceId']);
        $this->assertEquals(4, $response['itemRewards'][3]['reward']['resourceAmount']);

        $this->assertEquals($usrItemTrade->getMstItemId(), $response['usrItemTrade']['mstItemId']);
        $this->assertEquals($usrItemTrade->getResetTradeAmount(), $response['usrItemTrade']['tradeAmount']);
        $this->assertEquals(
            StringUtil::convertToISO8601($usrItemTrade->getTradeAmountResetAt()),
            $response['usrItemTrade']['tradeAmountResetAt'],
        );
    }

    public function test_exchange_select_item_ステータス200で想定通りのレスポンスが返ってくる()
    {
        $consumeMstItemId = 'test_item_1';

        $usrUser = UsrUser::factory()->create([
            'id' => fake()->uuid(),
            'tutorial_status' => 0,
        ]);

        $usrItems = collect();
        for ($i = 1; $i <= 3; $i++) {
            $usrItems->push(UsrItem::factory()->create([
                    'usr_user_id' => $usrUser->getId(),
                    'mst_item_id' => 'test_item_'. $i,
                    'amount' => 10,
            ]));
        }

        $itemRewards = collect();
        for ($i = 1; $i <= 3; $i++) {
            $itemRewards->push(new ItemReward(
                RewardType::ITEM->value,
                'test_item_'. $i,
                $i,
                $consumeMstItemId,
            ));
        }

        $resultData = new ItemExchangeSelectItemResultData(
            $usrItems,
            $itemRewards,
        );

        $this->mock(ItemExchangeSelectItemUseCase::class, function (MockInterface $mock) use ($resultData) {
            $mock->shouldReceive('exec')->andReturn($resultData);
        });

        $requestData = [
            'mstItemId' => $consumeMstItemId,
            'selectMstItemId' => 'test_item_2',
            'amount' => 1,
        ];
        $response = $this->sendRequest('exchange_select_item', $requestData);
        $response->assertStatus(HttpStatusCode::SUCCESS);

        $this->assertEquals($consumeMstItemId, $response['usrItems'][0]['mstItemId']);
        $this->assertEquals(10, $response['usrItems'][0]['amount']);
        $this->assertEquals('test_item_2', $response['usrItems'][1]['mstItemId']);
        $this->assertEquals(10, $response['usrItems'][1]['amount']);
        $this->assertEquals('test_item_3', $response['usrItems'][2]['mstItemId']);
        $this->assertEquals(10, $response['usrItems'][2]['amount']);

        $this->assertEquals(RewardType::ITEM->value, $response['itemRewards'][0]['reward']['resourceType']);
        $this->assertEquals($consumeMstItemId, $response['itemRewards'][0]['reward']['resourceId']);
        $this->assertEquals(1, $response['itemRewards'][0]['reward']['resourceAmount']);
        $this->assertEquals(RewardType::ITEM->value, $response['itemRewards'][1]['reward']['resourceType']);
        $this->assertEquals('test_item_2', $response['itemRewards'][1]['reward']['resourceId']);
        $this->assertEquals(2, $response['itemRewards'][1]['reward']['resourceAmount']);
        $this->assertEquals(RewardType::ITEM->value, $response['itemRewards'][2]['reward']['resourceType']);
        $this->assertEquals('test_item_3', $response['itemRewards'][2]['reward']['resourceId']);
        $this->assertEquals(3, $response['itemRewards'][2]['reward']['resourceAmount']);
    }
}
