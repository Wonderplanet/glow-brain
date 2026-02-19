<?php

namespace Feature\Domain\Item\Services;

use App\Domain\Item\Enums\ItemType;
use App\Domain\Item\Services\ItemIdleBoxService;
use App\Domain\Resource\Dtos\LogTriggerDto;
use App\Domain\Resource\Entities\Rewards\BaseReward;
use App\Domain\Resource\Enums\RewardConvertedReason;
use App\Domain\Resource\Enums\RewardType;
use App\Domain\Resource\Mst\Models\MstIdleIncentive;
use App\Domain\Resource\Mst\Models\MstIdleIncentiveReward;
use App\Domain\Resource\Mst\Models\MstItem;
use App\Domain\Resource\Mst\Models\MstQuest;
use App\Domain\Resource\Mst\Models\MstStage;
use App\Domain\Stage\Enums\QuestType;
use App\Domain\Stage\Models\Eloquent\UsrStage;
use App\Domain\Unit\Enums\UnitColorType;
use Carbon\CarbonImmutable;
use Tests\TestCase;

class ItemIdleBoxServiceTest extends TestCase
{
    private ItemIdleBoxService $itemIdleBoxService;

    public function setUp(): void
    {
        parent::setUp();
        $this->itemIdleBoxService = $this->app->make(ItemIdleBoxService::class);
    }

    public static function params_test_convertIdleBoxToRealResources_放置ボックスが実体のリソース情報に変換される()
    {
        return [
            'コイン' => [
                'resourceType' => RewardType::COIN->value,
                'resourceId' => null,
                'resourceAmount' => 10,
                'expectedType' => RewardType::COIN->value,
                'expectedId' => null,
                'expectedAmount' => 10,
                'rewardConvertedReason' => null,
            ],
            '無償ダイヤモンド' => [
                'resourceType' => RewardType::FREE_DIAMOND->value,
                'resourceId' => null,
                'resourceAmount' => 10,
                'expectedType' => RewardType::FREE_DIAMOND->value,
                'expectedId' => null,
                'expectedAmount' => 10,
                'rewardConvertedReason' => null,
            ],
            'スタミナ' => [
                'resourceType' => RewardType::STAMINA->value,
                'resourceId' => null,
                'resourceAmount' => 10,
                'expectedType' => RewardType::STAMINA->value,
                'expectedId' => null,
                'expectedAmount' => 10,
                'rewardConvertedReason' => null,
            ],
            '放置ボックスではないアイテム' => [
                'resourceType' => RewardType::ITEM->value,
                'resourceId' => 'normal',
                'resourceAmount' => 10,
                'expectedType' => RewardType::ITEM->value,
                'expectedId' => 'normal',
                'expectedAmount' => 10,
                'rewardConvertedReason' => null,
            ],
            'コイン放置ボックス' => [
                'resourceType' => RewardType::ITEM->value,
                'resourceId' => 'idle_coin',
                'resourceAmount' => 2,
                'expectedType' => RewardType::COIN->value,
                'expectedId' => null,
                'expectedAmount' => 240, // 2 * 60 * 2 * 10 / 10
                'rewardConvertedReason' => RewardConvertedReason::CONVERT_IDLE_BOX->value,
            ],
            'リミテッドメモリー放置ボックス' => [
                'resourceType' => RewardType::ITEM->value,
                'resourceId' => 'idle_rank_up_material',
                'resourceAmount' => 2,
                'expectedType' => RewardType::ITEM->value,
                'expectedId' => 'rank_up_material',
                'expectedAmount' => 360, // 3 * 60 * 2 * 10 / 10
                'rewardConvertedReason' => RewardConvertedReason::CONVERT_IDLE_BOX->value,
            ],
            '経験値' => [
                'resourceType' => RewardType::EXP->value,
                'resourceId' => null,
                'resourceAmount' => 10,
                'expectedType' => RewardType::EXP->value,
                'expectedId' => null,
                'expectedAmount' => 10,
                'rewardConvertedReason' => null,
            ],
        ];
    }

    /**
     * 探索連動アイテムの仕様が一旦廃止になったが、復活の可能性があるので、コードとしては残しておく。
     * @dataProvider params_test_convertIdleBoxToRealResources_放置ボックスが実体のリソース情報に変換される
     */
    public function test_convertIdleBoxToRealResources_放置ボックスが実体のリソース情報に変換される(
        string $resourceType,
        ?string $resourceId,
        int $resourceAmount,
        string $expectedType,
        ?string $expectedId,
        int $expectedAmount,
        ?string $rewardConvertedReason,
    ): void {
        $this->assertTrue(true);

        // $usrUser = $this->createUsrUser();
        // $now = CarbonImmutable::now();

        // MstItem::factory()->createMany([
        //     [
        //         'id' => 'normal',
        //         'type' => ItemType::ETC->value,
        //         'start_date' => '2023-01-01 00:00:00',
        //         'end_date' => '2037-01-01 00:00:00',
        //     ],
        //     [
        //         'id' => 'rank_up_material',
        //         'type' => ItemType::RANK_UP_MATERIAL->value,
        //         'effect_value' => UnitColorType::COLORLESS->value,
        //         'start_date' => '2023-01-01 00:00:00',
        //         'end_date' => '2037-01-01 00:00:00',
        //     ],
        //     [
        //         'id' => 'idle_coin',
        //         'type' => ItemType::IDLE_COIN_BOX->value,
        //         'effect_value' => 2,
        //         'start_date' => '2023-01-01 00:00:00',
        //         'end_date' => '2037-01-01 00:00:00',
        //     ],
        //     [
        //         'id' => 'idle_rank_up_material',
        //         'type' => ItemType::IDLE_RANK_UP_MATERIAL_BOX->value,
        //         'effect_value' => 3,
        //         'start_date' => '2023-01-01 00:00:00',
        //         'end_date' => '2037-01-01 00:00:00',
        //     ],
        // ]);
        // $mstQuest = MstQuest::factory()->create([
        //     'id' => 'normalQuest1',
        //     'quest_type' => QuestType::NORMAL->value,
        // ]);
        // $mstStage = MstStage::factory()->create([
        //     'mst_quest_id' => 'normalQuest1',
        //     'sort_order' => 1,
        // ])->toEntity();
        // MstIdleIncentive::factory()->create([
        //     'reward_increase_interval_minutes' => 10
        // ]);
        // MstIdleIncentiveReward::factory()->create([
        //     'mst_stage_id' => $mstStage->getId(),
        //     'base_coin_amount' => '10.0001',
        // ])->toEntity();
        // UsrStage::factory()->create([
        //     'usr_user_id' => $usrUser->getId(),
        //     'mst_stage_id' => $mstStage->getId(),
        //     
        // ]);

        // $reward = new BaseReward($resourceType, $resourceId, $resourceAmount, new LogTriggerDto('test', 'test'));

        // $rewards = $this->itemIdleBoxService->convertIdleBoxToRealResources(
        //     $usrUser->getId(),
        //     collect([$reward->getId() => $reward]),
        //     $now,
        // );

        // $actual = $rewards->first();
        // $this->assertEquals($expectedType, $actual->getType());
        // $this->assertEquals($expectedId, $actual->getResourceId());
        // $this->assertEquals($expectedAmount, $actual->getAmount());
        // if (is_null($rewardConvertedReason)) {
        //     $this->assertNull($actual->getRewardConvertedReason());
        // } else {
        //     $this->assertEquals($rewardConvertedReason, $actual->getRewardConvertedReason()->value);
        // }
    }
}
