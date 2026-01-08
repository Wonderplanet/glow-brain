# テストパターン実装ガイド

## 3つのテストパターン

| パターン | 配置 | 特徴 | 依存の扱い |
|---------|------|------|-----------|
| **Unit** | `tests/Unit/` | 単一クラスのロジック | 全依存をモック化 |
| **Feature** | `tests/Feature/Domain/` | 複数クラスの統合 | 実DB使用 |
| **Scenario** | `tests/Feature/Scenario/` | エンドツーエンド | 複数APIリクエスト |

## Unit Test

全依存を`\Mockery::mock()`でモック化。

```php
class SignUpUseCaseTest extends BaseUseCaseTestCase
{
    public function test_アカウントが作成される()
    {
        // Setup - 全依存をモック
        $clock = \Mockery::mock(Clock::class);
        $clock->shouldReceive('now')->andReturn(CarbonImmutable::now());

        $useCase = new SignUpUseCase($clock, /* ... */);

        // Exercise & Verify
        $result = $useCase->exec($platform, $billingPlatform, $clientUuid);
        $this->assertArrayHasKey('id_token', $result);
    }
}
```

**または実DBパターン:**

```php
public function test_exec_正常動作()
{
    $this->fixTime('2025-04-01 00:00:00');
    MstUserLevel::factory()->create(['level' => 1]);

    $useCase = app()->make(SignUpUseCase::class);
    $result = $useCase->exec($platform, $billingPlatform, $clientUuid);

    $this->assertArrayHasKey('id_token', $result);
}
```

## Feature Test

### Service Test

```php
class ItemServiceTest extends TestCase
{
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
        $this->assertEquals(5, UsrItem::find([...])->getAmount());
    }
}
```

### Controller Test

```php
class GameControllerTest extends BaseControllerTestCase
{
    use WithoutMiddleware;
    protected string $baseUrl = '/api/game/';

    public function test_updateAndFetch_正常()
    {
        $usrUser = $this->createUsrUser();

        // UseCaseモック化
        $this->mock(GameUpdateAndFetchUseCase::class, function ($mock) {
            $mock->shouldReceive('__invoke')->andReturn([]);
        });

        $response = $this->sendRequest('update_and_fetch');
        $response->assertStatus(HttpStatusCode::SUCCESS);
    }
}
```

## Scenario Test

```php
class TutorialScenarioTest extends BaseControllerTestCase
{
    use TestMultipleApiRequestsTrait;

    public function setUp(): void
    {
        parent::setUp();
        $this->createMasterRelease(); // 必須
    }

    public function test_チュートリアル完了フロー()
    {
        // 1. サインアップ
        $response1 = $this->postJson($this->baseUrl . 'sign_up', $params);
        $this->resetAppForNextRequest($usrUserId); // 必須

        // 2. サインイン
        $response2 = $this->postJson($this->baseUrl . 'sign_in', $params);
        $this->resetAppForNextRequest($usrUserId); // 必須

        // Verify
        $this->assertEquals(1, UsrUserLogin::find([...])->getLoginCount());
    }
}
```

## 重要ポイント

| テスト種別 | saveAll()必要性 | 複数リクエスト |
|-----------|---------------|--------------|
| UseCase/Controller | 不要（自動保存） | - |
| Service/Repository | **必須** | - |
| Scenario | 不要（自動保存） | `resetAppForNextRequest()` 必須 |

**時間固定:** 常に`$this->fixTime()`を使用（`CarbonImmutable::now()`禁止）

詳細実装例は **[examples.md](examples.md)** 参照。
