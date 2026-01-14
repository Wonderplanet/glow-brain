# PHPStan ガイド: 静的解析エラーの修正

## 目次

1. [概要](#概要)
2. [基本的な使い方](#基本的な使い方)
3. [エラータイプ別修正方法](#エラータイプ別修正方法)
4. [よくあるエラーパターン](#よくあるエラーパターン)
5. [Laravel固有のエラー](#laravel固有のエラー)
6. [トラブルシューティング](#トラブルシューティング)

## 概要

### PHPStanとは

PHPStan (PHP Static Analysis Tool) は、コードを実行せずに型エラーやバグを検出するツール。

**利点**:
- 実行時エラーを事前に防止
- 型の不整合を検出
- null参照エラーを防止
- 未定義のプロパティ・メソッドを検出

### 設定ファイル

**場所**: `api/phpstan.neon`

**設定内容**:
```neon
includes:
    - ./vendor/nunomaduro/larastan/extension.neon

parameters:
    paths:
        - app/
        - ../local/lib/laravel-wp-billing/src
        - ../local/lib/laravel-wp-currency/src
    excludePaths:
        - app/Console/Commands/*
        - app/Http/Resources/Api/*
    level: 6
    checkGenericClassInNonGenericObjectType: false
```

**解析レベル**: Level 6 (0-9の10段階、6は中上級レベル)

### Larastan

Larastan は、Laravel向けのPHPStan拡張。Laravelの動的な機能を理解して解析する。

## 基本的な使い方

### 全ファイルを解析

```bash
# 全対象ファイルを解析
./tools/bin/sail-wp phpstan

# 実行されるコマンド（内部）
# docker-compose exec php vendor/bin/phpstan analyse --memory-limit=-1
```

### 特定ファイルを解析

```bash
./tools/bin/sail-wp phpstan analyze api/app/Domain/Example/Services/ExampleService.php
```

### エラー表示形式

```
------ ---------------------------------------------------------------------------
Line   api/app/Domain/Example/Services/ExampleService.php
------ ---------------------------------------------------------------------------
 45     Parameter #1 $userId of method getUserData() expects string, int given.
 67     Method getItems() should return array<Item> but returns array.
 89     Property UsrUser::$name (string) does not accept null.
------ ---------------------------------------------------------------------------

[ERROR] Found 3 errors
```

### 成功時の出力

```
[OK] No errors
```

## エラータイプ別修正方法

### 1. Parameter type mismatch (引数の型不一致)

#### エラーメッセージ

```
Parameter #1 $userId of method getUserData() expects string, int given.
```

#### 原因

メソッドが期待する型と、渡される引数の型が異なる。

#### 修正前

```php
class ExampleService
{
    public function getUserData(string $userId): array
    {
        // ...
    }

    public function process(): void
    {
        $id = 12345;  // int
        $this->getUserData($id);  // エラー: string を期待しているがintが渡されている
    }
}
```

#### 修正方法1: 型キャスト

```php
public function process(): void
{
    $id = 12345;
    $this->getUserData((string) $id);  // intをstringに変換
}
```

#### 修正方法2: 引数の型を変更

```php
public function getUserData(int|string $userId): array
{
    $userId = (string) $userId;  // 内部でstringに統一
    // ...
}
```

### 2. Return type mismatch (戻り値の型不一致)

#### エラーメッセージ

```
Method getItems() should return array<Item> but returns array.
```

#### 原因

メソッドの戻り値の型アノテーションが不足または不正確。

#### 修正前

```php
/**
 * @return array
 */
public function getItems(): array
{
    return UsrItem::where('usr_user_id', $userId)->get();
}
```

#### 修正後

```php
/**
 * @return array<UsrItem>
 */
public function getItems(): array
{
    return UsrItem::where('usr_user_id', $userId)->get()->all();
}
```

または、より厳密に：

```php
/**
 * @return \Illuminate\Support\Collection<int, UsrItem>
 */
public function getItems(): Collection
{
    return UsrItem::where('usr_user_id', $userId)->get();
}
```

### 3. Nullable type (null許容型)

#### エラーメッセージ

```
Property UsrUser::$name (string) does not accept null.
```

#### 原因

nullを許容しない型に、nullが代入される可能性がある。

#### 修正前

```php
class ExampleService
{
    public function getUserName(string $userId): string
    {
        $user = UsrUser::find($userId);  // nullの可能性
        return $user->name;  // エラー: $userがnullの場合エラー
    }
}
```

#### 修正方法1: nullチェックを追加

```php
public function getUserName(string $userId): string
{
    $user = UsrUser::find($userId);
    if ($user === null) {
        throw new \RuntimeException('User not found');
    }
    return $user->name;
}
```

#### 修正方法2: 戻り値をnull許容に

```php
public function getUserName(string $userId): ?string
{
    $user = UsrUser::find($userId);
    return $user?->name;
}
```

#### 修正方法3: firstOrFail()を使用

```php
public function getUserName(string $userId): string
{
    $user = UsrUser::where('id', $userId)->firstOrFail();
    return $user->name;
}
```

### 4. Property not found (プロパティが見つからない)

#### エラーメッセージ

```
Access to an undefined property UsrUser::$invalidProperty.
```

#### 原因

存在しないプロパティにアクセスしている。

#### 修正前

```php
$user = UsrUser::find($userId);
echo $user->invalidProperty;  // エラー: 存在しないプロパティ
```

#### 修正方法1: 正しいプロパティ名を使用

```php
$user = UsrUser::find($userId);
echo $user->name;  // 正しいプロパティ名
```

#### 修正方法2: 動的プロパティの場合はアノテーション追加

```php
/**
 * @property string $dynamicProperty
 */
class UsrUser extends Model
{
    // ...
}
```

### 5. Array shape (配列の型定義)

#### エラーメッセージ

```
Method getConfig() should return array{key1: string, key2: int} but returns array.
```

#### 原因

配列の構造（キーと値の型）が明示されていない。

#### 修正前

```php
/**
 * @return array
 */
public function getConfig(): array
{
    return [
        'key1' => 'value',
        'key2' => 100,
    ];
}
```

#### 修正後

```php
/**
 * @return array{key1: string, key2: int}
 */
public function getConfig(): array
{
    return [
        'key1' => 'value',
        'key2' => 100,
    ];
}
```

### 6. Generic type (ジェネリック型)

#### エラーメッセージ

```
Method getUsers() return type has no value type specified in iterable type array.
```

#### 原因

配列やコレクションの要素の型が指定されていない。

#### 修正前

```php
/**
 * @return array
 */
public function getUsers(): array
{
    return UsrUser::all()->toArray();
}
```

#### 修正後

```php
/**
 * @return array<int, UsrUser>
 */
public function getUsers(): array
{
    return UsrUser::all()->all();
}
```

または：

```php
/**
 * @return array<string, mixed>
 */
public function getUsers(): array
{
    return UsrUser::all()->toArray();
}
```

## よくあるエラーパターン

### パターン1: Eloquentモデルのfind()

#### エラー

```
Cannot call method getName() on App\Domain\Example\Models\UsrExample|null.
```

#### 修正前

```php
public function process(string $userId): string
{
    $user = UsrExample::find($userId);
    return $user->getName();  // エラー: $userがnullの可能性
}
```

#### 修正後

```php
public function process(string $userId): string
{
    $user = UsrExample::find($userId);
    if ($user === null) {
        throw new \RuntimeException('User not found');
    }
    return $user->getName();
}
```

または：

```php
public function process(string $userId): string
{
    $user = UsrExample::findOrFail($userId);
    return $user->getName();
}
```

### パターン2: コレクションのfirst()

#### エラー

```
Cannot call method toArray() on UsrItem|null.
```

#### 修正前

```php
$item = UsrItem::where('usr_user_id', $userId)->first();
return $item->toArray();  // エラー: $itemがnullの可能性
```

#### 修正後

```php
$item = UsrItem::where('usr_user_id', $userId)->first();
if ($item === null) {
    return [];
}
return $item->toArray();
```

または：

```php
$item = UsrItem::where('usr_user_id', $userId)->firstOrFail();
return $item->toArray();
```

### パターン3: リクエストの入力値

#### エラー

```
Parameter #1 $userId of method process() expects string, mixed given.
```

#### 修正前

```php
public function handle(Request $request): void
{
    $userId = $request->input('user_id');
    $this->process($userId);  // エラー: mixedをstringに渡している
}

private function process(string $userId): void
{
    // ...
}
```

#### 修正後

```php
public function handle(Request $request): void
{
    $userId = $request->input('user_id');
    assert(is_string($userId));
    $this->process($userId);
}

private function process(string $userId): void
{
    // ...
}
```

または：

```php
public function handle(Request $request): void
{
    /** @var string $userId */
    $userId = $request->input('user_id');
    $this->process($userId);
}

private function process(string $userId): void
{
    // ...
}
```

### パターン4: Configのget()

#### エラー

```
Parameter #1 $value of method strlen() expects string, mixed given.
```

#### 修正前

```php
$apiKey = config('services.api.key');
$length = strlen($apiKey);  // エラー: mixedをstringに渡している
```

#### 修正後

```php
$apiKey = config('services.api.key');
assert(is_string($apiKey));
$length = strlen($apiKey);
```

または、型アノテーション：

```php
/** @var string $apiKey */
$apiKey = config('services.api.key');
$length = strlen($apiKey);
```

### パターン5: 配列のアクセス

#### エラー

```
Offset 'key' does not exist on array.
```

#### 修正前

```php
$data = ['key1' => 'value1'];
$value = $data['key2'];  // エラー: key2は存在しない
```

#### 修正後

```php
$data = ['key1' => 'value1'];
$value = $data['key2'] ?? 'default';
```

または：

```php
$data = ['key1' => 'value1'];
if (array_key_exists('key2', $data)) {
    $value = $data['key2'];
} else {
    $value = 'default';
}
```

## Laravel固有のエラー

### パターン1: Eloquent動的プロパティ

#### エラー

```
Access to an undefined property UsrUser::$items.
```

#### 原因

リレーションがPHPStanに認識されていない。

#### 修正方法: @propertyアノテーションを追加

```php
/**
 * @property-read \Illuminate\Database\Eloquent\Collection<int, UsrItem> $items
 */
class UsrUser extends Model
{
    public function items(): HasMany
    {
        return $this->hasMany(UsrItem::class);
    }
}
```

### パターン2: ファサード

#### エラー

```
Call to an undefined static method Cache::get().
```

#### 原因

Larastanがファサードを認識できていない（通常は発生しない）。

#### 修正方法: use文を確認

```php
use Illuminate\Support\Facades\Cache;

$value = Cache::get('key');
```

### パターン3: マクロメソッド

#### エラー

```
Call to an undefined method Illuminate\Support\Collection::macro().
```

#### 修正方法: @methodアノテーションを追加

マクロを登録する場所（ServiceProviderなど）でアノテーション追加：

```php
/**
 * @method static void customMethod(string $param)
 */
```

## トラブルシューティング

### 問題1: エラーが多すぎる

**対処法**:
1. ファイル単位で段階的に修正
2. エラータイプごとに分類して対応
3. よくあるパターンから優先的に修正

### 問題2: false positiveなエラー

**原因**: PHPStanが正しく推論できないケース

**対処法1**: @varや@paramでアノテーション追加

```php
/** @var string $value */
$value = $complexFunction();
```

**対処法2**: assert()で型を保証

```php
$value = $complexFunction();
assert(is_string($value));
```

**対処法3**: @phpstan-ignoreで無視（最終手段）

```php
// @phpstan-ignore-next-line
$value = $complexFunction();
```

### 問題3: メモリ不足エラー

**エラー**:
```
Allowed memory size exhausted
```

**対処法**: メモリ制限を増やす（通常は`--memory-limit=-1`で対応済み）

```bash
./tools/bin/sail-wp phpstan analyze --memory-limit=2G
```

### 問題4: Laravelの動的機能が認識されない

**対処法**: Larastanの設定を確認

`phpstan.neon`に以下が含まれていることを確認：

```neon
includes:
    - ./vendor/nunomaduro/larastan/extension.neon
```

## チェックリスト

- [ ] phpstanを実行してエラー0を確認
- [ ] すべてのメソッドに@param、@returnアノテーションがある
- [ ] null許容型が適切に指定されている
- [ ] 配列型にジェネリック型が指定されている
- [ ] Eloquentのfind()後にnullチェックがある
- [ ] 型キャストやassert()で型を明示している
- [ ] Laravelの動的機能に@propertyや@methodアノテーションがある
- [ ] false positiveには適切なコメントが記載されている
- [ ] コミットメッセージが適切に記載されている
