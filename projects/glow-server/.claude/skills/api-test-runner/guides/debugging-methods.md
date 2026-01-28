# デバッグ方法ガイド

## デバッグの基本

### dump() vs dd()

| 関数 | 動作 | 使用場面 |
|------|------|---------|
| `dump($var)` | 変数を出力して処理継続 | 複数の変数を確認したい |
| `dd($var)` | 変数を出力して処理停止 | 特定の時点で処理を止めたい |

## デバッグパターン別ガイド

### パターン1: アサーション失敗のデバッグ

#### エラー例

```
Failed asserting that 0 matches expected 5.

at tests/Feature/Domain/Item/ItemServiceTest.php:45
```

#### デバッグ手順

```php
public function test_apply_アイテム使用()
{
    $user = $this->createUsrUser();
    $this->itemService->addItem($user->getId(), 'item_1', 10);
    $this->saveAll();

    // デバッグ: DBの状態を確認
    $items = UsrItem::where('usr_user_id', $user->getId())->get();
    dump('Items count:', $items->count());
    dump('Items:', $items->toArray());

    $this->assertCount(1, $items);
}
```

**確認ポイント**:
- 期待する件数が実際に保存されているか
- データの内容が正しいか
- 外部キー制約で保存に失敗していないか

### パターン2: 例外エラーのデバッグ

#### エラー例

```
RuntimeException: User not found

at app/Domain/User/Services/UserService.php:23
```

#### デバッグ手順

```php
public function test_getUser()
{
    // デバッグ: ユーザーが作成されているか確認
    $user = $this->createUsrUser(['id' => 'user_123']);
    dump('Created user:', $user->toArray());

    // デバッグ: DBに保存されているか確認
    $dbUser = UsrUser::find('user_123');
    dump('DB user:', $dbUser?->toArray());

    try {
        $result = $this->userService->getUser('user_123');
        dump('Result:', $result);
    } catch (\Exception $e) {
        dump('Exception:', $e->getMessage());
        dump('Stack trace:', $e->getTraceAsString());
        throw $e;
    }
}
```

**確認ポイント**:
- データが作成されているか
- 正しいIDで検索しているか
- 例外の発生箇所とメッセージ

### パターン3: データベース状態のデバッグ

#### DB全体を確認

```php
public function test_example()
{
    // 全UsrItemを確認
    dump('All UsrItems:', UsrItem::all()->toArray());

    // 特定ユーザーのアイテムを確認
    $items = UsrItem::where('usr_user_id', 'user_123')->get();
    dump('User items:', $items->toArray());

    // アイテムの個数確認
    dump('Item count:', UsrItem::count());
}
```

#### クエリログを確認

```php
public function test_example()
{
    // クエリログ有効化
    \DB::enableQueryLog();

    $this->itemService->addItem($userId, 'item_1', 10);
    $this->saveAll();

    // 実行されたクエリを確認
    dump('Queries:', \DB::getQueryLog());
}
```

**出力例**:
```php
[
    [
        'query' => 'insert into `usr_items` (`usr_user_id`, `mst_item_id`, `amount`) values (?, ?, ?)',
        'bindings' => ['user_123', 'item_1', 10],
        'time' => 1.23,
    ],
]
```

### パターン4: モック呼び出しのデバッグ

#### エラー例

```
Mockery\Exception\NoMatchingExpectationException:
No matching handler found for ItemService::apply()
```

#### デバッグ手順

```php
public function test_useCase()
{
    // デバッグ: モックの呼び出しを確認
    $this->mock(ItemService::class, function (MockInterface $mock) {
        $mock->shouldReceive('apply')
            ->andReturnUsing(function (...$args) {
                dump('apply called with:', $args);
                return true;
            });
    });

    $this->useCase->exec($userId, $itemId);
}
```

**確認ポイント**:
- モックが呼ばれているか
- 引数が期待通りか
- 呼び出し回数は正しいか

### パターン5: 時間関連のデバッグ

```php
public function test_時間依存のロジック()
{
    // 時間を固定
    $now = $this->fixTime('2024-01-01 00:00:00');
    dump('Fixed time:', $now->toDateTimeString());

    // サービス実行
    $this->service->updateLastLogin($userId);

    // DBの時間を確認
    $user = UsrUser::find($userId);
    dump('Last login at:', $user->last_login_at->toDateTimeString());
    dump('Expected:', $now->toDateTimeString());

    $this->assertEquals($now, $user->last_login_at);
}
```

## 高度なデバッグテクニック

### テクニック1: 条件分岐のデバッグ

```php
public function test_条件分岐()
{
    $value = $this->service->calculate($input);

    // 条件分岐を追跡
    if ($value > 10) {
        dump('Branch A: value > 10', $value);
    } elseif ($value > 5) {
        dump('Branch B: value > 5', $value);
    } else {
        dump('Branch C: value <= 5', $value);
    }

    $this->assertEquals(expected, $value);
}
```

### テクニック2: ループ処理のデバッグ

```php
public function test_ループ処理()
{
    $items = $this->service->getItems($userId);

    dump('Total items:', count($items));

    foreach ($items as $index => $item) {
        dump("Item #{$index}:", $item->toArray());
    }

    $this->assertCount(5, $items);
}
```

### テクニック3: 実装コードにデバッグ出力を追加

**テストコード**:
```php
public function test_example()
{
    $result = $this->service->process($input);
    dd($result);  // 実装コードのdump()出力も表示される
}
```

**実装コード（一時的に追加）**:
```php
public function process($input): int
{
    dump('Input:', $input);

    $result = $this->calculate($input);
    dump('Calculated result:', $result);

    return $result;
}
```

### テクニック4: ログ出力とログファイル確認

#### 方法A: コード内でログ出力

```php
public function test_example()
{
    // ログ出力
    \Log::info('Test started', ['userId' => $userId]);

    $result = $this->service->process($userId);

    \Log::info('Test result', ['result' => $result]);
}
```

**ログレベル**:
- `\Log::debug()` - デバッグ情報
- `\Log::info()` - 一般情報
- `\Log::warning()` - 警告
- `\Log::error()` - エラー
- `\Log::critical()` - 重大なエラー

#### 方法B: laravel.logファイルを確認

**ログファイルの場所**:
```
api/storage/logs/laravel.log
```

**ログ確認コマンド**:

```bash
# 最新の100行を表示
tail -100 api/storage/logs/laravel.log

# リアルタイムで監視
tail -f api/storage/logs/laravel.log

# エラーのみ抽出
grep "ERROR" api/storage/logs/laravel.log

# 特定の文字列を検索
grep "User not found" api/storage/logs/laravel.log

# 日時でフィルタ
grep "2024-01-01" api/storage/logs/laravel.log
```

#### 方法C: Dockerコンテナのログを確認

```bash
# PHPコンテナのログをリアルタイムで確認
sail logs -f php

# 最新の100行のみ表示
sail logs --tail=100 php

# 全コンテナのログ
sail logs -f
```

#### ログの読み方

**ログ出力例**:
```
[2024-01-01 12:34:56] testing.ERROR: User not found {"userId":"user_123"}
[2024-01-01 12:34:57] testing.INFO: Test started {"userId":"user_456"}
```

**フォーマット**:
```
[日時] 環境.レベル: メッセージ {コンテキスト}
```

- **日時**: ログが出力された時刻
- **環境**: `testing`（テスト実行時）、`local`（開発時）等
- **レベル**: DEBUG, INFO, WARNING, ERROR, CRITICAL
- **メッセージ**: ログの内容
- **コンテキスト**: 追加情報（配列）

#### ログファイルのクリア

**テスト前にログをクリア**:
```bash
# ログファイルを削除
rm api/storage/logs/laravel.log

# または空にする
> api/storage/logs/laravel.log

# テスト実行
sail phpunit

# ログ確認
tail -f api/storage/logs/laravel.log
```

#### 実装コード内のログ確認

**実装コード（一時的に追加）**:
```php
public function process($userId): int
{
    \Log::info('Process started', ['userId' => $userId]);

    $user = UsrUser::find($userId);
    \Log::info('User found', ['user' => $user?->toArray()]);

    if ($user === null) {
        \Log::error('User not found', ['userId' => $userId]);
        throw new \RuntimeException('User not found');
    }

    $result = $this->calculate($user);
    \Log::info('Calculation result', ['result' => $result]);

    return $result;
}
```

**テスト実行後にログ確認**:
```bash
tail -50 api/storage/logs/laravel.log
```

## デバッグフロー

### ステップ1: エラーメッセージを読む

```
Failed asserting that 0 matches expected 5.
  at tests/Feature/Domain/Item/ItemServiceTest.php:45
```

**確認すること**:
- 期待値: 5
- 実際の値: 0
- 失敗箇所: ItemServiceTest.php:45行目

### ステップ2: dump()で変数確認

```php
public function test_apply_アイテム使用()
{
    $user = $this->createUsrUser();
    dump('User:', $user->toArray());

    $this->itemService->addItem($user->getId(), 'item_1', 10);
    $this->saveAll();

    $items = UsrItem::where('usr_user_id', $user->getId())->get();
    dump('Items:', $items->toArray());

    $this->assertCount(1, $items);  // ここで失敗
}
```

### ステップ3: ログファイルを確認（必要に応じて）

```bash
# ログをクリア
> api/storage/logs/laravel.log

# テスト実行
sail phpunit --filter test_apply_アイテム使用

# ログ確認
tail -100 api/storage/logs/laravel.log
```

**確認ポイント**:
- エラーログが出力されていないか
- 実装コード内のログ出力
- 例外のスタックトレース

### ステップ4: 原因を特定

dump()やログの出力から原因を特定:
- `saveAll()`が実行されていない
- 外部キー制約で保存失敗
- 検索条件が間違っている
- 実装コードで例外が発生している

### ステップ5: 修正

特定した原因に基づいて修正。

### ステップ6: デバッグコードを削除

修正完了後、以下を削除:
- デバッグ用のdump()/dd()
- 一時的に追加したログ出力
- try-catch（デバッグ用）

## デバッグ時のベストプラクティス

### ✅ 推奨

```php
// 1. 変数の内容を確認
dump('User ID:', $userId);
dump('Items:', $items->toArray());

// 2. 条件分岐を確認
dump('Condition check:', $value > 10);

// 3. 複数変数を一度に確認
dump([
    'userId' => $userId,
    'itemId' => $itemId,
    'amount' => $amount,
]);

// 4. 処理を停止して確認
dd($result);
```

### ❌ 避けるべき

```php
// 1. var_dump()やprint_r()の使用（dump()を使う）
var_dump($value);  // ❌

// 2. echoやprintの使用
echo $value;  // ❌

// 3. dump()を大量に残したままコミット
dump('Debug 1');
dump('Debug 2');
// ...
dump('Debug 100');  // ❌

// 4. 本番コードにdump()を残す
public function process() {
    dump('Processing...');  // ❌ テストコードのみに使用
    return $result;
}
```

## よくある問題と解決策

### 問題1: dump()の出力が表示されない

**原因**: テストが例外で失敗している

**解決策**: try-catchで例外を捕捉

```php
try {
    $result = $this->service->process();
    dump('Result:', $result);
} catch (\Exception $e) {
    dump('Exception:', $e->getMessage());
    throw $e;
}
```

### 問題2: DBの内容が期待と違う

**原因**: saveAll()の実行漏れ、トランザクションロールバック

**解決策**:
```php
// saveAll()を実行
$this->saveAll();

// DBの内容を確認
dump('DB contents:', UsrItem::all()->toArray());
```

### 問題3: モックが呼ばれない

**原因**: DIコンテナのバインディング問題

**解決策**:
```php
// モックのバインディングを確認
$this->mock(ItemService::class, function (MockInterface $mock) {
    $mock->shouldReceive('apply')
        ->andReturnUsing(function (...$args) {
            dump('Mock called with:', $args);
            return true;
        });
});

// UseCaseを再取得（モックが適用される）
$useCase = app()->make(ItemUseCase::class);
$useCase->exec($userId);
```

## チェックリスト

### デバッグ開始時
- [ ] エラーメッセージを正確に読む
- [ ] 失敗箇所のファイル名・行番号を確認
- [ ] 期待値と実際の値を確認
- [ ] 必要に応じてログファイルをクリア

### dump()使用時
- [ ] 変数名や説明を添える（`dump('User:', $user)`）
- [ ] 配列やオブジェクトは`toArray()`で見やすく
- [ ] 処理を止めたい場合は`dd()`を使用

### ログ確認時
- [ ] laravel.logファイルを確認（`tail -f api/storage/logs/laravel.log`）
- [ ] エラーログが出力されていないか確認
- [ ] 実装コード内のログ出力を確認
- [ ] 必要に応じてDockerコンテナのログも確認（`sail logs -f php`）

### デバッグ完了時
- [ ] 全てのdump()/dd()を削除
- [ ] 実装コードのデバッグ出力を削除
- [ ] 一時的に追加した\Log::出力を削除
- [ ] テストが成功することを確認
- [ ] 不要なコメントを削除

### コミット前
- [ ] デバッグ用コードが残っていないか確認
- [ ] ログ出力が残っていないか確認
- [ ] phpcsでコーディング規約をチェック
- [ ] laravel.logに不要なデバッグログが残っていないか確認
