<?php

declare(strict_types=1);

namespace Tests\Feature\Domain\Reward\Services;

use App\Domain\Common\Constants\ErrorCode;
use App\Domain\Common\Exceptions\GameException;
use App\Domain\Emblem\Models\UsrEmblem;
use App\Domain\Encyclopedia\Constants\EncyclopediaConstant;
use App\Domain\Encyclopedia\Models\UsrArtwork;
use App\Domain\Item\Enums\ItemType;
use App\Domain\Item\Models\Eloquent\UsrItem;
use App\Domain\Resource\Constants\MstConfigConstant;
use App\Domain\Resource\Entities\RewardSendPolicy;
use App\Domain\Resource\Enums\RewardType;
use App\Domain\Resource\Mst\Models\MstArtwork;
use App\Domain\Resource\Mst\Models\MstConfig;
use App\Domain\Resource\Mst\Models\MstEmblem;
use App\Domain\Resource\Mst\Models\MstItem;
use App\Domain\Resource\Mst\Models\MstUnit;
use App\Domain\Resource\Mst\Models\MstUserLevel;
use App\Domain\Reward\Managers\RewardManager;
use App\Domain\Reward\Services\RewardSendService;
use App\Domain\Unit\Models\Eloquent\UsrUnit;
use App\Domain\User\Constants\UserConstant;
use App\Domain\User\Models\UsrUserParameter;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\Feature\Domain\Reward\Test1Reward;
use Tests\TestCase;
use WonderPlanet\Domain\Currency\Services\CurrencyService;

class RewardSendServiceTest extends TestCase
{
    private RewardSendService $rewardSendService;
    private RewardManager $rewardManager;

    protected function setUp(): void
    {
        parent::setUp();

        $this->rewardSendService = $this->app->make(RewardSendService::class);
        $this->rewardManager = $this->app->make(RewardManager::class);
    }


    /**
     * テスト用のマスターデータを作成する
     */
    private function createMasterData(): void
    {
        // テスト用アイテム
        MstItem::factory()->createMany([
            ['id' => '1', 'type' => ItemType::ETC->value],
            ['id' => '2', 'type' => ItemType::ETC->value],
        ]);

        // テスト用エンブレム
        MstEmblem::factory()->createMany([
            ['id' => 'emblem_101'],
            ['id' => 'emblem_102'],
        ]);

        // テスト用ユニット
        MstUnit::factory()->createMany([
            ['id' => '1001'],
            ['id' => '1002'],
        ]);

        // テスト用ユーザーレベル
        MstUserLevel::factory()->createMany([
            ['level' => 1, 'exp' => 0],
            ['level' => 2, 'exp' => 1000],
            ['level' => 3, 'exp' => 2000],
        ]);
    }

    public function test_sendRewards_デフォルトポリシーで正常に全報酬タイプの報酬を配布できる(): void
    {
        // Setup
        $platform = UserConstant::PLATFORM_IOS;
        $now = $this->fixTime();

        // テスト用のユーザーを作成
        $usrUser = $this->createUsrUser();
        $usrUserId = $usrUser->getUsrUserId();

        // UsrUserParameterを作成
        UsrUserParameter::factory()->create([
            'usr_user_id' => $usrUserId,
            'level' => 1,
            'exp' => 0,
            'coin' => 0,
            'stamina' => 100,
            'stamina_updated_at' => $now->toDateTimeString(),
        ]);

        // プリズム用のデータを初期化
        $this->createDiamond(
            usrUserId: $usrUserId,
            freeDiamond: 0,
        );

        // テスト用のマスターデータを作成
        $this->createMasterData();

        // 全ての報酬タイプの報酬を複数追加
        $rewards = collect([
            // コイン（複数）
            new Test1Reward(RewardType::COIN, null, 100, 'test_coin_1'),
            new Test1Reward(RewardType::COIN, null, 200, 'test_coin_2'),
            // 無償プリズム（複数）
            new Test1Reward(RewardType::FREE_DIAMOND, null, 50, 'test_diamond_1'),
            new Test1Reward(RewardType::FREE_DIAMOND, null, 75, 'test_diamond_2'),
            // スタミナ（複数）
            new Test1Reward(RewardType::STAMINA, null, 10, 'test_stamina_1'),
            new Test1Reward(RewardType::STAMINA, null, 15, 'test_stamina_2'),
            // アイテム（複数）
            new Test1Reward(RewardType::ITEM, '1', 3, 'test_item_1'),
            new Test1Reward(RewardType::ITEM, '2', 5, 'test_item_2'),
            // エンブレム（複数）
            new Test1Reward(RewardType::EMBLEM, 'emblem_101', 1, 'test_emblem_1'),
            new Test1Reward(RewardType::EMBLEM, 'emblem_102', 1, 'test_emblem_2'),
            // 経験値（複数）
            new Test1Reward(RewardType::EXP, null, 500, 'test_exp_1'),
            new Test1Reward(RewardType::EXP, null, 750, 'test_exp_2'),
            // ユニット（複数）
            new Test1Reward(RewardType::UNIT, '1001', 1, 'test_unit_1'),
            new Test1Reward(RewardType::UNIT, '1002', 1, 'test_unit_2'),
        ]);

        $this->rewardManager->addRewards($rewards);

        // Exercise - policy=null（createDefaultPolicy使用）
        $result = $this->rewardSendService->sendRewards(
            usrUserId: $usrUserId,
            platform: $platform,
            now: $now,
            policy: null
        );
        $this->saveAll();

        // Verify - DBに保存されていることを確認
        // コイン・経験値・スタミナの確認（UsrUserParameter）
        $usrUserParameter = UsrUserParameter::query()->where('usr_user_id', $usrUserId)->first();
        $this->assertNotNull($usrUserParameter);
        $this->assertEquals(300, $usrUserParameter->getCoin(), 'コインが期待値と一致しません'); // 100 + 200
        $this->assertEquals(1250, $usrUserParameter->getExp(), '経験値が期待値と一致しません'); // 500 + 750
        $this->assertEquals(125, $usrUserParameter->getStamina(), 'スタミナが期待値と一致しません'); // 100 + 10 + 15

        // 無償プリズムの確認
        $currencyService = app(CurrencyService::class);
        $usrCurrencySummary = $currencyService->getCurrencySummary($usrUserId);
        $this->assertEquals(125, $usrCurrencySummary->getTotalAmount(), '無償プリズムが期待値と一致しません'); // 50 + 75

        // アイテムの確認
        $usrItems = UsrItem::query()->where('usr_user_id', $usrUserId)->get();
        $this->assertEquals(2, $usrItems->count(), 'アイテム数が期待値と一致しません');
        $mstItemIds = $usrItems->pluck('mst_item_id')->toArray();
        $this->assertContains('1', $mstItemIds, 'アイテムID:1が配布されていません');
        $this->assertContains('2', $mstItemIds, 'アイテムID:2が配布されていません');

        // エンブレムの確認
        $usrEmblems = UsrEmblem::query()->where('usr_user_id', $usrUserId)->get();
        $this->assertEquals(2, $usrEmblems->count(), 'エンブレム数が期待値と一致しません');
        $mstEmblemIds = $usrEmblems->pluck('mst_emblem_id')->toArray();
        $this->assertContains('emblem_101', $mstEmblemIds, 'エンブレムID:emblem_101が配布されていません');
        $this->assertContains('emblem_102', $mstEmblemIds, 'エンブレムID:emblem_102が配布されていません');

        // ユニットの確認
        $usrUnits = UsrUnit::query()->where('usr_user_id', $usrUserId)->get();
        $this->assertEquals(2, $usrUnits->count(), 'ユニット数が期待値と一致しません');
        $mstUnitIds = $usrUnits->pluck('mst_unit_id')->toArray();
        $this->assertContains('1001', $mstUnitIds, 'ユニットID:1001が配布されていません');
        $this->assertContains('1002', $mstUnitIds, 'ユニットID:1002が配布されていません');
    }

    /**
     * リソース上限超過パターンのデータプロバイダー
     */
    public static function params_test_sendRewards_制限超過エラーポリシーでリソース上限超過エラーが発生する(): array
    {
        return [
            'コインのみ上限超過' => [
                'coinExceeded' => true,
                'staminaExceeded' => false,
                'itemExceeded' => false,
            ],
            'スタミナのみ上限超過' => [
                'coinExceeded' => false,
                'staminaExceeded' => true,
                'itemExceeded' => false,
            ],
            'アイテムのみ上限超過' => [
                'coinExceeded' => false,
                'staminaExceeded' => false,
                'itemExceeded' => true,
            ],
            'コインとスタミナ上限超過' => [
                'coinExceeded' => true,
                'staminaExceeded' => true,
                'itemExceeded' => false,
            ],
            '全リソース上限超過' => [
                'coinExceeded' => true,
                'staminaExceeded' => true,
                'itemExceeded' => true,
            ],
        ];
    }

    #[DataProvider('params_test_sendRewards_制限超過エラーポリシーでリソース上限超過エラーが発生する')]
    public function test_sendRewards_制限超過エラーポリシーでリソース上限超過エラーが発生する(bool $coinExceeded, bool $staminaExceeded, bool $itemExceeded): void
    {
        // Setup - リソース上限の設定
        MstConfig::factory()->createMany([
            ['key' => MstConfigConstant::USER_COIN_MAX_AMOUNT, 'value' => '500'], // コイン上限
            ['key' => MstConfigConstant::USER_STAMINA_MAX_AMOUNT, 'value' => '150'], // スタミナ上限
            ['key' => MstConfigConstant::USER_ITEM_MAX_AMOUNT, 'value' => '3'], // アイテム上限
        ]);

        $platform = UserConstant::PLATFORM_IOS;
        $now = $this->fixTime();

        // テスト用のユーザーを作成
        $usrUser = $this->createUsrUser();
        $usrUserId = $usrUser->getUsrUserId();

        // UsrUserParameterを作成 - 現在の所持量を設定
        $currentCoin = $coinExceeded ? 400 : 100; // 上限超過時は400、通常は100
        $currentStamina = $staminaExceeded ? 140 : 100; // 上限超過時は140、通常は100

        UsrUserParameter::factory()->create([
            'usr_user_id' => $usrUserId,
            'level' => 1,
            'exp' => 0,
            'coin' => $currentCoin,
            'stamina' => $currentStamina,
            'stamina_updated_at' => $now->toDateTimeString(),
        ]);

        // プリズム用のデータを初期化
        $this->createDiamond(
            usrUserId: $usrUserId,
            freeDiamond: 0,
        );

        // テスト用のマスターデータを作成
        $this->createMasterData();

        // アイテムを設定 - 上限超過時は上限近くまで配布
        if ($itemExceeded) {
            UsrItem::factory()->createMany([
                ['usr_user_id' => $usrUserId, 'mst_item_id' => '1', 'amount' => 2],
                ['usr_user_id' => $usrUserId, 'mst_item_id' => '2', 'amount' => 1],
            ]);
        }

        // 報酬を設定 - 対象リソースのみ上限超過する量を配布
        $rewards = collect();

        if ($coinExceeded) {
            // コイン上限超過 (400 + 200 = 600 > 500)
            $rewards->push(new Test1Reward(RewardType::COIN, null, 200, 'test_coin_overflow'));
        }

        if ($staminaExceeded) {
            // スタミナ上限超過 (140 + 20 = 160 > 150)
            $rewards->push(new Test1Reward(RewardType::STAMINA, null, 20, 'test_stamina_overflow'));
        }

        if ($itemExceeded) {
            // アイテム上限超過 (既に3個所持 + 2個 = 5個 > 3個)
            $rewards->push(new Test1Reward(RewardType::ITEM, '1', 2, 'test_item_overflow'));
        }

        $this->rewardManager->addRewards($rewards);

        // Exercise & Verify - リソース上限超過エラーポリシーでエラーが発生することを確認
        $policy = RewardSendPolicy::createThrowErrorWhenResourceLimitReachedPolicy(
            new GameException(ErrorCode::LACK_OF_RESOURCES)
        );

        $this->expectException(GameException::class);
        $this->expectExceptionCode(ErrorCode::LACK_OF_RESOURCES);

        $this->rewardSendService->sendRewards(
            usrUserId: $usrUserId,
            platform: $platform,
            now: $now,
            policy: $policy
        );
    }

    /**
     * 報酬配布ロジック内で起きうるその他例外についても、投げることができていることを確認する
     *
     * 上限超過エラーは投げれているが、(例えばその他例外の)マスタデータがないエラーは投げれていない というケースが発生していないことを確認する
     */
    public function test_sendRewards_制限超過エラーポリシーで例外が発生した場合想定通りの例外になる(): void
    {
        // Setup
        $usrUserId = 'invalid_user_id'; // 存在しないユーザーID
        $platform = UserConstant::PLATFORM_IOS;
        $now = $this->fixTime();

        // 報酬を追加
        $rewards = collect([
            new Test1Reward(RewardType::ITEM, 'invalidMstItemId', 100),
        ]);

        $this->rewardManager->addRewards($rewards);

        // Exercise & Verify - policy=createThrowErrorWhenResourceLimitReachedPolicy使用で例外が発生することを確認
        $policy = RewardSendPolicy::createThrowErrorWhenResourceLimitReachedPolicy(
            new GameException(ErrorCode::MST_NOT_FOUND)
        );

        $this->expectException(GameException::class);
        $this->expectExceptionCode(ErrorCode::MST_NOT_FOUND);

        $this->rewardSendService->sendRewards(
            usrUserId: $usrUserId,
            platform: $platform,
            now: $now,
            policy: $policy
        );
    }

    /**
     * 重複原画がコインに変換されるテストのデータプロバイダー
     */
    public static function params_test_sendRewards_重複原画がコインに変換される(): array
    {
        return [
            '既に所持済みの原画を配布' => [
                'ownedMstArtworkIds' => ['artwork_owned'],
                'rewards' => [
                    ['mstArtworkId' => 'artwork_new', 'amount' => 1],
                    ['mstArtworkId' => 'artwork_owned', 'amount' => 1],
                ],
                'expectedMstArtworkIds' => ['artwork_new', 'artwork_owned'],
                'expectedConvertedCount' => 1,
            ],
            '同一原画を複数獲得' => [
                'ownedMstArtworkIds' => [],
                'rewards' => [
                    ['mstArtworkId' => 'artwork_multiple', 'amount' => 3],
                ],
                'expectedMstArtworkIds' => ['artwork_multiple'],
                'expectedConvertedCount' => 2,
            ],
            '所持済み原画を複数獲得' => [
                'ownedMstArtworkIds' => ['artwork_owned'],
                'rewards' => [
                    ['mstArtworkId' => 'artwork_owned', 'amount' => 3],
                ],
                'expectedMstArtworkIds' => ['artwork_owned'],
                'expectedConvertedCount' => 3,
            ],
        ];
    }

    /**
     * 重複原画がコインに変換されることを確認するテスト
     *
     * @param array<string> $ownedMstArtworkIds 既に所持済みの原画ID
     * @param array<array{mstArtworkId: string, amount: int}> $rewards 配布する報酬
     * @param array<string> $expectedMstArtworkIds 期待される原画ID
     * @param int $expectedConvertedCount コインに変換される原画数
     */
    #[DataProvider('params_test_sendRewards_重複原画がコインに変換される')]
    public function test_sendRewards_重複原画がコインに変換される(
        array $ownedMstArtworkIds,
        array $rewards,
        array $expectedMstArtworkIds,
        int $expectedConvertedCount
    ): void {
        // Setup
        $platform = UserConstant::PLATFORM_IOS;
        $now = $this->fixTime();

        $usrUser = $this->createUsrUser();
        $usrUserId = $usrUser->getUsrUserId();

        UsrUserParameter::factory()->create([
            'usr_user_id' => $usrUserId,
            'level' => 1,
            'exp' => 0,
            'coin' => 0,
            'stamina' => 100,
            'stamina_updated_at' => $now->toDateTimeString(),
        ]);

        $this->createDiamond(usrUserId: $usrUserId, freeDiamond: 0);

        // マスターデータ作成（報酬と所持済みから一意なIDを収集）
        $allMstArtworkIds = collect($rewards)->pluck('mstArtworkId')
            ->merge($ownedMstArtworkIds)
            ->unique()
            ->values();
        foreach ($allMstArtworkIds as $mstArtworkId) {
            MstArtwork::factory()->create(['id' => $mstArtworkId]);
        }

        // ユーザーが所持済みの原画を作成
        foreach ($ownedMstArtworkIds as $mstArtworkId) {
            UsrArtwork::factory()->create([
                'usr_user_id' => $usrUserId,
                'mst_artwork_id' => $mstArtworkId,
            ]);
        }

        // 報酬設定
        $rewardCollection = collect();
        foreach ($rewards as $index => $reward) {
            $rewardCollection->push(
                new Test1Reward(RewardType::ARTWORK, $reward['mstArtworkId'], $reward['amount'], "artwork_reward_{$index}")
            );
        }
        $this->rewardManager->addRewards($rewardCollection);

        // Exercise
        $this->rewardSendService->sendRewards(
            usrUserId: $usrUserId,
            platform: $platform,
            now: $now,
            policy: null
        );
        $this->saveAll();

        // Verify - 原画の確認
        $usrArtworks = UsrArtwork::query()->where('usr_user_id', $usrUserId)->get();
        $this->assertEquals(count($expectedMstArtworkIds), $usrArtworks->count());
        $mstArtworkIds = $usrArtworks->pluck('mst_artwork_id')->toArray();
        foreach ($expectedMstArtworkIds as $expectedMstArtworkId) {
            $this->assertContains($expectedMstArtworkId, $mstArtworkIds);
        }

        // Verify - コイン変換の確認
        $usrUserParameter = UsrUserParameter::query()->where('usr_user_id', $usrUserId)->first();
        $expectedCoin = $expectedConvertedCount * EncyclopediaConstant::DUPLICATE_ARTWORK_CONVERT_COIN;
        $this->assertEquals($expectedCoin, $usrUserParameter->getCoin());
    }
}
