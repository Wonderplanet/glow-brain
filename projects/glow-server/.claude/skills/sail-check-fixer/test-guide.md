# Test ガイド: テストエラーの修正

## 目次

1. [概要](#概要)
2. [基本的な使い方](#基本的な使い方)
3. [エラータイプ別修正方法](#エラータイプ別修正方法)
4. [よくあるテストエラー](#よくあるテストエラー)
5. [テストデバッグ方法](#テストデバッグ方法)
6. [カバレッジについて](#カバレッジについて)
7. [トラブルシューティング](#トラブルシューティング)

## 概要

### PHPUnitテストとは

PHPUnitは、PHPのユニットテスト・統合テストを実行するフレームワーク。

**テストの種類**:
- **Feature Test** (統合テスト): 複数コンポーネントを組み合わせたテスト
- **Unit Test** (ユニットテスト): 個別のクラス・メソッドをテスト

### テスト設定

**設定ファイル**: `api/phpunit.xml`

**テストディレクトリ**:
- `api/tests/Feature/` - フィーチャーテスト
- `api/tests/Unit/` - ユニットテスト
- `api/tests/Support/` - テスト支援クラス

**テストガイド**: `api/.claude/outputs/docs/api/glow-server-test-implementation-guide.md`

## 基本的な使い方

### 全テストを実行

```bash
# 全テストを実行（カバレッジ付き）
./tools/bin/sail-wp test --coverage | grep -v '100.0 %'

# 実行されるコマンド（内部）
# docker-compose exec php php artisan test --coverage | grep -v '100.0 %'
```

### 特定のテストファイルを実行

```bash
# 特定のテストクラスを実行
./tools/bin/sail-wp test --filter ExampleServiceTest

# 特定のテストメソッドを実行
./tools/bin/sail-wp test --filter test_example_method
```

### エラー表示形式

```
FAILED  Tests\Feature\Domain\Example\ExampleServiceTest > test_example
  Failed asserting that 10 matches expected 5.

  at tests/Feature/Domain/Example/ExampleServiceTest.php:67
    63▕     $this->saveAll();
    64▕
    65▕     // Verify
    66▕     $result = $this->exampleService->getResult($userId);
  ➜ 67▕     $this->assertEquals(5, $result);
    68▕ }

Tests:    1 failed, 99 passed (150 assertions)
Duration: 12.34s
```

### 成功時の出力

```
Tests:    100 passed (150 assertions)
Duration: 12.34s
```

## エラータイプ別修正方法

### 1. アサーションエラー (Assertion Failed)

#### エラーメッセージ

```
Failed asserting that 10 matches expected 5.
```

#### 原因

期待値と実際の値が異なる。

#### 修正方法1: テストデータを調整

**修正前**:
```php
public function test_example()
{
    $user = $this->createUsrUser();
    $this->createDiamond($user->getId(), 10, 0, 0);  // 10ダイヤモンド

    $result = $this->service->getDiamondAmount($user->getId());
    $this->assertEquals(5, $result);  // エラー: 10だが5を期待
}
```

**修正後**:
```php
public function test_example()
{
    $user = $this->createUsrUser();
    $this->createDiamond($user->getId(), 5, 0, 0);  // 5ダイヤモンドに変更

    $result = $this->service->getDiamondAmount($user->getId());
    $this->assertEquals(5, $result);  // 成功
}
```

#### 修正方法2: 期待値を修正

**修正前**:
```php
$this->assertEquals(5, $result);  // 期待値が間違っている
```

**修正後**:
```php
$this->assertEquals(10, $result);  // 正しい期待値
```

#### 修正方法3: 実装コードを修正

実装コードにバグがある場合は、実装を修正。

### 2. 例外エラー (Exception)

#### エラーメッセージ

```
RuntimeException: User not found

at app/Domain/Example/Services/ExampleService.php:45
```

#### 原因

想定外の例外が発生している。

#### 修正方法1: テストデータを準備

**修正前**:
```php
public function test_example()
{
    // ユーザーを作成していない
    $result = $this->service->getUser('user_123');  // エラー: ユーザーが存在しない
}
```

**修正後**:
```php
public function test_example()
{
    // ユーザーを作成
    $user = $this->createUsrUser(['id' => 'user_123']);

    $result = $this->service->getUser('user_123');  // 成功
    $this->assertNotNull($result);
}
```

#### 修正方法2: 例外を期待する

意図的に例外をテストしている場合：

```php
public function test_exception_when_user_not_found()
{
    $this->expectException(RuntimeException::class);
    $this->expectExceptionMessage('User not found');

    $this->service->getUser('invalid_user_id');  // 例外を期待
}
```

### 3. セットアップエラー (Setup Failed)

#### エラーメッセージ

```
Error: Call to a member function getId() on null
```

#### 原因

テストの前提条件が整っていない。

#### 修正方法: setUp()で初期化

**修正前**:
```php
public function test_example()
{
    $userId = $this->user->getId();  // エラー: $this->userが初期化されていない
}
```

**修正後**:
```php
private UsrUser $user;

protected function setUp(): void
{
    parent::setUp();
    $this->user = $this->createUsrUser();  // setUp()で初期化
}

public function test_example()
{
    $userId = $this->user->getId();  // 成功
}
```

### 4. データベースエラー (Database)

#### エラーメッセージ

```
SQLSTATE[23000]: Integrity constraint violation
```

#### 原因

データベース制約違反（外部キー制約、ユニーク制約等）。

#### 修正方法: 依存データを先に作成

**修正前**:
```php
public function test_example()
{
    UsrItem::factory()->create([
        'usr_user_id' => 'user_123',  // エラー: ユーザーが存在しない
        'mst_item_id' => 'item_1',
    ]);
}
```

**修正後**:
```php
public function test_example()
{
    // 先にユーザーを作成
    $user = $this->createUsrUser(['id' => 'user_123']);

    // 次にアイテムを作成
    UsrItem::factory()->create([
        'usr_user_id' => $user->getId(),
        'mst_item_id' => 'item_1',
    ]);
}
```

### 5. モックエラー (Mock)

#### エラーメッセージ

```
Mockery\Exception\NoMatchingExpectationException:
No matching handler found for ExampleService::process()
```

#### 原因

モックの期待設定が実際の呼び出しと一致しない。

#### 修正方法: モックの期待を正確に設定

**修正前**:
```php
$this->mock(ExampleService::class, function (MockInterface $mock) {
    $mock->shouldReceive('process')
        ->with('user_123')  // 引数が異なる
        ->once();
});

$this->useCase->exec('user_456');  // エラー: 'user_456'で呼ばれるが、'user_123'を期待
```

**修正後**:
```php
$this->mock(ExampleService::class, function (MockInterface $mock) {
    $mock->shouldReceive('process')
        ->with('user_456')  // 正しい引数
        ->once();
});

$this->useCase->exec('user_456');  // 成功
```

## よくあるテストエラー

### エラー1: RefreshDatabaseの重複インポート

**エラーメッセージ**:
```
Trait 'RefreshDatabase' not found or conflict
```

**原因**: TestCaseを継承しているのに、RefreshDatabaseを再度インポート

**修正前**:
```php
use Illuminate\Foundation\Testing\RefreshDatabase;  // ❌ 重複インポート

class ExampleTest extends TestCase
{
    use RefreshDatabase;  // ❌ 親クラスで既に使用
}
```

**修正後**:
```php
class ExampleTest extends TestCase
{
    // RefreshDatabaseは親クラス(TestCase)で使用済み
}
```

**理由**: `api/tests/TestCase.php`で既に`RefreshDatabase`を使用しており、子クラスでの再インポートは親クラスの調整処理を無効化してしまう。

### エラー2: saveAll()の実行漏れ

**エラーメッセージ**:
```
Failed asserting that 0 matches expected 10.
```

**原因**: UsrModel/LogModelの更新が保存されていない

**修正前**:
```php
public function test_example()
{
    $user = $this->createUsrUser();
    $this->itemService->addItem($user->getId(), 'item_1', 10);
    // saveAll()を実行していない

    $items = UsrItem::where('usr_user_id', $user->getId())->get();
    $this->assertCount(1, $items);  // エラー: データが保存されていない
}
```

**修正後**:
```php
public function test_example()
{
    $user = $this->createUsrUser();
    $this->itemService->addItem($user->getId(), 'item_1', 10);
    $this->saveAll();  // ✅ saveAll()を実行

    $items = UsrItem::where('usr_user_id', $user->getId())->get();
    $this->assertCount(1, $items);  // 成功
}
```

**注意**: UseCase, Controller テストでは`saveAll()`は実行不要（自動で保存される）。

### エラー3: fixTime()の使用漏れ

**エラーメッセージ**:
```
Failed asserting that two DateTime objects are equal.
```

**原因**: 時間が固定されていないため、テスト実行時刻によって結果が変わる

**修正前**:
```php
public function test_example()
{
    $user = $this->createUsrUser();
    $this->service->updateLastLogin($user->getId());

    $user->refresh();
    $this->assertEquals('2024-01-01 00:00:00', $user->last_login_at);  // エラー: 時間が一致しない
}
```

**修正後**:
```php
public function test_example()
{
    $now = $this->fixTime('2024-01-01 00:00:00');  // ✅ 時間を固定

    $user = $this->createUsrUser();
    $this->service->updateLastLogin($user->getId());

    $user->refresh();
    $this->assertEquals($now, $user->last_login_at);  // 成功
}
```

**重要**: `setTestNow()`は使わず、`fixTime()`を使用する。

### エラー4: @testアノテーションの使用

**エラーメッセージ**:
```
No tests executed
```

**原因**: `@test`アノテーションは古いバージョンの記述方法で使用禁止

**修正前**:
```php
/**
 * @test
 */
public function example()  // ❌ メソッド名に'test_'がない
{
    // ...
}
```

**修正後**:
```php
public function test_example()  // ✅ 'test_'で始まる
{
    // ...
}
```

### エラー5: ファクトリーの使用ミス

**エラーメッセージ**:
```
Call to undefined method create()
```

**修正前**:
```php
$user = UsrUser::create([  // ❌ ファクトリーを使うべき
    'id' => 'user_123',
]);
```

**修正後**:
```php
$user = UsrUser::factory()->create([  // ✅ ファクトリーを使用
    'id' => 'user_123',
]);
```

## テストデバッグ方法

### 方法1: dd()で変数をダンプ

```php
public function test_example()
{
    $result = $this->service->process();
    dd($result);  // ここで変数の内容を確認
    $this->assertEquals(10, $result);
}
```

### 方法2: dump()で変数を表示（テスト継続）

```php
public function test_example()
{
    $result = $this->service->process();
    dump($result);  // 変数を表示するがテストは継続
    $this->assertEquals(10, $result);
}
```

### 方法3: ログに出力

```php
public function test_example()
{
    $result = $this->service->process();
    \Log::info('Test result', ['result' => $result]);
    $this->assertEquals(10, $result);
}
```

ログ確認:
```bash
./tools/bin/sail-wp logs -f
```

### 方法4: 特定のテストのみ実行

```bash
# テストクラスを指定
./tools/bin/sail-wp test --filter ExampleServiceTest

# テストメソッドを指定
./tools/bin/sail-wp test --filter test_example_method

# ファイルパスを指定
./tools/bin/sail-wp test tests/Feature/Domain/Example/ExampleServiceTest.php
```

### 方法5: --stopOnFailureでエラー時に停止

```bash
./tools/bin/sail-wp test --stop-on-failure
```

## カバレッジについて

### カバレッジの確認

```bash
./tools/bin/sail-wp test --coverage | grep -v '100.0 %'
```

**出力例**:
```
  App\Domain\Example\Services\ExampleService ......... 85.7 %
    45-48
```

**意味**: ExampleServiceのカバレッジが85.7%で、45-48行目がテストされていない。

### カバレッジ100%未満の対応

#### 対応不要なケース

- **ゲッターのみのクラス**
- **例外クラス**
- **定数クラス**
- **テスト実行が困難なコード**（外部API呼び出し等）

#### 対応が必要なケース

- **重要なビジネスロジック**
- **条件分岐が多いコード**
- **エッジケース**

### カバレッジを上げる方法

#### 方法1: エッジケースのテストを追加

```php
// 正常系のテスト
public function test_process_success()
{
    $result = $this->service->process('valid_input');
    $this->assertEquals('success', $result);
}

// 異常系のテストを追加
public function test_process_with_invalid_input()
{
    $this->expectException(\InvalidArgumentException::class);
    $this->service->process('invalid_input');
}

// nullの場合のテストを追加
public function test_process_with_null()
{
    $result = $this->service->process(null);
    $this->assertNull($result);
}
```

#### 方法2: 条件分岐をテスト

```php
// if文の両方のパスをテスト
public function test_process_when_condition_true()
{
    // 条件がtrueの場合
    $result = $this->service->process(true);
    $this->assertEquals('A', $result);
}

public function test_process_when_condition_false()
{
    // 条件がfalseの場合
    $result = $this->service->process(false);
    $this->assertEquals('B', $result);
}
```

## トラブルシューティング

### 問題1: 大量のテストが失敗

**対処法**:
1. まず1つのテストファイルに絞って修正
2. setUp()やファクトリーの問題を確認
3. 共通の原因を特定（時間、データベース等）

### 問題2: テストがランダムに失敗する

**原因**: データの初期化不足、時間依存のロジック

**対処法**:
- `fixTime()`で時間を固定
- `RefreshDatabase`で毎回DBをリセット
- テスト間で共有する状態を排除

### 問題3: テストが遅い

**対処法**:
- 不要なテストデータ作成を削減
- モックを活用して外部依存を排除
- 並列実行を検討（`--parallel`オプション）

### 問題4: メモリ不足

**エラー**:
```
Allowed memory size exhausted
```

**対処法**:
```bash
# メモリ制限を増やす
php -d memory_limit=2G artisan test
```

## チェックリスト

- [ ] `sail test --coverage`を実行して全テストがPASS
- [ ] RefreshDatabaseを重複インポートしていない
- [ ] saveAll()が必要な箇所で実行されている
- [ ] fixTime()で時間を固定している
- [ ] @testアノテーションを使用していない
- [ ] ファクトリーを適切に使用している
- [ ] モックの期待設定が正確
- [ ] カバレッジ100%未満の箇所を確認し、必要に応じてテスト追加
- [ ] コミットメッセージが適切に記載されている
