# モック期待値不一致の修正パターン

## 概要

モック期待値不一致は、Mockeryで設定した期待値（shouldReceive）と実際の呼び出しが一致しない場合に発生します。

**典型的なエラーメッセージ**:
```
Mockery\Exception\NoMatchingExpectationException:
No matching handler found for ItemService::apply()

Mockery\Exception\InvalidCountException:
Method apply() should be called exactly 1 times but called 0 times
```

## 原因分析フロー

```
1. エラーメッセージからモック名とメソッド名を確認
   ↓
2. 期待値設定を確認
   ├─ メソッド名の一致
   ├─ 引数の一致
   ├─ 呼び出し回数
   └─ 返り値
   ↓
3. 実際の呼び出しを確認
   ↓
4. 不一致箇所を特定して修正
```

## 修正パターン1: メソッド名の不一致

### ケース1-1: メソッド名のスペルミス

**エラー**:
```
Mockery\Exception\NoMatchingExpectationException:
No matching handler found for ItemService::addItem()
```

**原因**: モックで設定したメソッド名と実際の呼び出しが異なる

**修正前**:
```php
public function test_useCase()
{
    $this->mock(ItemService::class, function (MockInterface $mock) {
        $mock->shouldReceive('additem')  // スペルミス: additem
            ->once()
            ->andReturn(true);
    });

    $this->useCase->exec($userId);  // エラー: addItem()が呼ばれる
}
```

**修正後**:
```php
public function test_useCase()
{
    $this->mock(ItemService::class, function (MockInterface $mock) {
        $mock->shouldReceive('addItem')  // ✅ 正しいメソッド名
            ->once()
            ->andReturn(true);
    });

    $this->useCase->exec($userId);  // 成功
}
```

## 修正パターン2: 引数の不一致

### ケース2-1: 引数の値が異なる

**エラー**:
```
Mockery\Exception\NoMatchingExpectationException:
No matching handler found for ItemService::apply('user_456', 'item_1', 10)
```

**原因**: with()で指定した引数と実際の呼び出しが異なる

**修正前**:
```php
public function test_useCase()
{
    $this->mock(ItemService::class, function (MockInterface $mock) {
        $mock->shouldReceive('apply')
            ->with('user_123', 'item_1', 10)  // 引数が異なる
            ->once();
    });

    $this->useCase->exec('user_456', 'item_1', 10);  // エラー: user_456で呼ばれる
}
```

**修正後**:
```php
public function test_useCase()
{
    $this->mock(ItemService::class, function (MockInterface $mock) {
        $mock->shouldReceive('apply')
            ->with('user_456', 'item_1', 10)  // ✅ 正しい引数
            ->once();
    });

    $this->useCase->exec('user_456', 'item_1', 10);  // 成功
}
```

### ケース2-2: 引数を柔軟にマッチング

**方法1: 任意の引数を許可**

```php
$mock->shouldReceive('apply')
    ->withAnyArgs()  // 任意の引数を許可
    ->once();
```

**方法2: 部分的に引数をチェック**

```php
use Mockery as m;

$mock->shouldReceive('apply')
    ->with(
        m::type('string'),     // 1番目: 文字列なら何でもOK
        'item_1',              // 2番目: 厳密に'item_1'
        m::type('int')         // 3番目: 整数なら何でもOK
    )
    ->once();
```

**方法3: 引数を後から確認**

```php
$mock->shouldReceive('apply')
    ->andReturnUsing(function ($userId, $itemId, $amount) {
        // 引数の内容をテスト内で確認
        $this->assertIsString($userId);
        $this->assertEquals('item_1', $itemId);
        $this->assertGreaterThan(0, $amount);
        return true;
    });
```

### ケース2-3: 引数の順序が異なる

**修正前**:
```php
$mock->shouldReceive('process')
    ->with($itemId, $userId)  // 順序が逆
    ->once();

$this->useCase->exec($userId, $itemId);  // エラー
```

**修正後**:
```php
$mock->shouldReceive('process')
    ->with($userId, $itemId)  // ✅ 正しい順序
    ->once();

$this->useCase->exec($userId, $itemId);  // 成功
```

## 修正パターン3: 呼び出し回数の不一致

### ケース3-1: 呼び出し回数が期待より多い/少ない

**エラー**:
```
Mockery\Exception\InvalidCountException:
Method apply() should be called exactly 1 times but called 2 times
```

**原因**: once()で1回を期待しているが実際は2回呼ばれている

**修正前**:
```php
public function test_useCase()
{
    $this->mock(ItemService::class, function (MockInterface $mock) {
        $mock->shouldReceive('apply')
            ->once();  // 1回を期待
    });

    // 実際は2回呼び出される
    $this->useCase->exec($userId);  // エラー
}
```

**修正後（パターンA: 期待値を修正）**:
```php
public function test_useCase()
{
    $this->mock(ItemService::class, function (MockInterface $mock) {
        $mock->shouldReceive('apply')
            ->times(2);  // ✅ 2回に変更
    });

    $this->useCase->exec($userId);  // 成功
}
```

**修正後（パターンB: 実装を修正）**:
```php
// 実装コードを修正して1回のみ呼ぶように変更
```

### ケース3-2: 呼び出し回数の指定方法

| メソッド | 意味 |
|---------|------|
| `once()` | 1回 |
| `twice()` | 2回 |
| `times(n)` | n回 |
| `atLeast()->times(n)` | 最低n回 |
| `atMost()->times(n)` | 最大n回 |
| `between(m, n)` | m回以上n回以下 |
| `zeroOrMoreTimes()` | 0回以上 |
| `never()` | 0回（呼ばれない） |

**例**:
```php
// 最低1回
$mock->shouldReceive('apply')
    ->atLeast()->once();

// 最大3回
$mock->shouldReceive('apply')
    ->atMost()->times(3);

// 呼ばれないことを期待
$mock->shouldReceive('delete')
    ->never();
```

## 修正パターン4: 返り値の問題

### ケース4-1: 返り値の型が間違っている

**エラー**:
```
TypeError: Return value must be of type array, bool returned
```

**修正前**:
```php
$this->mock(ItemService::class, function (MockInterface $mock) {
    $mock->shouldReceive('getItems')
        ->andReturn(true);  // バグ: 配列を返すべき
});

$items = $this->useCase->exec($userId);  // エラー: 配列が期待される
```

**修正後**:
```php
$this->mock(ItemService::class, function (MockInterface $mock) {
    $mock->shouldReceive('getItems')
        ->andReturn([]);  // ✅ 配列を返す
});

$items = $this->useCase->exec($userId);  // 成功
```

### ケース4-2: 複数回呼び出しで異なる値を返す

```php
$mock->shouldReceive('getAmount')
    ->andReturn(10, 20, 30);  // 1回目は10、2回目は20、3回目は30

// または
$mock->shouldReceive('getAmount')
    ->andReturnValues([10, 20, 30]);
```

### ケース4-3: 例外を返す

```php
$mock->shouldReceive('process')
    ->andThrow(new \RuntimeException('Test exception'));
```

## 修正パターン5: モックのバインディング問題

### ケース5-1: モックが適用されていない

**エラー**:
```
Method apply() should be called exactly 1 times but called 0 times
```

**原因**: DIコンテナのバインディングタイミングが間違っている

**修正前**:
```php
public function test_useCase()
{
    // UseCaseを先に取得
    $useCase = app()->make(ItemUseCase::class);

    // モックを後から設定（適用されない）
    $this->mock(ItemService::class, function (MockInterface $mock) {
        $mock->shouldReceive('apply')->once();
    });

    $useCase->exec($userId);  // エラー: モックが呼ばれない
}
```

**修正後**:
```php
public function test_useCase()
{
    // 先にモックを設定
    $this->mock(ItemService::class, function (MockInterface $mock) {
        $mock->shouldReceive('apply')->once();
    });

    // モック設定後にUseCaseを取得
    $useCase = app()->make(ItemUseCase::class);  // ✅ モックが適用される

    $useCase->exec($userId);  // 成功
}
```

### ケース5-2: Controllerテストでのモック

```php
public function test_controller()
{
    // UseCaseをモック化
    $this->mock(ItemUseCase::class, function (MockInterface $mock) {
        $mock->shouldReceive('__invoke')  // Controllerから呼ばれる
            ->once()
            ->andReturn([]);
    });

    $response = $this->postJson($this->baseUrl . 'item/add', $params);
    $response->assertStatus(200);
}
```

## デバッグ手順

### ステップ1: エラーメッセージを読む

```
Mockery\Exception\NoMatchingExpectationException:
No matching handler found for ItemService::apply('user_456', 'item_1', 10)
```

**確認事項**:
- クラス: ItemService
- メソッド: apply
- 実際の引数: ('user_456', 'item_1', 10)

### ステップ2: モック設定を確認

```php
$mock->shouldReceive('apply')
    ->with('user_123', 'item_1', 10)  // 'user_123'を期待しているが'user_456'が来る
    ->once();
```

### ステップ3: andReturnUsing()でデバッグ

```php
$this->mock(ItemService::class, function (MockInterface $mock) {
    $mock->shouldReceive('apply')
        ->andReturnUsing(function (...$args) {
            dump('apply called with:', $args);  // 実際の引数を確認
            return true;
        });
});

$this->useCase->exec($userId);
```

### ステップ4: 原因を特定

dump()の出力から不一致箇所を特定:
- メソッド名が違う → **修正パターン1**
- 引数が違う → **修正パターン2**
- 呼び出し回数が違う → **修正パターン3**

### ステップ5: 修正して確認

修正後、dump()を削除してテストを再実行。

## よくある間違い

### 間違い1: モック設定の順序

```php
// ❌ 間違い: UseCaseを先に取得
$useCase = app()->make(ItemUseCase::class);
$this->mock(ItemService::class, ...);  // モックが適用されない

// ✅ 正しい: モックを先に設定
$this->mock(ItemService::class, ...);
$useCase = app()->make(ItemUseCase::class);
```

### 間違い2: 複数のモック設定

```php
// ❌ 間違い: 同じメソッドを2回設定（後者で上書き）
$mock->shouldReceive('apply')->once();
$mock->shouldReceive('apply')->twice();  // 上書きされる

// ✅ 正しい: 1つの設定にまとめる
$mock->shouldReceive('apply')->twice();
```

### 間違い3: 引数の型チェック

```php
// ❌ 間違い: 厳密すぎる
$mock->shouldReceive('apply')
    ->with('123', 'item_1', 10);  // '123'（文字列）を期待

$this->useCase->exec(123, 'item_1', 10);  // エラー: 123（整数）が来る

// ✅ 正しい: 型を許容
use Mockery as m;

$mock->shouldReceive('apply')
    ->with(m::type('int'), 'item_1', 10);  // 整数なら何でもOK
```

## 高度なテクニック

### テクニック1: 部分モック

一部のメソッドのみモック化

```php
$service = Mockery::mock(ItemService::class)->makePartial();
$service->shouldReceive('getAmount')
    ->andReturn(100);  // このメソッドのみモック

// 他のメソッドは実際の実装が呼ばれる
```

### テクニック2: スパイ

実際の呼び出しを記録

```php
$spy = Mockery::spy(ItemService::class);

$this->useCase->exec($userId);

// 呼び出しを検証
$spy->shouldHaveReceived('apply')
    ->with($userId, 'item_1', 10)
    ->once();
```

### テクニック3: 複雑な引数検証

```php
$mock->shouldReceive('process')
    ->with(m::on(function ($arg) {
        return is_array($arg) && count($arg) > 0;
    }))
    ->once();
```

## チェックリスト

### エラー発生時
- [ ] エラーメッセージからクラス/メソッド名を確認
- [ ] 期待した引数と実際の引数を確認
- [ ] 呼び出し回数を確認

### 修正前
- [ ] モック設定の順序が正しいか確認
- [ ] メソッド名のスペルミスがないか確認
- [ ] 引数の値・型・順序が正しいか確認
- [ ] 呼び出し回数が正しいか確認

### デバッグ時
- [ ] andReturnUsing()で実際の呼び出しを確認
- [ ] dump()で引数の内容を確認
- [ ] モックが適用されているか確認

### 修正後
- [ ] dump()、andReturnUsing()を削除
- [ ] テストを再実行して成功を確認
- [ ] 他のテストに影響がないか確認

### コミット前
- [ ] 全テストがPASS
- [ ] デバッグ用コードが残っていない
- [ ] モック設定が適切に記述されている
