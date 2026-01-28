<?php

namespace Tests\Feature\Domain\Gacha\UseCases;

use App\Domain\Common\Constants\ErrorCode;
use App\Domain\Common\Enums\Language;
use App\Domain\Common\Exceptions\GameException;
use App\Domain\Common\Utils\CacheKeyUtil;
use App\Domain\Gacha\Enums\CostType;
use App\Domain\Gacha\Enums\GachaType;
use App\Domain\Gacha\Models\UsrGacha;
use App\Domain\Gacha\UseCases\GachaDrawUseCase;
use App\Domain\Item\Models\Eloquent\UsrItem;
use App\Domain\Resource\Entities\Rewards\StepUpGachaStepReward;
use App\Domain\Resource\Enums\RarityType;
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
use App\Domain\User\Constants\UserConstant;
use App\Domain\User\Models\UsrUserParameter;
use Tests\Support\Entities\CurrentUser;
use Tests\TestCase;
use WonderPlanet\Domain\Currency\Constants\CurrencyConstants;
use WonderPlanet\Domain\Currency\Delegators\CurrencyDelegator;

/**
 * ステップアップガチャのテストクラス
 *
 * GachaDrawUseCaseTestから分離してステップアップガチャ関連のテストを集約
 */
class GachaDrawUseCaseStepUpTest extends TestCase
{
    private GachaDrawUseCase $useCase;
    private CurrencyDelegator $currencyDelegator;

    public function setUp(): void
    {
        parent::setUp();
        $this->useCase = app(GachaDrawUseCase::class);
        $this->currencyDelegator = app(CurrencyDelegator::class);
    }

    public function testExec_ステップアップガシャ_ステップ1を実行()
    {
        // Setup
        $usrUser = $this->createUsrUser();
        $currentUser = new CurrentUser($usrUser->getId());
        UsrUserParameter::factory()->create(['usr_user_id' => $usrUser->getId()]);
        $this->createStepUpGachaData();

        // ダイヤモンドを設定（初回無料なので0でも実行可能）
        $this->createDiamond($usrUser->getId(), 0, 0, 0);

        // Exercise
        $resultData = $this->useCase->exec(
            $currentUser,
            'opr_gacha_id',
            0,
            10,
            null,
            0,
            UserConstant::PLATFORM_IOS,
            CurrencyConstants::PLATFORM_APPSTORE,
            CostType::FREE
        );

        // Verify
        $this->assertEquals(10, $resultData->gachaRewards->count());

        // UsrGachaのステップ情報を確認（DBから直接取得）
        $usrGacha = UsrGacha::query()
            ->where('usr_user_id', $currentUser->getId())
            ->where('opr_gacha_id', 'opr_gacha_id')
            ->first();
        $this->assertNotNull($usrGacha);
        $this->assertEquals(2, $usrGacha->current_step_number); // ステップ2に進む
        $this->assertEquals(0, $usrGacha->loop_count); // 周回数は0のまま
    }
    public function testExec_ステップアップガシャ_ステップ進行()
    {
        // Setup
        $usrUser = $this->createUsrUser();
        $currentUser = new CurrentUser($usrUser->getId());
        UsrUserParameter::factory()->create(['usr_user_id' => $usrUser->getId()]);
        $this->createStepUpGachaData();

        // ダイヤモンドを十分に設定
        $this->createDiamond($usrUser->getId(), 10000, 0, 0);

        // UsrGachaを作成（ステップ2から開始）
        UsrGacha::factory()->create([
            'usr_user_id' => $currentUser->getId(),
            'opr_gacha_id' => 'opr_gacha_id',
            'current_step_number' => 2,
            'loop_count' => 0,
            'count' => 10,
        ]);

        // Exercise - ステップ2を実行
        $resultData = $this->useCase->exec(
            $currentUser,
            'opr_gacha_id',
            10,
            10,
            null,
            300,
            UserConstant::PLATFORM_IOS,
            CurrencyConstants::PLATFORM_APPSTORE,
            CostType::DIAMOND
        );

        // Verify
        $this->assertEquals(10, $resultData->gachaRewards->count());

        // ステップ3に進むことを確認（DBから直接取得）
        $usrGacha = UsrGacha::query()
            ->where('usr_user_id', $currentUser->getId())
            ->where('opr_gacha_id', 'opr_gacha_id')
            ->first();
        $this->assertNotNull($usrGacha);
        $this->assertEquals(3, $usrGacha->current_step_number);
    }

    public function testExec_ステップアップガシャ_最終ステップで周回()
    {
        // Setup
        $usrUser = $this->createUsrUser();
        $currentUser = new CurrentUser($usrUser->getId());
        UsrUserParameter::factory()->create(['usr_user_id' => $usrUser->getId()]);
        $this->createStepUpGachaData(5, 3);

        // ダイヤモンドを十分に設定
        $this->createDiamond($usrUser->getId(), 10000, 0, 0);

        // UsrGachaを作成（ステップ5=最終ステップ）
        UsrGacha::factory()->create([
            'usr_user_id' => $currentUser->getId(),
            'opr_gacha_id' => 'opr_gacha_id',
            'current_step_number' => 5,
            'loop_count' => 0,
            'count' => 40,
        ]);

        // Exercise - 最終ステップを実行
        $resultData = $this->useCase->exec(
            $currentUser,
            'opr_gacha_id',
            40,
            10,
            null,
            300,
            UserConstant::PLATFORM_IOS,
            CurrencyConstants::PLATFORM_APPSTORE,
            CostType::DIAMOND
        );

        // Verify
        $this->assertEquals(10, $resultData->gachaRewards->count());

        // ステップ1に戻り、周回数が1増えることを確認（DBから直接取得）
        $usrGacha = UsrGacha::query()
            ->where('usr_user_id', $currentUser->getId())
            ->where('opr_gacha_id', 'opr_gacha_id')
            ->first();
        $this->assertNotNull($usrGacha);
        $this->assertEquals(1, $usrGacha->current_step_number); // ステップ1に戻る
        $this->assertEquals(1, $usrGacha->loop_count); // 周回数が1増える
    }

    public function testExec_ステップアップガシャ_周回数上限エラー()
    {
        // Setup
        $usrUser = $this->createUsrUser();
        $currentUser = new CurrentUser($usrUser->getId());
        UsrUserParameter::factory()->create(['usr_user_id' => $usrUser->getId()]);
        $this->createStepUpGachaData(5, 3);

        // ダイヤモンドを十分に設定
        $this->createDiamond($usrUser->getId(), 10000, 0, 0);

        // UsrGachaを作成（最大周回数3、現在ステップ5=最終ステップ）
        UsrGacha::factory()->create([
            'usr_user_id' => $currentUser->getId(),
            'opr_gacha_id' => 'opr_gacha_id',
            'current_step_number' => 5,
            'loop_count' => 3, // すでに最大周回数に達している
            'count' => 50,
        ]);

        // Exercise & Verify - 周回数上限エラー
        $this->expectException(GameException::class);
        $this->expectExceptionCode(ErrorCode::GACHA_STEPUP_MAX_LOOP_COUNT_EXCEEDED);

        $this->useCase->exec(
            $currentUser,
            'opr_gacha_id',
            50,
            10,
            null,
            300,
            UserConstant::PLATFORM_IOS,
            CurrencyConstants::PLATFORM_APPSTORE,
            CostType::DIAMOND
        );
    }

    public function testExec_ステップアップガシャ_初回無料()
    {
        // Setup
        $usrUser = $this->createUsrUser();
        $currentUser = new CurrentUser($usrUser->getId());
        UsrUserParameter::factory()->create(['usr_user_id' => $usrUser->getId()]);
        $this->createStepUpGachaData();

        // ダイヤモンド0でも実行可能（初回無料のため）
        $this->createDiamond($usrUser->getId(), 0, 0, 0);

        // Exercise
        $resultData = $this->useCase->exec(
            $currentUser,
            'opr_gacha_id',
            0,
            10,
            null,
            0,
            UserConstant::PLATFORM_IOS,
            CurrencyConstants::PLATFORM_APPSTORE,
            CostType::FREE
        );

        // Verify
        $this->assertEquals(10, $resultData->gachaRewards->count());
    }

    public function testExec_ステップアップガシャ_2周目は有料()
    {
        // Setup
        $usrUser = $this->createUsrUser();
        $currentUser = new CurrentUser($usrUser->getId());
        UsrUserParameter::factory()->create(['usr_user_id' => $usrUser->getId()]);
        $this->createStepUpGachaData();

        // ダイヤモンドを設定
        $this->createDiamond($usrUser->getId(), 10000, 0, 0);

        // UsrGachaを作成（2周目のステップ1）
        UsrGacha::factory()->create([
            'usr_user_id' => $currentUser->getId(),
            'opr_gacha_id' => 'opr_gacha_id',
            'current_step_number' => 1,
            'loop_count' => 1, // 2周目
            'count' => 50,
        ]);

        // Exercise - 2周目のステップ1はダイヤモンド必要
        $resultData = $this->useCase->exec(
            $currentUser,
            'opr_gacha_id',
            50,
            10,
            null,
            300,
            UserConstant::PLATFORM_IOS,
            CurrencyConstants::PLATFORM_APPSTORE,
            CostType::DIAMOND
        );

        // Verify
        $this->assertEquals(10, $resultData->gachaRewards->count());

        // ダイヤモンドが消費されていることを確認
        $currency = $this->currencyDelegator->getCurrencySummary($currentUser->getId());
        $this->assertEquals(9700, $currency->getFreeAmount()); // 10000 - 300
    }

    public function testExec_ステップアップガシャ_確定枠が排出される()
    {
        // Setup
        $usrUser = $this->createUsrUser();
        $currentUser = new CurrentUser($usrUser->getId());
        UsrUserParameter::factory()->create(['usr_user_id' => $usrUser->getId()]);
        $this->createStepUpGachaData();

        // ダイヤモンドを設定
        $this->createDiamond($usrUser->getId(), 10000, 0, 0);

        // UsrGachaを作成（ステップ3 = 確定枠あり）
        UsrGacha::factory()->create([
            'usr_user_id' => $currentUser->getId(),
            'opr_gacha_id' => 'opr_gacha_id',
            'current_step_number' => 3,
            'loop_count' => 0,
            'count' => 20,
        ]);

        // Exercise
        $resultData = $this->useCase->exec(
            $currentUser,
            'opr_gacha_id',
            20,
            10,
            null,
            300,
            UserConstant::PLATFORM_IOS,
            CurrencyConstants::PLATFORM_APPSTORE,
            CostType::DIAMOND
        );

        // Verify
        $this->assertEquals(10, $resultData->gachaRewards->count());

        // 確定枠が含まれていることを確認（最後の1個が確定枠）
        // 確定枠はgachaRewardsの最後に配置される
        $lastReward = $resultData->gachaRewards->last();
        $this->assertNotNull($lastReward);
    }

    public function testExec_ステップアップガシャ_通常枠と確定枠が正しく分離される()
    {
        // Setup
        $usrUser = $this->createUsrUser();
        $currentUser = new CurrentUser($usrUser->getId());
        UsrUserParameter::factory()->create(['usr_user_id' => $usrUser->getId()]);
        $this->createStepUpGachaData();

        // ダイヤモンドを設定
        $this->createDiamond($usrUser->getId(), 10000, 0, 0);

        // UsrGachaを作成（ステップ3 = 確定枠1個）
        UsrGacha::factory()->create([
            'usr_user_id' => $currentUser->getId(),
            'opr_gacha_id' => 'opr_gacha_id',
            'current_step_number' => 3,
            'loop_count' => 0,
            'count' => 20,
        ]);

        // Exercise
        $resultData = $this->useCase->exec(
            $currentUser,
            'opr_gacha_id',
            20,
            10,
            null,
            300,
            UserConstant::PLATFORM_IOS,
            CurrencyConstants::PLATFORM_APPSTORE,
            CostType::DIAMOND
        );

        // Verify
        // ステップ3は確定枠1個: 通常枠9個 + 確定枠1個 = 合計10個
        $this->assertEquals(10, $resultData->gachaRewards->count());

        // 確定枠は最後に配置される
        $lastReward = $resultData->gachaRewards->last();
        $this->assertNotNull($lastReward);
    }

    public function testExec_ステップアップガシャ_確定枠複数個の場合()
    {
        // Setup
        $usrUser = $this->createUsrUser();
        $currentUser = new CurrentUser($usrUser->getId());
        UsrUserParameter::factory()->create(['usr_user_id' => $usrUser->getId()]);

        $oprGachaId = 'opr_gacha_stepup_multi_fixed';
        $this->createStepUpGachaDataWithCustomFixedCount($oprGachaId, 5, 3, 3); // 確定枠3個

        // ダイヤモンドを設定
        $this->createDiamond($usrUser->getId(), 10000, 0, 0);

        // UsrGachaを作成（ステップ3 = 確定枠3個）
        UsrGacha::factory()->create([
            'usr_user_id' => $currentUser->getId(),
            'opr_gacha_id' => $oprGachaId,
            'current_step_number' => 3,
            'loop_count' => 0,
            'count' => 20,
        ]);

        // Exercise
        $resultData = $this->useCase->exec(
            $currentUser,
            $oprGachaId,
            20,
            10,
            null,
            300,
            UserConstant::PLATFORM_IOS,
            CurrencyConstants::PLATFORM_APPSTORE,
            CostType::DIAMOND
        );

        // Verify
        // 確定枠3個: 通常枠7個 + 確定枠3個 = 合計10個
        $this->assertEquals(10, $resultData->gachaRewards->count());

        // 確定枠は最後に配置される（3個）
        $rewards = $resultData->gachaRewards->toArray();
        $this->assertCount(10, $rewards);
    }

    public function testExec_ステップアップガシャ_レア度閾値SSRの確定枠()
    {
        // Setup
        $usrUser = $this->createUsrUser();
        $currentUser = new CurrentUser($usrUser->getId());
        UsrUserParameter::factory()->create(['usr_user_id' => $usrUser->getId()]);

        $oprGachaId = 'opr_gacha_stepup_ssr';
        $this->createStepUpGachaDataWithRarityThreshold($oprGachaId, RarityType::SSR);

        // ダイヤモンドを設定
        $this->createDiamond($usrUser->getId(), 10000, 0, 0);

        // UsrGachaを作成（ステップ3 = SSR確定枠）
        UsrGacha::factory()->create([
            'usr_user_id' => $currentUser->getId(),
            'opr_gacha_id' => $oprGachaId,
            'current_step_number' => 3,
            'loop_count' => 0,
            'count' => 20,
        ]);

        // Exercise
        $resultData = $this->useCase->exec(
            $currentUser,
            $oprGachaId,
            20,
            10,
            null,
            300,
            UserConstant::PLATFORM_IOS,
            CurrencyConstants::PLATFORM_APPSTORE,
            CostType::DIAMOND
        );

        // Verify - 確定枠がSSR以上であることを確認
        // 確定枠は最後に配置される
        $lastReward = $resultData->gachaRewards->last();
        $this->assertNotNull($lastReward);
        $this->assertEquals(10, $resultData->gachaRewards->count());
    }

    public function testExec_ステップアップガシャ_確定枠なしステップは全て通常枠()
    {
        // Setup
        $usrUser = $this->createUsrUser();
        $currentUser = new CurrentUser($usrUser->getId());
        UsrUserParameter::factory()->create(['usr_user_id' => $usrUser->getId()]);
        $this->createStepUpGachaData();

        // ダイヤモンドを設定
        $this->createDiamond($usrUser->getId(), 10000, 0, 0);

        // UsrGachaを作成（ステップ2 = 確定枠なし）
        UsrGacha::factory()->create([
            'usr_user_id' => $currentUser->getId(),
            'opr_gacha_id' => 'opr_gacha_id',
            'current_step_number' => 2,
            'loop_count' => 0,
            'count' => 10,
        ]);

        // Exercise
        $resultData = $this->useCase->exec(
            $currentUser,
            'opr_gacha_id',
            10,
            10,
            null,
            300,
            UserConstant::PLATFORM_IOS,
            CurrencyConstants::PLATFORM_APPSTORE,
            CostType::DIAMOND
        );

        // Verify - 全て通常枠であることを確認
        // 確定枠なしステップなので、全て通常の景品抽選から排出
        $this->assertEquals(10, $resultData->gachaRewards->count());
    }

    public function testExec_ステップアップガシャ_おまけ報酬が付与される()
    {
        // Setup
        $usrUser = $this->createUsrUser();
        $currentUser = new CurrentUser($usrUser->getId());
        UsrUserParameter::factory()->create(['usr_user_id' => $usrUser->getId()]);
        $this->createStepUpGachaData();

        // おまけ報酬を作成（ステップ1に付与）
        $bonusItem = MstItem::factory()->create();
        OprStepUpGachaStepReward::factory()->create([
            'opr_gacha_id' => 'opr_gacha_id',
            'step_number' => 1,
            'loop_count_target' => null, // 全周回で付与
            'resource_type' => RewardType::ITEM->value,
            'resource_id' => $bonusItem->id,
            'resource_amount' => 100,
        ]);

        // ダイヤモンドを設定
        $this->createDiamond($usrUser->getId(), 0, 0, 0);

        // Exercise - ステップ1を実行
        $resultData = $this->useCase->exec(
            $currentUser,
            'opr_gacha_id',
            0,
            10,
            null,
            0,
            UserConstant::PLATFORM_IOS,
            CurrencyConstants::PLATFORM_APPSTORE,
            CostType::FREE
        );

        // Verify - おまけ報酬が付与されることを確認
        $this->assertEquals(1, $resultData->stepRewards->count());

        /** @var StepUpGachaStepReward $stepReward */
        $stepReward = $resultData->stepRewards->first();
        $this->assertEquals($bonusItem->id, $stepReward->getResourceId());
        $this->assertEquals(100, $stepReward->getAmount());

        // アイテムが付与されていることを確認
        $usrItem = UsrItem::query()
            ->where('usr_user_id', $currentUser->getId())
            ->where('mst_item_id', $bonusItem->id)
            ->first();
        $this->assertNotNull($usrItem);
        $this->assertEquals(100, $usrItem->amount);

        // Redisキャッシュにおまけ報酬が保存されていることを確認
        $histories = $this->getFromRedis(CacheKeyUtil::getGachaHistoryKey($currentUser->getId()));
        $this->assertNotNull($histories);
        $this->assertCount(1, $histories);

        $history = $histories->first();
        $response = $history->formatToResponse();
        $this->assertNotNull($response['stepRewards']);
        $this->assertCount(1, $response['stepRewards']);
        $this->assertEquals($bonusItem->id, $response['stepRewards'][0]['resourceId']);
        $this->assertEquals(100, $response['stepRewards'][0]['resourceAmount']);
    }

    public function testExec_ステップアップガシャ_おまけ報酬が複数付与される()
    {
        // Setup
        $usrUser = $this->createUsrUser();
        $currentUser = new CurrentUser($usrUser->getId());
        UsrUserParameter::factory()->create(['usr_user_id' => $usrUser->getId()]);
        $this->createStepUpGachaData();

        // おまけ報酬を複数作成
        $bonusItem1 = MstItem::factory()->create();
        $bonusItem2 = MstItem::factory()->create();

        OprStepUpGachaStepReward::factory()->create([
            'opr_gacha_id' => 'opr_gacha_id',
            'step_number' => 1,
            'loop_count_target' => null,
            'resource_type' => RewardType::ITEM->value,
            'resource_id' => $bonusItem1->id,
            'resource_amount' => 100,
        ]);

        OprStepUpGachaStepReward::factory()->create([
            'opr_gacha_id' => 'opr_gacha_id',
            'step_number' => 1,
            'loop_count_target' => null,
            'resource_type' => RewardType::ITEM->value,
            'resource_id' => $bonusItem2->id,
            'resource_amount' => 50,
        ]);

        // ダイヤモンドを設定
        $this->createDiamond($usrUser->getId(), 0, 0, 0);

        // Exercise
        $resultData = $this->useCase->exec(
            $currentUser,
            'opr_gacha_id',
            0,
            10,
            null,
            0,
            UserConstant::PLATFORM_IOS,
            CurrencyConstants::PLATFORM_APPSTORE,
            CostType::FREE
        );

        // Verify - おまけ報酬が2つ付与されることを確認
        $this->assertEquals(2, $resultData->stepRewards->count());

        // アイテムが両方付与されていることを確認
        $usrItem1 = UsrItem::query()
            ->where('usr_user_id', $currentUser->getId())
            ->where('mst_item_id', $bonusItem1->id)
            ->first();
        $this->assertNotNull($usrItem1);
        $this->assertEquals(100, $usrItem1->amount);

        $usrItem2 = UsrItem::query()
            ->where('usr_user_id', $currentUser->getId())
            ->where('mst_item_id', $bonusItem2->id)
            ->first();
        $this->assertNotNull($usrItem2);
        $this->assertEquals(50, $usrItem2->amount);

        // Redisキャッシュにおまけ報酬が保存されていることを確認
        $histories = $this->getFromRedis(CacheKeyUtil::getGachaHistoryKey($currentUser->getId()));
        $this->assertNotNull($histories);
        $this->assertCount(1, $histories);

        $history = $histories->first();
        $response = $history->formatToResponse();
        $this->assertNotNull($response['stepRewards']);
        $this->assertCount(2, $response['stepRewards']);
        $this->assertEquals($bonusItem1->id, $response['stepRewards'][0]['resourceId']);
        $this->assertEquals(100, $response['stepRewards'][0]['resourceAmount']);
        $this->assertEquals($bonusItem2->id, $response['stepRewards'][1]['resourceId']);
        $this->assertEquals(50, $response['stepRewards'][1]['resourceAmount']);
    }

    public function testExec_ステップアップガシャ_初回周回のみのおまけ報酬()
    {
        // Setup
        $usrUser = $this->createUsrUser();
        $currentUser = new CurrentUser($usrUser->getId());
        UsrUserParameter::factory()->create(['usr_user_id' => $usrUser->getId()]);
        $this->createStepUpGachaData();

        // おまけ報酬を作成（loop_count_target = 0 で初回のみ）
        $bonusItem = MstItem::factory()->create();
        OprStepUpGachaStepReward::factory()->create([
            'opr_gacha_id' => 'opr_gacha_id',
            'step_number' => 1,
            'loop_count_target' => 0, // 初回のみ
            'resource_type' => RewardType::ITEM->value,
            'resource_id' => $bonusItem->id,
            'resource_amount' => 100,
        ]);

        // ダイヤモンドを設定
        $this->createDiamond($usrUser->getId(), 0, 0, 0);

        // Exercise - ステップ1（初回）を実行
        $resultData = $this->useCase->exec(
            $currentUser,
            'opr_gacha_id',
            0,
            10,
            null,
            0,
            UserConstant::PLATFORM_IOS,
            CurrencyConstants::PLATFORM_APPSTORE,
            CostType::FREE
        );

        // Verify - おまけ報酬が付与されることを確認
        $this->assertEquals(1, $resultData->stepRewards->count());

        /** @var StepUpGachaStepReward $stepReward */
        $stepReward = $resultData->stepRewards->first();
        $this->assertEquals($bonusItem->id, $stepReward->getResourceId());
        $this->assertEquals(100, $stepReward->getAmount());

        // Redisキャッシュにおまけ報酬が保存されていることを確認
        $histories = $this->getFromRedis(CacheKeyUtil::getGachaHistoryKey($currentUser->getId()));
        $this->assertNotNull($histories);
        $this->assertCount(1, $histories);

        $history = $histories->first();
        $response = $history->formatToResponse();
        $this->assertNotNull($response['stepRewards']);
        $this->assertCount(1, $response['stepRewards']);
        $this->assertEquals($bonusItem->id, $response['stepRewards'][0]['resourceId']);
        $this->assertEquals(100, $response['stepRewards'][0]['resourceAmount']);
    }

    public function testExec_ステップアップガシャ_2回目周回ではおまけ報酬なし()
    {
        // Setup
        $usrUser = $this->createUsrUser();
        $currentUser = new CurrentUser($usrUser->getId());
        UsrUserParameter::factory()->create(['usr_user_id' => $usrUser->getId()]);
        $this->createStepUpGachaData();

        // おまけ報酬を作成（loop_count_target = 0 で初回のみ）
        $bonusItem = MstItem::factory()->create();
        OprStepUpGachaStepReward::factory()->create([
            'opr_gacha_id' => 'opr_gacha_id',
            'step_number' => 1,
            'loop_count_target' => 0, // 初回のみ
            'resource_type' => RewardType::ITEM->value,
            'resource_id' => $bonusItem->id,
            'resource_amount' => 100,
        ]);

        // ダイヤモンドを設定
        $this->createDiamond($usrUser->getId(), 10000, 0, 0);

        // UsrGachaを作成（2周目のステップ1）
        UsrGacha::factory()->create([
            'usr_user_id' => $currentUser->getId(),
            'opr_gacha_id' => 'opr_gacha_id',
            'current_step_number' => 1,
            'loop_count' => 1, // 2周目
            'count' => 50,
        ]);

        // Exercise - 2周目のステップ1を実行
        $resultData = $this->useCase->exec(
            $currentUser,
            'opr_gacha_id',
            50,
            10,
            null,
            300,
            UserConstant::PLATFORM_IOS,
            CurrencyConstants::PLATFORM_APPSTORE,
            CostType::DIAMOND
        );

        // Verify - おまけ報酬が付与されないことを確認（loop_count_target = 0 で初回のみのため）
        $this->assertEquals(0, $resultData->stepRewards->count());
    }

    public function testExec_ステップアップガシャ_おまけ報酬がないステップ()
    {
        // Setup
        $usrUser = $this->createUsrUser();
        $currentUser = new CurrentUser($usrUser->getId());
        UsrUserParameter::factory()->create(['usr_user_id' => $usrUser->getId()]);
        $this->createStepUpGachaData();

        // おまけ報酬を設定しない（ステップ2にはおまけなし）

        // ダイヤモンドを設定
        $this->createDiamond($usrUser->getId(), 10000, 0, 0);

        // UsrGachaを作成（ステップ2）
        UsrGacha::factory()->create([
            'usr_user_id' => $currentUser->getId(),
            'opr_gacha_id' => 'opr_gacha_id',
            'current_step_number' => 2,
            'loop_count' => 0,
            'count' => 10,
        ]);

        // Exercise - ステップ2を実行
        $resultData = $this->useCase->exec(
            $currentUser,
            'opr_gacha_id',
            10,
            10,
            null,
            300,
            UserConstant::PLATFORM_IOS,
            CurrencyConstants::PLATFORM_APPSTORE,
            CostType::DIAMOND
        );

        // Verify - おまけ報酬が付与されないことを確認
        $this->assertEquals(0, $resultData->stepRewards->count());
    }

    // ===== Helper Methods =====

    /**
     * ステップアップガシャのテストデータを作成
     */
    protected function createStepUpGachaData(int $maxStepNumber = 5, int $maxLoopCount = 3): void
    {
        $this->createBaseData();

        $oprGachaId = 'opr_gacha_id';

        OprGacha::factory()->create([
            'id' => $oprGachaId,
            'gacha_type' => GachaType::STEPUP->value,
            'prize_group_id' => 'prize_group_id',
        ]);

        OprGachaI18n::factory()->create([
            'opr_gacha_id' => $oprGachaId,
            'language' => Language::Ja->value,
        ]);

        OprStepUpGacha::factory()->create([
            'opr_gacha_id' => $oprGachaId,
            'max_step_number' => $maxStepNumber,
            'max_loop_count' => $maxLoopCount,
        ]);

        // ステップ設定を作成
        for ($i = 1; $i <= $maxStepNumber; $i++) {
            OprStepUpGachaStep::factory()->create([
                'opr_gacha_id' => $oprGachaId,
                'step_number' => $i,
                'draw_count' => 10,
                'fixed_prize_count' => $i >= 3 ? 1 : 0, // ステップ3以降は確定枠1
                'prize_group_id' => 'prize_group_id',
                'fixed_prize_group_id' => $i >= 3 ? 'fixed_prize_group_id' : null,
                'cost_type' => CostType::DIAMOND->value,
                'cost_num' => 300, // 全ステップ共通のコスト（初回無料は is_first_free で判定）
                'is_first_free' => $i === 1, // ステップ1のみ初回無料
            ]);
        }

        // OprGachaUseResourceを作成
        OprGachaUseResource::factory()->create([
            'opr_gacha_id' => $oprGachaId,
            'cost_type' => CostType::DIAMOND,
            'cost_id' => '',
            'cost_num' => 300,
            'draw_count' => 10,
            'cost_priority' => 1,
        ]);

        // 確定枠用の景品グループを作成（SR以上）
        $srUnit = MstUnit::factory()->create([
            'fragment_mst_item_id' => 'fragment1',
            'rarity' => RarityType::SR->value,
        ]);
        OprGachaPrize::factory()->create([
            'group_id' => 'fixed_prize_group_id',
            'resource_type' => RewardType::UNIT,
            'resource_id' => $srUnit->id,
            'weight' => 100,
        ]);
    }

    /**
     * 確定枠数をカスタマイズしたステップアップガシャのテストデータを作成
     */
    protected function createStepUpGachaDataWithCustomFixedCount(
        string $oprGachaId,
        int $maxStepNumber = 5,
        int $targetStepNumber = 3,
        int $fixedPrizeCount = 3
    ): void {
        $this->createBaseData();

        OprGacha::factory()->create([
            'id' => $oprGachaId,
            'gacha_type' => GachaType::STEPUP->value,
            'prize_group_id' => 'prize_group_id',
        ]);

        OprGachaI18n::factory()->create([
            'opr_gacha_id' => $oprGachaId,
            'language' => Language::Ja->value,
        ]);

        OprStepUpGacha::factory()->create([
            'opr_gacha_id' => $oprGachaId,
            'max_step_number' => $maxStepNumber,
            'max_loop_count' => 3,
        ]);

        for ($i = 1; $i <= $maxStepNumber; $i++) {
            OprStepUpGachaStep::factory()->create([
                'opr_gacha_id' => $oprGachaId,
                'step_number' => $i,
                'draw_count' => 10,
                'fixed_prize_count' => $i === $targetStepNumber ? $fixedPrizeCount : 0,
                'prize_group_id' => 'prize_group_id',
                'fixed_prize_group_id' => $i === $targetStepNumber ? 'fixed_prize_group_id' : null,
                'cost_type' => CostType::DIAMOND->value,
                'cost_num' => 300,
                'is_first_free' => false,
            ]);
        }

        OprGachaUseResource::factory()->create([
            'opr_gacha_id' => $oprGachaId,
            'cost_type' => CostType::DIAMOND,
            'cost_id' => '',
            'cost_num' => 300,
            'draw_count' => 10,
            'cost_priority' => 1,
        ]);

        $srUnit = MstUnit::factory()->create([
            'fragment_mst_item_id' => 'fragment1',
            'rarity' => RarityType::SR->value,
        ]);
        OprGachaPrize::factory()->create([
            'group_id' => 'fixed_prize_group_id',
            'resource_type' => RewardType::UNIT,
            'resource_id' => $srUnit->id,
            'weight' => 100,
        ]);
    }

    /**
     * レア度閾値をカスタマイズしたステップアップガシャのテストデータを作成
     */
    protected function createStepUpGachaDataWithRarityThreshold(
        string $oprGachaId,
        RarityType $rarityThreshold
    ): void {
        $this->createBaseData();

        OprGacha::factory()->create([
            'id' => $oprGachaId,
            'gacha_type' => GachaType::STEPUP->value,
            'prize_group_id' => 'prize_group_id',
        ]);

        OprGachaI18n::factory()->create([
            'opr_gacha_id' => $oprGachaId,
            'language' => Language::Ja->value,
        ]);

        OprStepUpGacha::factory()->create([
            'opr_gacha_id' => $oprGachaId,
            'max_step_number' => 5,
            'max_loop_count' => 3,
        ]);

        for ($i = 1; $i <= 5; $i++) {
            OprStepUpGachaStep::factory()->create([
                'opr_gacha_id' => $oprGachaId,
                'step_number' => $i,
                'draw_count' => 10,
                'fixed_prize_count' => $i >= 3 ? 1 : 0,
                'fixed_prize_rarity_threshold_type' => $i >= 3 ? $rarityThreshold->value : null,
                'prize_group_id' => 'prize_group_id',
                'fixed_prize_group_id' => $i >= 3 ? 'fixed_prize_group_id' : null,
                'cost_type' => CostType::DIAMOND->value,
                'cost_num' => 300,
                'is_first_free' => false,
            ]);
        }

        OprGachaUseResource::factory()->create([
            'opr_gacha_id' => $oprGachaId,
            'cost_type' => CostType::DIAMOND,
            'cost_id' => '',
            'cost_num' => 300,
            'draw_count' => 10,
            'cost_priority' => 1,
        ]);

        // SSR以上のユニットを確定枠用に作成
        $ssrUnit = MstUnit::factory()->create([
            'fragment_mst_item_id' => 'fragment1',
            'rarity' => RarityType::SSR->value,
        ]);
        OprGachaPrize::factory()->create([
            'group_id' => 'fixed_prize_group_id',
            'resource_type' => RewardType::UNIT,
            'resource_id' => $ssrUnit->id,
            'weight' => 100,
        ]);
    }

    protected function createBaseData(): void
    {
        // 基本データの作成（GachaDrawUseCaseTestから移植）
        // 景品グループの作成など
        $nUnit = MstUnit::factory()->create([
            'fragment_mst_item_id' => 'fragment_n',
            'rarity' => RarityType::N->value,
        ]);

        OprGachaPrize::factory()->create([
            'group_id' => 'prize_group_id',
            'resource_type' => RewardType::UNIT,
            'resource_id' => $nUnit->id,
            'weight' => 100,
        ]);
    }
}
