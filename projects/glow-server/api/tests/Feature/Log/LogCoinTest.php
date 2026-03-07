<?php

namespace Tests\Feature\Http\Controllers;

use App\Domain\Outpost\Enums\OutpostEnhancementType;
use App\Domain\Outpost\Models\LogOutpostEnhancement;
use App\Domain\Resource\Enums\RewardType;
use App\Domain\Resource\Log\Enums\LogResourceTriggerSource;
use App\Domain\Resource\Mst\Models\MstOutpost;
use App\Domain\Resource\Mst\Models\MstOutpostEnhancement;
use App\Domain\Resource\Mst\Models\MstOutpostEnhancementLevel;
use App\Domain\Resource\Mst\Models\MstShopItem;
use App\Domain\Resource\Mst\Models\MstUnit;
use App\Domain\Resource\Mst\Models\MstUnitLevelUp;
use App\Domain\Shop\Enums\ShopItemCostType;
use App\Domain\Shop\Enums\ShopType;
use App\Domain\Unit\Models\LogUnitLevelUp;
use App\Domain\Unit\Models\Eloquent\UsrUnit;
use App\Domain\User\Models\UsrUserParameter;
use App\Exceptions\HttpStatusCode;
use Tests\Support\Traits\TestLogTrait;

class LogCoinTest extends BaseControllerTestCase
{
    use TestLogTrait;

    protected string $baseUrl = '/api/';

    public function setUp(): void
    {
        parent::setUp();
    }

    public function test_unit_levelUp_1から3へレベルアップしコイン消費ログが保存される()
    {
        // Setup
        $now = $this->fixTime('2023-01-01 00:00:00');
        $nginxRequestId = __FUNCTION__;
        $this->setNginxRequestId($nginxRequestId);

        $usrUserId = $this->createUsrUser()->getId();

        $usrUnit = UsrUnit::factory()->create([
            'usr_user_id' => $usrUserId,
            'mst_unit_id' => 'unit1',
            'level' => 1,
            'rank' => 1,
            'grade_level' => 1,
        ]);
        $unitLabel = 'DropR';
        MstUnit::factory()->create([
            'id' => 'unit1',
            'unit_label' => $unitLabel,
        ]);
        MstUnitLevelUp::factory()->createMany([
            [
                'unit_label' => $unitLabel,
                'level' => 2,
                'required_coin' => 200,
            ],
            [
                'unit_label' => $unitLabel,
                'level' => 3,
                'required_coin' => 300,
            ],
        ]);

        UsrUserParameter::factory()->create([
            'usr_user_id' => $usrUserId,
            'level' => 1,
            'coin' => 1000,
        ]);
        $this->createDiamond($usrUserId);

        // Exercise
        $requestData = [
            'usrUnitId' => $usrUnit->getId(),
            'level' => 3,
        ];
        $response = $this->sendRequest('unit/level_up', $requestData);

        // Verify
        $response->assertStatus(HttpStatusCode::SUCCESS);

        // log_unit_level_upsとlog_coinsにレコード1つずつ追加される
        $this->checkLoggingNo($usrUserId, 2);

        $logUnitLevelUp = LogUnitLevelUp::query()
            ->where('usr_user_id', $usrUserId)
            ->first();
        $this->assertNotNull($logUnitLevelUp);

        $this->checkLogResourcesByUse(
            usrUserId: $usrUserId,
            nginxRequestId: $nginxRequestId,
            rewardType: RewardType::COIN,
            expectedAmounts: [
                ['before_amount' => 1000, 'after_amount' => 500],
            ],
            expectedTriggers: [
                [
                    'trigger_source' => 'log_unit_level_ups',
                    'trigger_value' => $logUnitLevelUp->getId(),
                    'trigger_option' => '',
                ],
            ]
        );
    }

    public function test_outpost_enhance_1から3へレベルアップしコイン消費ログが保存される()
    {
        // Setup
        $now = $this->fixTime('2023-01-01 00:00:00');
        $nginxRequestId = __FUNCTION__;
        $this->setNginxRequestId($nginxRequestId);

        $usrUserId = $this->createUsrUser()->getId();

        $outpostId = 'outpost_1';
        $enhancementId1 = 'enhancement_1';
        $enhancementId2 = 'enhancement_2';
        MstOutpost::factory()->create([
            'id' => $outpostId,
            'start_at' => '2021-01-01 00:00:00',
            'end_at' => '2037-01-01 00:00:00',
        ]);
        MstOutpostEnhancement::factory()->createMany([
            [
                'id' => $enhancementId1,
                'mst_outpost_id' => $outpostId,
                'outpost_enhancement_type' => OutpostEnhancementType::cases()[0]->value,
            ],
            [
                'id' => $enhancementId2,
                'mst_outpost_id' => $outpostId,
                'outpost_enhancement_type' => OutpostEnhancementType::cases()[1]->value,
            ],
        ]);
        MstOutpostEnhancementLevel::factory()->createMany([
            [
                'mst_outpost_enhancement_id' => $enhancementId1,
                'level' => 2,
                'cost_coin' => 200,
            ],
            [
                'mst_outpost_enhancement_id' => $enhancementId1,
                'level' => 3,
                'cost_coin' => 300,
            ],
        ]);

        UsrUserParameter::factory()->create([
            'usr_user_id' => $usrUserId,
            'level' => 1,
            'coin' => 1000,
        ]);

        $this->createDiamond($usrUserId);

        // Exercise
        $response = $this->sendRequest(
            'outpost/enhance', [
                'mstOutpostEnhancementId' => $enhancementId1,
                'level' => 3,
            ]
        );

        // Verify
        $response->assertStatus(HttpStatusCode::SUCCESS);

        // log_outpost_enhancementsとlog_coinsにレコード1つずつ追加される
        $this->checkLoggingNo($usrUserId, 2);

        $logOutpostEnhancement = LogOutpostEnhancement::query()
            ->where('usr_user_id', $usrUserId)
            ->first();
        $this->assertNotNull($logOutpostEnhancement);

        $this->checkLogResourcesByUse(
            usrUserId: $usrUserId,
            nginxRequestId: $nginxRequestId,
            rewardType: RewardType::COIN,
            expectedAmounts: [
                ['before_amount' => 1000, 'after_amount' => 500],
            ],
            expectedTriggers: [
                [
                    'trigger_source' => 'log_outpost_enhancements',
                    'trigger_value' => $logOutpostEnhancement->getId(),
                    'trigger_option' => '',
                ],
            ]
        );
    }

    public function test_shop_tradeShopItem_コインでショップアイテムを交換しコイン消費ログが保存される()
    {
        // Setup
        $now = $this->fixTime('2023-01-01 00:00:00');
        $nginxRequestId = __FUNCTION__;
        $this->setNginxRequestId($nginxRequestId);

        $usrUserId = $this->createUsrUser()->getId();

        $mstShopItem = MstShopItem::factory()->create([
            'id' => fake()->uuid(),
            'shop_type' => ShopType::DAILY->value,
            'cost_type' => ShopItemCostType::COIN->value,
            'cost_amount' => 100,
            'is_first_time_free' => 0,
            'tradable_count' => 1,
            'resource_type' => RewardType::FREE_DIAMOND->value,
            'resource_id' => null,
            'resource_amount' => 100,
            'start_date' => '2000-01-01 00:00:00',
            'end_date' => '2030-01-01 00:00:00'
        ])->toEntity();

        UsrUserParameter::factory()->create([
            'usr_user_id' => $usrUserId,
            'level' => 1,
            'coin' => 1000,
        ]);

        $this->createDiamond($usrUserId);

        // Exercise
        $response = $this->sendRequest(
            'shop/trade_shop_item', [
                'mstShopItemId' => $mstShopItem->getId(),
            ]
        );

        // Verify
        $response->assertStatus(HttpStatusCode::SUCCESS);

        $this->checkLoggingNo($usrUserId, 2);

        $this->checkLogResourcesByUse(
            usrUserId: $usrUserId,
            nginxRequestId: $nginxRequestId,
            rewardType: RewardType::COIN,
            expectedAmounts: [
                ['before_amount' => 1000, 'after_amount' => 900],
            ],
            expectedTriggers: [
                [
                    'trigger_source' => LogResourceTriggerSource::TRADE_SHOP_ITEM_COST->value,
                    'trigger_value' => $mstShopItem->getId(),
                    'trigger_option' => '',
                ],
            ]
        );
    }
}
