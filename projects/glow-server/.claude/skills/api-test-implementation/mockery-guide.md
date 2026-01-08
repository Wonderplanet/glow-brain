# Mockeryガイド

## 基本パターン

```php
// Mock作成
$mockService = \Mockery::mock(ItemService::class);

// 振る舞い定義
$mockService->shouldReceive('apply')->andReturn(true);

// 引数・回数チェック
$mockService->shouldReceive('apply')
    ->once()
    ->with($usrUserId, $platform, $mstItem, $amount, $now)
    ->andReturn(true);

// 例外スロー
$mockService->shouldReceive('apply')
    ->andThrow(new GameException(ErrorCode::ITEM_NOT_FOUND));
```

## LaravelのDI統合

```php
// Controllerテストでモック化
$this->mock(UserChangeNameUseCase::class, function ($mock) {
    $mock->shouldReceive('exec')->andReturn([]);
});

// UseCaseが自動的にモックに置き換わる
$response = $this->sendRequest('change_name', ['name' => 'hoge']);
```

## 呼び出し回数

```php
$mock->shouldReceive('method')->once();      // 1回
$mock->shouldReceive('method')->twice();     // 2回
$mock->shouldReceive('method')->times(3);    // 3回
$mock->shouldReceive('method')->never();     // 呼ばれない
```

## 部分モック

```php
$this->mock(MissionUpdateHandleService::class)
    ->makePartial()
    ->shouldReceive('handleAllUpdateTriggeredMissions')
    ->andReturn();
```

## protectedメソッド

```php
$mockRepository = \Mockery::mock(UsrUserProfileRepository::class)->makePartial();
$mockRepository->shouldAllowMockingProtectedMethods();
$mockRepository->shouldReceive('makeMyIdNumString')->andReturn('1000000001');

$this->app->instance(UsrUserProfileRepository::class, $mockRepository);
```

## 引数マッチング

```php
// 任意の引数
$mock->shouldReceive('method')->withAnyArgs()->andReturn(true);

// 型マッチング
$mock->shouldReceive('method')->with(\Mockery::type('string'))->andReturn(true);

// Closure検証
$mock->shouldReceive('method')
    ->with(\Mockery::on(function ($arg) {
        return $arg > 0;
    }))
    ->andReturn(true);
```

## Clock Mockパターン

```php
$clock = \Mockery::mock(Clock::class);
$clock->shouldReceive('now')->andReturn(CarbonImmutable::parse('2025-04-01 00:00:00'));

$useCase = new SignUpUseCase($clock, /* ... */);
```
