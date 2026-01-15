# テスト実装例

実際のコードを参照して実装パターンを学ぶ。

## UseCase Unit Test

**参照:** `api/tests/Unit/Auth/SignUpUseCaseTest.php`

**特徴:**
- 全依存を`\Mockery::mock()`でモック化
- `BaseUseCaseTestCase`を継承
- Setup-Exercise-Verifyパターン

**主要パターン:**
```php
// モック作成とUseCase初期化
$clock = \Mockery::mock(Clock::class);
$useCase = new SignUpUseCase($clock, /* ... */);

// 振る舞い定義
$clock->shouldReceive('now')->andReturn($now);

// 実行と検証
$result = $useCase->exec($platform, $billingPlatform, $clientUuid);
$this->assertArrayHasKey('id_token', $result);
```

## Service Feature Test

**参照:** `api/tests/Feature/Domain/Item/ItemServiceTest.php`

**特徴:**
- 実DB使用（`RefreshDatabase`）
- `app(ItemService::class)`でインスタンス取得
- `saveAll()`で明示的保存

**主要パターン:**
```php
use TestRewardTrait;

public function test_apply_アイテム使用()
{
    // Setup
    $now = $this->fixTime();
    $usrUserId = $this->createUsrUser()->getId();
    MstItem::factory()->create(['id' => 'item1']);

    // Exercise
    $this->itemService->apply($usrUserId, $platform, $mstItem, 5, $now);
    $this->sendRewards($usrUserId, $platform, $now);
    $this->saveAll(); // 重要

    // Verify
    $usrItem = UsrItem::find(['usr_user_id' => $usrUserId]);
    $this->assertEquals(5, $usrItem->getAmount());
}
```

## Controller Feature Test

**参照:** `api/tests/Feature/Http/Controllers/Game/GameControllerTest.php`

**特徴:**
- `BaseControllerTestCase`継承
- UseCaseのみモック化
- `sendRequest()`でAPIリクエスト

**主要パターン:**
```php
use WithoutMiddleware;
protected string $baseUrl = '/api/game/';

public function test_updateAndFetch_正常()
{
    // Setup
    $usrUser = $this->createUsrUser();

    // UseCaseモック
    $this->mock(GameUpdateAndFetchUseCase::class, function ($mock) {
        $mock->shouldReceive('__invoke')->andReturn([]);
    });

    // Exercise & Verify
    $response = $this->sendRequest('update_and_fetch');
    $response->assertStatus(HttpStatusCode::SUCCESS);
}
```

## Scenario Test

**参照:** `api/tests/Feature/Scenario/TutorialLoginBonusScenarioTest.php`

**特徴:**
- `TestMultipleApiRequestsTrait`必須
- `resetAppForNextRequest()`で状態リセット
- `createMasterRelease()`でマスタ準備

**主要パターン:**
```php
use TestMultipleApiRequestsTrait;

public function setUp(): void
{
    parent::setUp();
    $this->createMasterRelease(); // 必須
}

public function test_チュートリアル完了フロー()
{
    // Setup
    $this->fixTime('2024-04-01 15:00:00');
    MstUserLevel::factory()->create(['level' => 1]);

    // 1. サインアップ
    $response1 = $this->postJson($this->baseUrl . 'sign_up', $params);
    $usrUserId = /* レスポンスから取得 */;
    $this->setUsrUserId($usrUserId);
    $this->resetAppForNextRequest($usrUserId); // 重要

    // 2. サインイン
    $this->fixTime('2024-04-01 15:01:00');
    $response2 = $this->postJson($this->baseUrl . 'sign_in', $params);
    $this->resetAppForNextRequest($usrUserId); // 重要

    // Verify
    $usrUserLogin = UsrUserLogin::where('usr_user_id', $usrUserId)->first();
    $this->assertEquals(1, $usrUserLogin->getLoginCount());
}
```

## テストパターン選択

| 要件 | パターン | 配置 | 参照ファイル |
|-----|---------|------|------------|
| ロジックのみテスト | Unit | `tests/Unit/` | SignUpUseCaseTest.php |
| DB統合テスト | Feature | `tests/Feature/Domain/` | ItemServiceTest.php |
| APIエンドポイント | Controller | `tests/Feature/Http/Controllers/` | GameControllerTest.php |
| ユーザーフロー | Scenario | `tests/Feature/Scenario/` | TutorialLoginBonusScenarioTest.php |

詳細は **[test-patterns.md](test-patterns.md)** 参照。
