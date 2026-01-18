<?php

namespace Tests\Feature\Domain\Reward;

use App\Domain\Emblem\Constants\EmblemConstant;
use App\Domain\Emblem\Models\UsrEmblem;
use App\Domain\Item\Enums\ItemType;
use App\Domain\Item\Models\Eloquent\UsrItem;
use App\Domain\Message\Constants\MessageConstant;
use App\Domain\Message\Enums\MessageSource;
use App\Domain\Message\Models\Eloquent\UsrMessage;
use App\Domain\Resource\Enums\RewardConvertedReason;
use App\Domain\Resource\Enums\RewardType;
use App\Domain\Resource\Enums\UnreceivedRewardReason;
use App\Domain\Resource\Mst\Models\MstEmblem;
use App\Domain\Resource\Mst\Models\MstIdleIncentive;
use App\Domain\Resource\Mst\Models\MstIdleIncentiveReward;
use App\Domain\Resource\Mst\Models\MstItem;
use App\Domain\Resource\Mst\Models\MstQuest;
use App\Domain\Resource\Mst\Models\MstStage;
use App\Domain\Resource\Mst\Models\MstUnit;
use App\Domain\Resource\Mst\Models\MstUnitFragmentConvert;
use App\Domain\Resource\Mst\Models\MstUserLevel;
use App\Domain\Resource\Mst\Models\MstUserLevelBonus;
use App\Domain\Resource\Mst\Models\MstUserLevelBonusGroup;
use App\Domain\Reward\Managers\RewardManager;
use App\Domain\Reward\Services\RewardSendService;
use App\Domain\Stage\Enums\QuestType;
use App\Domain\Stage\Models\Eloquent\UsrStage;
use App\Domain\Unit\Enums\UnitColorType;
use App\Domain\Unit\Models\Eloquent\UsrUnit;
use App\Domain\User\Constants\UserConstant;
use App\Domain\User\Models\UsrUserParameter;
use Carbon\CarbonImmutable;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Config;
use PHPUnit\Framework\Attributes\DataProvider;
use ReflectionClass;
use Tests\TestCase;
use WonderPlanet\Domain\Currency\Services\CurrencyService;

class RewardManagerTest extends TestCase
{
    private RewardManager $rewardManager;
    private RewardSendService $rewardSendService;

    protected function setUp(): void
    {
        parent::setUp();

        $this->rewardManager = $this->app->make(RewardManager::class);
        $this->rewardSendService = $this->app->make(RewardSendService::class);
    }

    private function getPrivateNeedToSendRewards(): Collection
    {
        $reflectionClass = new ReflectionClass($this->rewardManager);
        $property = $reflectionClass->getProperty('needToSendRewards');
        $property->setAccessible(true);
        return collect($property->getValue($this->rewardManager));
    }

    private function getPrivateSentRewards(): Collection
    {
        $reflectionClass = new ReflectionClass($this->rewardManager);
        $property = $reflectionClass->getProperty('sentRewards');
        $property->setAccessible(true);
        return $property->getValue($this->rewardManager);
    }

    /**
     * @dataProvider params_test_addReward_有効な報酬オブジェクトのみを追加できることを確認
     */
    public function test_addReward_有効な報酬オブジェクトのみを追加できることを確認(
        bool $isSingle,
    ) {
        // Setup
        $test1Reward1 = new Test1Reward(RewardType::COIN->value, null, 100, 'test1Id_coin_100');
        $test1Reward2 = new Test1Reward(RewardType::COIN->value, null, 0, 'test1Id_coin_0');
        $test1Reward3 = new Test1Reward(RewardType::COIN->value, null, -1, 'test1Id_coin_-1');
        $test1Reward4 = new Test1Reward('invalidType', 'invalidTypeResourceId', 50, 'test1Id_invalidType_50');

        // Exercise
        if ($isSingle) {
            $this->rewardManager->addReward($test1Reward1);
            $this->rewardManager->addReward($test1Reward2);
            $this->rewardManager->addReward($test1Reward3);
            $this->rewardManager->addReward($test1Reward4);
        } else {
            $this->rewardManager->addRewards(collect([
                $test1Reward1,
                $test1Reward2,
                $test1Reward3,
                $test1Reward4,
            ]));
        }

        // Verify
        $result = $this->getPrivateNeedToSendRewards();
        $this->assertCount(1, $result);
        $this->assertEquals($test1Reward1, $result->first());
    }

    public static function params_test_addReward_有効な報酬オブジェクトのみを追加できることを確認()
    {
        return [
            'addReward 1つずつ追加するメソッドの確認' => ['isSingle' => true],
            'addRewards 複数をまとめて追加するメソッドの確認' => ['isSingle' => false],
        ];
    }

    public function test_sendRewards_全報酬タイプの報酬配布を実行し配布済み報酬情報を取得できることを確認()
    {
        // Setup
        $usrUserId = $this->createUsrUser()->getId();
        $now = CarbonImmutable::now();
        $mstStageId = 'stage1';

        // usr
        $usrUserParameter = UsrUserParameter::factory()->create([
            'usr_user_id' => $usrUserId,
            'level' => 1,
            'coin' => 1,
            'stamina' => 3,
            'exp' => 4,
        ]);
        $this->createDiamond(
            usrUserId: $usrUserId,
            freeDiamond: 2,
        );
        UsrItem::factory()->createMany([
            ['usr_user_id' => $usrUserId, 'mst_item_id' => 'item2', 'amount' => 2],
            ['usr_user_id' => $usrUserId, 'mst_item_id' => 'item4', 'amount' => 4],
        ]);
        UsrStage::factory()->create([
            'usr_user_id' => $usrUserId,
            'mst_stage_id' => $mstStageId,
            'clear_count' => 1,
            
        ]);

        // mst
        MstUserLevel::factory()->createMany([
            ['level' => 1, 'exp' => 0],
            ['level' => 2, 'exp' => 9999999999999], // レベルアップしないように設定
        ]);
        MstItem::factory()->createMany([
            ['id' => 'item1'],
            ['id' => 'item2'],
            ['id' => 'item3'],
            ['id' => 'item4'],
            // [
            //     'id' => 'idle_coin_box',
            //     'type' => ItemType::IDLE_COIN_BOX->value,
            //     'effect_value' => 2,
            // ],
            // [
            //     'id' => 'idle_rank_up_material_box',
            //     'type' => ItemType::IDLE_RANK_UP_MATERIAL_BOX->value,
            //     'effect_value' => 3,
            // ],
            [
                'id' => 'rank_up_material',
                'type' => ItemType::RANK_UP_MATERIAL->value,
                'effect_value' => UnitColorType::COLORLESS->value,
            ],
        ]);
        $mstQuest = MstQuest::factory()->create([
            'quest_type' => QuestType::NORMAL->value,
        ])->toEntity();
        MstStage::factory()->create([
            'id' => $mstStageId,
            'mst_quest_id' => $mstQuest->getId(),
            'sort_order' => 1,
        ]);
        MstIdleIncentive::factory()->create([
            'reward_increase_interval_minutes' => 10
        ]);
        MstIdleIncentiveReward::factory()->create([
            'mst_stage_id' => $mstStageId,
            'base_coin_amount' => 10,
        ]);
        MstEmblem::factory()->create([
            'id' => 'emblem1',
        ]);
        MstUnit::factory()->create([
            'id' => 'unit1'
        ]);

        // 全報酬タイプごとに、複数の報酬オブジェクトから同時に獲得した場合を想定する
        // // 期待するコインの報酬量：240 = 2 * 60 * 2 * 10 / 10
        // $idleCoinBoxReward = new Test1Reward(RewardType::ITEM->value, 'idle_coin_box', 2, '');
        // // 期待するアイテムの報酬量：1080 = 3 * 60 * 3 * 20 / 10
        // $idleRankUpMaterialBoxReward = new Test1Reward(RewardType::ITEM->value, 'idle_rank_up_material_box', 3, '');
        $needToSendRewards = collect([
            new Test1Reward(RewardType::COIN->value, null, 100, ''),
            new Test2Reward(RewardType::COIN->value, null, 200, ''),
            new Test2Reward(RewardType::COIN->value, null, 300, ''),

            new Test1Reward(RewardType::STAMINA->value, null, 300, ''),
            new Test2Reward(RewardType::STAMINA->value, null, 400, ''),

            new Test1Reward(RewardType::FREE_DIAMOND->value, null, 500, ''),
            new Test2Reward(RewardType::FREE_DIAMOND->value, null, 600, ''),

            new Test1Reward(RewardType::EXP->value, null, 1100, ''),
            new Test2Reward(RewardType::EXP->value, null, 1200, ''),

            new Test1Reward(RewardType::ITEM->value, 'item1', 900, ''),
            new Test1Reward(RewardType::ITEM->value, 'item1', 100, ''),
            new Test2Reward(RewardType::ITEM->value, 'item2', 500, ''),
            new Test2Reward(RewardType::ITEM->value, 'item2', 500, ''),
            new Test1Reward(RewardType::ITEM->value, 'item2', 100, ''),
            new Test2Reward(RewardType::ITEM->value, 'item1', 200, ''),
            new Test1Reward(RewardType::ITEM->value, 'item3', 1500, ''),
            new Test2Reward(RewardType::ITEM->value, 'item4', 1600, ''),

            new Test1Reward(RewardType::EMBLEM->value, 'emblem1', 1, ''),

            new Test1Reward(RewardType::UNIT->value, 'unit1', 1, ''),

            // // idleBoxアイテム
            // $idleCoinBoxReward,
            // $idleRankUpMaterialBoxReward,

        ]);
        $this->rewardManager->addRewards($needToSendRewards);

        // Exercise
        $this->rewardSendService->sendRewards($usrUserId, UserConstant::PLATFORM_IOS, $now);
        $this->saveAll();

        // Verify

        // 配布実行の確認
        $usrUserParameter->refresh();
        // $this->assertEquals(1 + 100 + 200 + 300 + 240, $usrUserParameter->getCoin());
        $this->assertEquals(1 + 100 + 200 + 300, $usrUserParameter->getCoin());
        $this->assertEquals(3 + 300 + 400, $usrUserParameter->getStamina());
        $this->assertEquals(4 + 1100 + 1200, $usrUserParameter->getExp());

        $diamond = $this->getDiamond($usrUserId);
        $this->assertEquals(2 + 500 + 600, $diamond->getFreeAmount());

        $usrItems = UsrItem::where('usr_user_id', $usrUserId)->get()->keyBy(function ($usrItem) {
            return $usrItem->getMstItemId();
        });
        // $this->assertCount(5, $usrItems);
        $this->assertCount(4, $usrItems);
        // 未所持 かつ 複数の報酬オブジェクトから同時に付与されたアイテム
        $this->assertEquals(900 + 100 + 200, $usrItems->get('item1')->getAmount());
        // 所持済 かつ 複数の報酬オブジェクトから同時に付与されたアイテム
        $this->assertEquals(2 + 500 + 500 + 100, $usrItems->get('item2')->getAmount());
        // 未所持 かつ 単一の報酬オブジェクトから付与されたアイテム
        $this->assertEquals(1500, $usrItems->get('item3')->getAmount());
        // 所持済 かつ 単一の報酬オブジェクトから付与されたアイテム
        $this->assertEquals(4 + 1600, $usrItems->get('item4')->getAmount());
        // // idleBoxアイテム
        // $this->assertArrayNotHasKey('idle_coin_box', $usrItems);
        // $this->assertEquals(1080, $usrItems->get('rank_up_material')->getAmount());

        $usrEmblem = UsrEmblem::query()->where('usr_user_id', $usrUserId)->first();
        $this->assertNotNull($usrEmblem);
        $this->assertEquals('emblem1', $usrEmblem->getMstEmblemId());

        // キャラ付与ができてることを確認
        $usrUnit = UsrUnit::query()->where('usr_user_id', $usrUserId)->first();
        $this->assertNotNull($usrUnit);
        $this->assertEquals('unit1', $usrUnit->getMstUnitId());

        // レスポンスの確認
        $expectedRewards = $needToSendRewards->groupBy(fn($reward) => get_class($reward));
        // $idleBoxRewardIds = collect([$idleCoinBoxReward->getId(), $idleRankUpMaterialBoxReward->getId()]);

        // idleBoxReward以外を確認
        // Test1Reward
        // $actuals = $this->rewardManager->getSentRewards(Test1Reward::class)
        //     ->filter(fn($reward) => !$idleBoxRewardIds->contains($reward->getId()));
        // $this->assertCount(12 - count($idleBoxRewardIds), $actuals);
        $actuals = $this->rewardManager->getSentRewards(Test1Reward::class);
        $this->assertCount(10, $actuals);
        // Test2Reward
        $actuals = $this->rewardManager->getSentRewards(Test2Reward::class);
        $this->assertCount(9, $actuals);
        $this->assertEquals($expectedRewards->get(Test2Reward::class), $actuals);

        // // idleBoxRewardを確認
        // $actuals = $this->rewardManager->getSentRewards(Test1Reward::class)
        //     ->filter(fn($reward) => $idleBoxRewardIds->contains($reward->getId()))
        //     ->keyBy(fn($reward) => $reward->getId());
        // // idle_coin_box
        // $actual = $actuals->get($idleCoinBoxReward->getId());
        // $this->assertEquals(RewardType::COIN->value, $actual->getType());
        // $this->assertNull($actual->getResourceId());
        // $this->assertEquals(240, $actual->getAmount());
        // // idle_rank_up_material_box
        // $actual = $actuals->get($idleRankUpMaterialBoxReward->getId());
        // $this->assertEquals(RewardType::ITEM->value, $actual->getType());
        // $this->assertEquals('rank_up_material', $actual->getResourceId());
        // $this->assertEquals(1080, $actual->getAmount());

        // 配布後のステータスを確認
        // 配布済みリストの全ての報酬オブジェクトが配布済みステータスになっていることを確認
        $isSents = $actuals->merge($actuals)
            ->map(fn($reward) => $reward->isSent())->unique();
        $this->assertCount(1, $isSents);
        $this->assertTrue($isSents->first());
        // 配布前リストには報酬オブジェクトが残っていないことを確認
        $this->assertCount(0, $this->getPrivateNeedToSendRewards());
    }

    public function test_sendRewards_エンブレムが重複付与された場合にコインに変換されることを確認()
    {
        // Setup
        $usrUserId = $this->createUsrUser()->getId();
        $now = CarbonImmutable::now();
        $mstStageId = 'stage1';

        // usr
        $usrUserParameter = UsrUserParameter::factory()->create([
            'usr_user_id' => $usrUserId,
            'level' => 1,
            'coin' => 1,
            'stamina' => 3,
            'exp' => 4,
        ]);
        $this->createDiamond(
            usrUserId: $usrUserId,
            freeDiamond: 2,
        );

        // mst
        MstUserLevel::factory()->createMany([
            ['level' => 1, 'exp' => 0],
            ['level' => 2, 'exp' => 9999999999999], // レベルアップしないように設定
        ]);
        MstEmblem::factory()->create([
            'id' => 'emblem1',
        ]);

        // 全報酬タイプごとに、複数の報酬オブジェクトから同時に獲得した場合を想定する
        $needToSendRewards = collect([
            new Test1Reward(RewardType::COIN->value, null, 100, ''),
            new Test1Reward(RewardType::EMBLEM->value, 'emblem1', 1, ''),
            new Test2Reward(RewardType::COIN->value, null, 200, ''),
            new Test2Reward(RewardType::EMBLEM->value, 'emblem1', 1, ''),
            new Test2Reward(RewardType::COIN->value, null, 300, ''),
        ]);
        $this->rewardManager->addRewards($needToSendRewards);

        // Exercise
        $this->rewardSendService->sendRewards($usrUserId, UserConstant::PLATFORM_IOS, $now);
        $this->saveAll();

        // Verify

        // 配布実行の確認
        $usrUserParameter->refresh();
        $this->assertEquals(1 + 100 + 200 + 300 + EmblemConstant::DUPLICATE_EMBLEM_CONVERT_COIN, $usrUserParameter->getCoin());

        $usrEmblem = UsrEmblem::query()->where('usr_user_id', $usrUserId)->first();
        $this->assertNotNull($usrEmblem);
        $this->assertEquals('emblem1', $usrEmblem->getMstEmblemId());

        // レスポンスの確認
        $expectedRewards = $needToSendRewards->groupBy(fn($reward) => get_class($reward));

        // idleBoxReward以外を確認
        // Test1Reward
        $test1Actuals = $this->rewardManager->getSentRewards(Test1Reward::class);
        $this->assertCount(2, $test1Actuals);
        // Test2Reward
        $test2Actuals = $this->rewardManager->getSentRewards(Test2Reward::class);
        $this->assertCount(3, $test2Actuals);
        $duplicatedReward = $test2Actuals->filter(function ($reward) {
            return $reward->getRewardConvertedReason() === RewardConvertedReason::DUPLICATED_EMBLEM;
        });
        $this->assertCount(1, $duplicatedReward);
        $this->assertEquals(EmblemConstant::DUPLICATE_EMBLEM_CONVERT_COIN, $duplicatedReward->first()->getAmount());
        $this->assertEquals(RewardType::COIN->value, $duplicatedReward->first()->getType());

        // 配布前リストには報酬オブジェクトが残っていないことを確認
        $this->assertCount(0, $this->getPrivateNeedToSendRewards());
    }

    public function test_sendRewards_所持済みのエンブレムが付与された場合にコインに変換されることを確認()
    {
        // Setup
        $usrUserId = $this->createUsrUser()->getId();
        $now = CarbonImmutable::now();
        $mstStageId = 'stage1';

        // usr
        $usrUserParameter = UsrUserParameter::factory()->create([
            'usr_user_id' => $usrUserId,
            'level' => 1,
            'coin' => 1,
            'stamina' => 3,
            'exp' => 4,
        ]);
        $this->createDiamond(
            usrUserId: $usrUserId,
            freeDiamond: 2,
        );

        // mst
        MstUserLevel::factory()->createMany([
            ['level' => 1, 'exp' => 0],
            ['level' => 2, 'exp' => 9999999999999], // レベルアップしないように設定
        ]);
        MstEmblem::factory()->createMany([
            [
                'id' => 'emblem1',
            ],
            [
                'id' => 'emblem2',
            ],
            [
                'id' => 'emblem3',
            ],
        ]);
        UsrEmblem::factory()->create([
            'usr_user_id' => $usrUserId,
            'mst_emblem_id' => 'emblem1',
        ]);

        // 全報酬タイプごとに、複数の報酬オブジェクトから同時に獲得した場合を想定する
        $needToSendRewards = collect([
            new Test1Reward(RewardType::COIN->value, null, 100, ''),
            new Test1Reward(RewardType::EMBLEM->value, 'emblem1', 1, ''),
            new Test1Reward(RewardType::EMBLEM->value, 'emblem2', 1, ''),
            new Test2Reward(RewardType::COIN->value, null, 200, ''),
            new Test2Reward(RewardType::EMBLEM->value, 'emblem2', 1, ''),
            new Test2Reward(RewardType::EMBLEM->value, 'emblem3', 1, ''),
            new Test2Reward(RewardType::COIN->value, null, 300, ''),
        ]);
        $this->rewardManager->addRewards($needToSendRewards);

        // Exercise
        $this->rewardSendService->sendRewards($usrUserId, UserConstant::PLATFORM_IOS, $now);
        $this->saveAll();

        // Verify

        // 配布実行の確認
        $usrUserParameter->refresh();
        $this->assertEquals(1 + 100 + 200 + 300 + (EmblemConstant::DUPLICATE_EMBLEM_CONVERT_COIN * 2), $usrUserParameter->getCoin());

        $usrEmblem = UsrEmblem::query()->where('usr_user_id', $usrUserId)->where('mst_emblem_id', 'emblem1')->first();
        $this->assertNotNull($usrEmblem);
        $usrEmblem = UsrEmblem::query()->where('usr_user_id', $usrUserId)->where('mst_emblem_id', 'emblem2')->first();
        $this->assertNotNull($usrEmblem);
        $usrEmblem = UsrEmblem::query()->where('usr_user_id', $usrUserId)->where('mst_emblem_id', 'emblem3')->first();
        $this->assertNotNull($usrEmblem);
        $usrEmblems = UsrEmblem::query()->where('usr_user_id', $usrUserId)->get();
        $this->assertCount(3, $usrEmblems);

        // レスポンスの確認
        $expectedRewards = $needToSendRewards->groupBy(fn($reward) => get_class($reward));

        // idleBoxReward以外を確認
        // Test1Reward
        $test1Actuals = $this->rewardManager->getSentRewards(Test1Reward::class);
        $this->assertCount(3, $test1Actuals);
        $duplicatedReward = $test1Actuals->filter(function ($reward) {
            return $reward->getRewardConvertedReason() === RewardConvertedReason::DUPLICATED_EMBLEM;
        });
        $this->assertCount(1, $duplicatedReward);
        $this->assertEquals(EmblemConstant::DUPLICATE_EMBLEM_CONVERT_COIN, $duplicatedReward->first()->getAmount());
        $this->assertEquals(RewardType::COIN->value, $duplicatedReward->first()->getType());
        // Test2Reward
        $test2Actuals = $this->rewardManager->getSentRewards(Test2Reward::class);
        $this->assertCount(4, $test2Actuals);
        $duplicatedReward = $test2Actuals->filter(function ($reward) {
            return $reward->getRewardConvertedReason() === RewardConvertedReason::DUPLICATED_EMBLEM;
        });
        $this->assertCount(1, $duplicatedReward);
        $this->assertEquals(EmblemConstant::DUPLICATE_EMBLEM_CONVERT_COIN, $duplicatedReward->first()->getAmount());
        $this->assertEquals(RewardType::COIN->value, $duplicatedReward->first()->getType());

        // 配布前リストには報酬オブジェクトが残っていないことを確認
        $this->assertCount(0, $this->getPrivateNeedToSendRewards());
    }

    public function test_sendRewards_キャラが重複付与された場合にアイテムに変換されることを確認()
    {
        // 設定
        $usrUserId = $this->createUsrUser()->getId();
        $now = CarbonImmutable::now();

        // 前提マスター作成
        MstItem::factory()->create([
            'id' => 'unit1_fragment_mst_item_id',
            'type' => ItemType::CHARACTER_FRAGMENT->value,
        ]);
        MstUnit::factory()->create([
            'id' => 'unit1',
            'fragment_mst_item_id' => 'unit1_fragment_mst_item_id',
            'unit_label' => 'DropR'
        ]);
        MstUnitFragmentConvert::factory()->create([
            'unit_label' => 'DropR',
            'convert_amount' => 123
        ]);

        // 対象メソッド実行
        $needToSendRewards = collect([
            new Test1Reward(RewardType::UNIT->value, 'unit1', 1, ''),
            new Test2Reward(RewardType::UNIT->value, 'unit1', 1, ''),
        ]);
        $this->rewardManager->addRewards($needToSendRewards);
        $this->rewardSendService->sendRewards($usrUserId, UserConstant::PLATFORM_IOS, $now);
        $this->saveAll();

        // キャラ付与ができてることを確認
        $usrUnit = UsrUnit::query()->where('usr_user_id', $usrUserId)->first();
        $this->assertNotNull($usrUnit);
        $this->assertEquals('unit1', $usrUnit->getMstUnitId());

        // キャラ重複時のかけらアイテム変換ができていることを確認
        $usrItems = UsrItem::where('usr_user_id', $usrUserId)->get()->keyBy(function ($usrItem) {
            return $usrItem->getMstItemId();
        });
        $this->assertCount(1, $usrItems);
        $this->assertEquals(123, $usrItems->get('unit1_fragment_mst_item_id')->getAmount());

        // Test1Rewardの状態確認
        $test1Actuals = $this->rewardManager->getSentRewards(Test1Reward::class);
        $this->assertCount(1, $test1Actuals);

        // Test2Rewardの状態確認
        $test2Actuals = $this->rewardManager->getSentRewards(Test2Reward::class);
        $this->assertCount(1, $test2Actuals);
        $duplicatedReward = $test2Actuals->filter(function ($reward) {
            return $reward->getRewardConvertedReason() === RewardConvertedReason::DUPLICATED_UNIT;
        });
        $this->assertCount(1, $duplicatedReward);
        $this->assertEquals(RewardType::ITEM->value, $duplicatedReward->first()->getType());
        $this->assertEquals('unit1_fragment_mst_item_id', $duplicatedReward->first()->getResourceId());
        $this->assertEquals(123, $duplicatedReward->first()->getAmount());

        // 配布前リストに報酬オブジェクトが残っていないことを確認
        $this->assertCount(0, $this->getPrivateNeedToSendRewards());
    }

    public function test_sendRewards_所持済みのキャラが付与された場合にアイテムに変換されることを確認()
    {
        // 設定
        $usrUserId = $this->createUsrUser()->getId();
        $now = CarbonImmutable::now();

        // 前提マスター作成
        MstItem::factory()->create([
            'id' => 'unit1_fragment_mst_item_id',
            'type' => ItemType::CHARACTER_FRAGMENT->value,
        ]);
        MstUnit::factory()->create([
            'id' => 'unit1',
            'fragment_mst_item_id' => 'unit1_fragment_mst_item_id',
            'unit_label' => 'DropR'
        ]);
        MstUnitFragmentConvert::factory()->create([
            'unit_label' => 'DropR',
            'convert_amount' => 123
        ]);

        // 前提トランザクションデータ作成
        UsrUnit::factory()->create([
            'usr_user_id' => $usrUserId,
            'mst_unit_id' => 'unit1'
        ]);

        // 対象メソッド実行
        $needToSendRewards = collect([
            new Test1Reward(RewardType::UNIT->value, 'unit1', 1, ''),
        ]);
        $this->rewardManager->addRewards($needToSendRewards);
        $this->rewardSendService->sendRewards($usrUserId, UserConstant::PLATFORM_IOS, $now);
        $this->saveAll();

        // キャラが1件のみで意図通りのキャラであることを確認
        $usrUnits = UsrUnit::query()->where('usr_user_id', $usrUserId)->get();
        $this->assertCount(1, $usrUnits);
        $this->assertEquals('unit1', $usrUnits->first()->getMstUnitId());

        // キャラ重複時のかけらアイテム変換ができていることを確認
        $usrItems = UsrItem::where('usr_user_id', $usrUserId)->get()->keyBy(function ($usrItem) {
            return $usrItem->getMstItemId();
        });
        $this->assertCount(1, $usrItems);
        $this->assertEquals(123, $usrItems->get('unit1_fragment_mst_item_id')->getAmount());

        // Test1Rewardの状態確認
        $test1Actuals = $this->rewardManager->getSentRewards(Test1Reward::class);
        $this->assertCount(1, $test1Actuals);
        $duplicatedReward = $test1Actuals->filter(function ($reward) {
            return $reward->getRewardConvertedReason() === RewardConvertedReason::DUPLICATED_UNIT;
        });
        $this->assertCount(1, $duplicatedReward);
        $this->assertEquals(RewardType::ITEM->value, $duplicatedReward->first()->getType());
        $this->assertEquals('unit1_fragment_mst_item_id', $duplicatedReward->first()->getResourceId());
        $this->assertEquals(123, $duplicatedReward->first()->getAmount());

        // 配布前リストに報酬オブジェクトが残っていないことを確認
        $this->assertCount(0, $this->getPrivateNeedToSendRewards());
    }

    public function test_sendRewards_報酬配布して追加報酬付与が発生した場合でも全て配布できていることを確認する()
    {
        // Setup
        $usrUserId = $this->createUsrUser()->getId();
        $now = CarbonImmutable::now();

        // usr
        $usrUserParameter = UsrUserParameter::factory()->create([
            'usr_user_id' => $usrUserId,
            'level' => 1,
            'exp' => 2,
            'coin' => 3,
        ]);

        // mst
        // 追加報酬としてレベルアップ報酬を設定
        MstUserLevel::factory()->createMany([
            ['level' => 1, 'exp' => 0],
            ['level' => 2, 'exp' => 100],
            ['level' => 3, 'exp' => 200],
        ]);
        MstUserLevelBonus::factory()->create([
            'level' => 2,
            'mst_user_level_bonus_group_id' => 'group1',
        ]);
        MstUserLevelBonusGroup::factory()->create([
            'mst_user_level_bonus_group_id' => 'group1',
            'resource_type' => RewardType::COIN->value,
            'resource_id' => null,
            'resource_amount' => 300,
        ]);

        $test1Reward = new Test1Reward(RewardType::EXP->value, null, 100, '');
        $this->rewardManager->addReward($test1Reward);

        // Exercise
        $this->rewardSendService->sendRewards($usrUserId, UserConstant::PLATFORM_IOS, $now);
        $this->saveAll();

        // Verify

        // 配布実行の確認
        $usrUserParameter->refresh();
        $this->assertEquals(2, $usrUserParameter->getLevel());
        $this->assertEquals(2 + 100, $usrUserParameter->getExp());
        $this->assertEquals(3 + 300, $usrUserParameter->getCoin());
    }

    public static function params_test_sendRewards_無償プリズムが上限超過で即時受取できずメールボックスへ送信される()
    {
        return [
            'プリズム上限チェック有償無償 合算' => [
                'separateCurrencyLimitCheck' => false,
            ],
            'プリズム上限チェック有償無償 別々' => [
                'separateCurrencyLimitCheck' => true,
            ],
        ];
    }

    #[DataProvider('params_test_sendRewards_無償プリズムが上限超過で即時受取できずメールボックスへ送信される')]
    public function test_sendRewards_無償プリズムが上限超過で即時受取できずメールボックスへ送信される(
        bool $separateCurrencyLimitCheck,
    ) {
        // Setup
        $usrUserId = $this->createUsrUser()->getId();
        $now = $this->fixTime();

        Config::set('wp_currency.store.separate_currency_limit_check', $separateCurrencyLimitCheck);

        // usr
        $usrUserParameter = UsrUserParameter::factory()->create([
            'usr_user_id' => $usrUserId,
            'coin' => 1,
        ]);

        // 無償プリズムを上限まであと1の数量を所持している状態にする
        $currencyService = $this->app->make(CurrencyService::class);
        $maxFreeDiamondAmount = $currencyService->getMaxOwnedCurrencyAmount();
        $beforeAmount = $maxFreeDiamondAmount - 1;
        $this->createDiamond(
            usrUserId: $usrUserId,
            freeDiamond: $beforeAmount,
        );

        // 無償プリズムの上限を超える報酬を配布リストに追加
        $test1Reward = new Test1Reward(RewardType::FREE_DIAMOND->value, null, 100, 'test1Reward_1');
        $this->rewardManager->addReward($test1Reward);

        // 即時配布可能なリソースなら配布されることも確認する
        $test2Reward = new Test2Reward(RewardType::COIN->value, null, 200, 'test2Reward_1');
        $this->rewardManager->addReward($test2Reward);

        // Exercise
        $this->rewardSendService->sendRewards($usrUserId, UserConstant::PLATFORM_IOS, $now);
        $this->saveAll();

        // Verify

        // 無償プリズム
        // 未配布であることを確認
        $diamond = $this->getDiamond($usrUserId);
        $this->assertEquals($beforeAmount, $diamond->getFreeAmount());
        // メールボックスに送信されていることを確認
        // DB
        $actual = UsrMessage::query()->where('usr_user_id', $usrUserId)->get();
        $this->assertCount(1, $actual);
        $actual = $actual->first();
        $this->assertEquals(null, $actual->mng_message_id);
        $this->assertStringStartsWith(MessageSource::RESOURCE_LIMIT_REACHED->value, $actual->message_source);
        $this->assertEquals(null, $actual->reward_group_id);
        $this->assertEquals(RewardType::FREE_DIAMOND->value, $actual->resource_type);
        $this->assertEquals(null, $actual->resource_id);
        $this->assertEquals(100, $actual->resource_amount);
        $this->assertEquals(0, $actual->is_received);
        $this->assertEquals(MessageConstant::REWARD_UNRECEIVED_TITLE, $actual->title);
        $this->assertEquals(MessageConstant::REWARD_UNRECEIVED_BODY, $actual->body);
        $this->assertEquals(null, $actual->opened_at);
        $this->assertEquals(null, $actual->received_at);
        $this->assertEquals(null, $actual->expired_at); // 無期限
        // レスポンス
        $sentRewards = $this->rewardManager->getSentRewards(Test1Reward::class);
        $this->assertCount(1, $sentRewards);
        $actual = $sentRewards->first();
        $this->assertEquals(RewardType::FREE_DIAMOND->value, $actual->getType());
        $this->assertEquals(null, $actual->getResourceId());
        $this->assertEquals(100, $actual->getAmount());
        $this->assertEquals(UnreceivedRewardReason::SENT_TO_MESSAGE, $actual->getUnreceivedRewardReason());
        $this->assertTrue($actual->isSent());

        // コイン
        // 配布済みであることを確認
        $usrUserParameter->refresh();
        $this->assertEquals(1 + 200, $usrUserParameter->getCoin());
        // レスポンス
        $sentRewards = $this->rewardManager->getSentRewards(Test2Reward::class);
        $this->assertCount(1, $sentRewards);
        $actual = $sentRewards->first();
        $this->assertEquals(RewardType::COIN->value, $actual->getType());
        $this->assertEquals(null, $actual->getResourceId());
        $this->assertEquals(200, $actual->getAmount());
        $this->assertTrue($actual->isSent());
        $this->assertEquals(UnreceivedRewardReason::NONE, $actual->getUnreceivedRewardReason());
    }
}
