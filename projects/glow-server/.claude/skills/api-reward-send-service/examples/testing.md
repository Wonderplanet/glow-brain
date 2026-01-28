# テスト実装例

RewardSendServiceを使った機能のテスト実装パターンを紹介します。

## 目次

- [基本的なテストパターン](#基本的なテストパターン)
- [実装例: 全報酬タイプのテスト](#実装例-全報酬タイプのテスト)
- [実装例: リソース上限超過テスト](#実装例-リソース上限超過テスト)
- [テストデータの準備](#テストデータの準備)
- [ポイント](#ポイント)

## 基本的なテストパターン

### テストクラスの構成

```php
use Tests\TestCase;

class YourUseCaseTest extends TestCase
{
    private YourUseCase $yourUseCase;
    private RewardManager $rewardManager;

    protected function setUp(): void
    {
        parent::setUp();

        $this->yourUseCase = $this->app->make(YourUseCase::class);
        $this->rewardManager = $this->app->make(RewardManager::class);
    }

    public function test_報酬が正常に配布できる(): void
    {
        // Setup: テストデータ準備
        // Exercise: 実行
        // Verify: 検証
    }
}
```

### テストの基本フロー

```php
public function test_報酬が正常に配布できる(): void
{
    // Setup
    $usrUser = $this->createUsrUser();
    $usrUserId = $usrUser->getUsrUserId();
    $now = $this->fixTime();

    // マスターデータ作成
    MstItem::factory()->create(['id' => '1']);

    // Exercise
    $this->yourUseCase->exec($usrUserId, $platform, $now);
    $this->saveAll();

    // Verify
    $usrItem = UsrItem::query()
        ->where('usr_user_id', $usrUserId)
        ->where('mst_item_id', '1')
        ->first();

    $this->assertNotNull($usrItem);
    $this->assertEquals(10, $usrItem->getAmount());
}
```

## 実装例: 全報酬タイプのテスト

**ファイルパス**: `api/tests/Feature/Domain/Reward/Services/RewardSendServiceTest.php`

全報酬タイプ（COIN, FREE_DIAMOND, STAMINA, ITEM, EXP, EMBLEM, UNIT）の配布をテストする実装例です。

### セットアップ

```php
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
}
```

### 全報酬タイプの配布テスト

```php
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
```

## 実装例: リソース上限超過テスト

### DataProviderを使ったパラメータ化テスト

```php
/**
 * リソース上限超過パターンのデータプロバイダー
 */
public static function params_test_sendRewards_制限超過エラーポリシーでリソース上限超過エラーが発生する(): array
{
    return [
        'コインのみ上限超過' => [
            'coin' => true,
            'stamina' => false,
            'item' => false,
        ],
        'スタミナのみ上限超過' => [
            'coin' => false,
            'stamina' => true,
            'item' => false,
        ],
        'アイテムのみ上限超過' => [
            'coin' => false,
            'stamina' => false,
            'item' => true,
        ],
        'コインとスタミナ上限超過' => [
            'coin' => true,
            'stamina' => true,
            'item' => false,
        ],
        '全リソース上限超過' => [
            'coin' => true,
            'stamina' => true,
            'item' => true,
        ],
    ];
}
```

### パラメータ化テスト本体

```php
#[DataProvider('params_test_sendRewards_制限超過エラーポリシーでリソース上限超過エラーが発生する')]
public function test_sendRewards_制限超過エラーポリシーでリソース上限超過エラーが発生する(
    bool $coinExceeded,
    bool $staminaExceeded,
    bool $itemExceeded
): void {
    // Setup - リソース上限の設定
    MstConfig::factory()->createMany([
        ['key' => MstConfigConstant::USER_COIN_MAX_AMOUNT, 'value' => '500'], // コイン上限
        ['key' => MstConfigConstant::USER_STAMINA_MAX_AMOUNT, 'value' => '150'], // スタミナ上限
        ['key' => MstConfigConstant::USER_ITEM_MAX_AMOUNT, 'value' => '3'], // アイテム上限
    ]);

    $platform = UserConstant::PLATFORM_IOS;
    $now = $this->fixTime();

    $usrUser = $this->createUsrUser();
    $usrUserId = $usrUser->getUsrUserId();

    // 現在の所持量を設定（上限近くに設定）
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

    $this->createDiamond(usrUserId: $usrUserId, freeDiamond: 0);
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
```

### その他の例外テスト

```php
/**
 * 報酬配布ロジック内で起きうるその他例外についても、投げることができていることを確認する
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
```

## テストデータの準備

### テスト用報酬クラス

```php
/**
 * テスト用の報酬クラス
 */
class Test1Reward extends BaseReward
{
    public function __construct(
        string $type,
        ?string $id,
        int $amount,
        ?string $uniqueId = null,
    ) {
        parent::__construct($type, $id, $amount, $uniqueId);
    }
}
```

### Factory使用パターン

```php
// ユーザーデータ作成
$usrUser = $this->createUsrUser();

// UsrUserParameter作成
UsrUserParameter::factory()->create([
    'usr_user_id' => $usrUserId,
    'level' => 1,
    'exp' => 0,
    'coin' => 100,
]);

// マスターデータ作成
MstItem::factory()->create(['id' => '1']);
MstEmblem::factory()->create(['id' => 'emblem_101']);
MstUnit::factory()->create(['id' => '1001']);
```

## ポイント

### ✅ テストは3ステップで構成

```php
// Setup: テストデータ準備
// Exercise: 実行
// Verify: 検証
```

### ✅ RewardManagerを注入してテスト

```php
$this->rewardManager = $this->app->make(RewardManager::class);
$this->rewardManager->addRewards($rewards);
```

### ✅ DataProviderで複数パターンをテスト

```php
#[DataProvider('params_test_xxx')]
public function test_xxx(bool $coinExceeded, bool $staminaExceeded): void
{
    // テストロジック
}
```

### ✅ アサーションメッセージを明記

```php
$this->assertEquals(300, $usrUserParameter->getCoin(), 'コインが期待値と一致しません');
$this->assertEquals(125, $usrUserParameter->getStamina(), 'スタミナが期待値と一致しません');
```

### ✅ saveAll()を呼んでからVerify

```php
// Exercise
$this->rewardSendService->sendRewards(...);
$this->saveAll(); // 重要！

// Verify
$usrItem = UsrItem::query()->where(...)->first();
```

### ❌ 間違ったテスト

```php
// ❌ saveAll()を呼ばずに検証
$this->rewardSendService->sendRewards(...);
$usrItem = UsrItem::query()->where(...)->first(); // データが保存されていない

// ❌ アサーションメッセージなし
$this->assertEquals(300, $usrUserParameter->getCoin()); // 失敗時に何が間違っているか分からない
```

## まとめ

- **3ステップ構成**: Setup → Exercise → Verify
- **RewardManagerを使う**: テスト用報酬を追加
- **DataProvider活用**: 複数パターンを効率的にテスト
- **saveAll()必須**: Verify前に必ず実行
- **アサーションメッセージ**: 失敗時の原因を明確にする

基本的な使い方は **[examples/basic-usage.md](basic-usage.md)** を、エラーハンドリングは **[examples/error-handling.md](error-handling.md)** を参照してください。
