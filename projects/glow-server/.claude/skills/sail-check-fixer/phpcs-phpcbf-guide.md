# PHPCS/PHPCBF ガイド: コーディング規約チェック

## 目次

1. [概要](#概要)
2. [phpcbf: 自動修正](#phpcbf-自動修正)
3. [phpcs: 手動修正](#phpcs-手動修正)
4. [適用ルール一覧](#適用ルール一覧)
5. [よくあるエラーと修正方法](#よくあるエラーと修正方法)
6. [トラブルシューティング](#トラブルシューティング)

## 概要

### PHPCSとは

PHP_CodeSniffer (PHPCS) は、PHPコードがコーディング規約に準拠しているかをチェックするツール。

- **phpcs**: 違反を検出（チェックのみ）
- **phpcbf**: 違反を自動修正（PHP Code Beautifier and Fixer）

### 設定ファイル

**場所**: `api/phpcs.xml`

**対象ディレクトリ**:
- `api/app/Http/Controllers`
- `api/app/Domain`
- `local/lib/laravel-wp-billing/src`
- `local/lib/laravel-wp-currency/src`

**適用規約**:
- PSR-12 (PHP Standard Recommendation 12)
- Slevomat Coding Standard (拡張ルール)

## phpcbf: 自動修正

### 基本的な使い方

```bash
# 全対象ファイルを自動修正
./tools/bin/sail-wp phpcbf

# 実行されるコマンド（内部）
# docker-compose exec php vendor/bin/phpcbf
```

### 自動修正される内容

#### 1. インデントと空白

**修正前**:
```php
<?php
class Example {
  public function test() {
      return true;
  }
}
```

**修正後**:
```php
<?php
class Example
{
    public function test()
    {
        return true;
    }
}
```

#### 2. declare(strict_types=1)の追加

**修正前**:
```php
<?php

namespace App\Domain\Example;

class ExampleService
{
```

**修正後**:
```php
<?php

declare(strict_types=1);

namespace App\Domain\Example;

class ExampleService
{
```

#### 3. use文のアルファベット順並び替え

**修正前**:
```php
use App\Domain\Common\Entities\CurrentUser;
use App\Domain\Example\Models\UsrExample;
use App\Domain\Common\Constants\Database;
```

**修正後**:
```php
use App\Domain\Common\Constants\Database;
use App\Domain\Common\Entities\CurrentUser;
use App\Domain\Example\Models\UsrExample;
```

#### 4. 配列の最後のカンマ追加

**修正前**:
```php
$array = [
    'key1' => 'value1',
    'key2' => 'value2'
];
```

**修正後**:
```php
$array = [
    'key1' => 'value1',
    'key2' => 'value2',
];
```

#### 5. 厳密な比較への変更

**修正前**:
```php
if ($value == null) {
    // ...
}
```

**修正後**:
```php
if ($value === null) {
    // ...
}
```

### 実行後の確認

```bash
# 差分を確認
git diff

# 修正内容が適切であればコミット
git add .
git commit -m "fix: コーディング規約違反を自動修正 (phpcbf)"
```

### 出力例

**成功時**:
```
PHPCBF RESULT SUMMARY
----------------------------------------------------------------------
A TOTAL OF 45 ERRORS WERE FIXED IN 12 FILES
----------------------------------------------------------------------
```

**修正不要時**:
```
PHPCBF RESULT SUMMARY
----------------------------------------------------------------------
A TOTAL OF 0 ERRORS WERE FIXED IN 0 FILES
----------------------------------------------------------------------
```

## phpcs: 手動修正

### 基本的な使い方

```bash
# 全対象ファイルをチェック
./tools/bin/sail-wp phpcs

# 実行されるコマンド（内部）
# docker-compose exec php vendor/bin/phpcs
```

### エラー表示形式

```
FILE: /var/www/html/api/app/Domain/Example/Services/ExampleService.php
----------------------------------------------------------------------
FOUND 3 ERRORS AND 1 WARNING AFFECTING 3 LINES
----------------------------------------------------------------------
 15 | ERROR   | [x] Expected 1 space after USE keyword; 0 found
 45 | ERROR   | [ ] Missing parameter type
 67 | WARNING | [ ] Line exceeds 120 characters; contains 145 characters
 89 | ERROR   | [ ] Method name should be in camelCase
----------------------------------------------------------------------
```

**記号の意味**:
- `[x]` : phpcbfで自動修正可能（通常は出ないはず）
- `[ ]` : 手動修正が必要

### 成功時の出力

```
----------------------------------------------------------------------
FOUND 0 ERRORS AND 0 WARNINGS AFFECTING 0 LINES
----------------------------------------------------------------------
```

## 適用ルール一覧

### PSR-12準拠ルール

#### 1. ファイル構造

- ファイルの先頭は`<?php`
- `declare(strict_types=1);`が必須
- namespaceの後に1行空行
- useブロックの後に1行空行

#### 2. インデント

- スペース4つ
- タブは使用禁止

#### 3. 行の長さ

- 推奨: 120文字以内
- 上限: 制限なし（警告のみ）

#### 4. クラス・メソッド

- 開き括弧は次の行
- 閉じ括弧は単独の行

```php
class Example
{
    public function test()
    {
        // ...
    }
}
```

### Slevomat Coding Standard拡張ルール

#### 1. TrailingArrayComma

複数行のarrayの最後にカンマを付ける。

✅ **正しい例**:
```php
$array = [
    'key1' => 'value1',
    'key2' => 'value2',
];
```

❌ **間違った例**:
```php
$array = [
    'key1' => 'value1',
    'key2' => 'value2'  // カンマがない
];
```

**理由**: 差分が見やすくなり、新しい要素を追加する際にミスが減る。

#### 2. DisallowEmpty

empty関数は使わない。

✅ **正しい例**:
```php
if ($array === []) {
    // ...
}

if ($value === null) {
    // ...
}

if ($string === '') {
    // ...
}
```

❌ **間違った例**:
```php
if (empty($array)) {
    // ...
}
```

**理由**: 明示的な比較の方が意図が明確になる。

#### 3. DisallowYodaComparison

ヨーダ記法は使わない。

✅ **正しい例**:
```php
if ($value === 10) {
    // ...
}
```

❌ **間違った例**:
```php
if (10 === $value) {
    // ...
}
```

**理由**: 可読性が低下するため。

#### 4. AlphabeticallySortedUses

名前空間のインポートはアルファベット順に指定する。

✅ **正しい例**:
```php
use App\Domain\Common\Constants\Database;
use App\Domain\Common\Entities\CurrentUser;
use App\Domain\Example\Models\UsrExample;
```

❌ **間違った例**:
```php
use App\Domain\Common\Entities\CurrentUser;
use App\Domain\Example\Models\UsrExample;
use App\Domain\Common\Constants\Database;
```

#### 5. UnusedUses

未使用の名前空間のインポートは削除する。

✅ **正しい例**:
```php
use App\Domain\Common\Entities\CurrentUser;

class ExampleService
{
    public function test(CurrentUser $user)
    {
        // CurrentUserを使用している
    }
}
```

❌ **間違った例**:
```php
use App\Domain\Common\Entities\CurrentUser;
use App\Domain\Example\Models\UsrExample;  // 使用していない

class ExampleService
{
    public function test(CurrentUser $user)
    {
        // UsrExampleを使用していない
    }
}
```

**注意**: PHPDocで使用している場合は削除されない。

```php
use App\Domain\Example\Models\UsrExample;

class ExampleService
{
    /**
     * @return UsrExample  // PHPDocで使用
     */
    public function getExample()
    {
        // ...
    }
}
```

#### 6. DisallowEqualOperators

緩い比較「==」ではなく厳密な比較「===」を使用する。

✅ **正しい例**:
```php
if ($value === 10) {
    // ...
}

if ($value !== null) {
    // ...
}
```

❌ **間違った例**:
```php
if ($value == 10) {
    // ...
}

if ($value != null) {
    // ...
}
```

**理由**: 型の暗黙的な変換を防ぎ、予期しないバグを避ける。

#### 7. DeclareStrictTypes

コードの先頭に「declare(strict_types=1);」を付ける。

✅ **正しい例**:
```php
<?php

declare(strict_types=1);

namespace App\Domain\Example;

class ExampleService
{
```

❌ **間違った例**:
```php
<?php

namespace App\Domain\Example;

class ExampleService
{
```

**フォーマット**:
- namespace宣言の前に配置
- `declare(strict_types=1);` の形式（スペースなし）

#### 8. NullableTypeForNullDefaultValue

デフォルト引数がnullの場合はnullableな型を指定する。

✅ **正しい例**:
```php
public function test(?string $value = null): void
{
    // ...
}
```

❌ **間違った例**:
```php
public function test(string $value = null): void
{
    // ...
}
```

## よくあるエラーと修正方法

### エラー1: Missing parameter type

**エラーメッセージ**:
```
ERROR | Missing parameter type
```

**修正前**:
```php
public function process($userId, $itemId)
{
    // ...
}
```

**修正後**:
```php
public function process(string $userId, string $itemId): void
{
    // ...
}
```

### エラー2: Missing return type

**エラーメッセージ**:
```
ERROR | Missing return type
```

**修正前**:
```php
public function getUser(string $userId)
{
    return UsrUser::find($userId);
}
```

**修正後**:
```php
public function getUser(string $userId): ?UsrUser
{
    return UsrUser::find($userId);
}
```

### エラー3: Line exceeds 120 characters

**エラーメッセージ**:
```
WARNING | Line exceeds 120 characters; contains 145 characters
```

**修正前**:
```php
$result = $this->service->processVeryLongMethodName($param1, $param2, $param3, $param4, $param5);
```

**修正後**:
```php
$result = $this->service->processVeryLongMethodName(
    $param1,
    $param2,
    $param3,
    $param4,
    $param5,
);
```

### エラー4: Useless @var tag

**エラーメッセージ**:
```
ERROR | Useless @var tag
```

**修正前**:
```php
/** @var string $userId */
$userId = $request->input('user_id');
```

**修正後**:
```php
$userId = $request->input('user_id');
```

**理由**: 型が自明な場合、@varタグは不要。

### エラー5: Expected 0 spaces after opening parenthesis

**エラーメッセージ**:
```
ERROR | Expected 0 spaces after opening parenthesis; 1 found
```

**修正前**:
```php
if ( $value === 10 ) {
    // ...
}
```

**修正後**:
```php
if ($value === 10) {
    // ...
}
```

## トラブルシューティング

### 問題1: phpcbfで修正されない

**原因**: 自動修正できない複雑なケース

**対処法**: phpcsのエラーメッセージを確認して手動修正

### 問題2: 大量のエラーが表示される

**原因**: 新規ファイルや大規模な変更

**対処法**:
1. まずphpcbfを実行して自動修正
2. 残ったエラーを1つずつ修正
3. ファイル単位で段階的に対応

### 問題3: 修正後もエラーが消えない

**原因**: キャッシュや設定ミス

**対処法**:
```bash
# キャッシュをクリア
./tools/bin/sail-wp artisan cache:clear

# 再度チェック
./tools/bin/sail-wp phpcs
```

### 問題4: 特定のルールを無視したい

**対処法**: コメントでルールを無視（非推奨、本当に必要な場合のみ）

```php
// phpcs:ignore SlevomatCodingStandard.ControlStructures.DisallowEmpty
if (empty($array)) {
    // ...
}
```

## チェックリスト

- [ ] phpcbfを実行して自動修正を適用
- [ ] 差分を確認して問題ないことを確認
- [ ] phpcsを実行してエラー0、警告0を確認
- [ ] すべてのuse文がアルファベット順に並んでいる
- [ ] 未使用のuse文が削除されている
- [ ] 全ての比較が厳密な比較（===）になっている
- [ ] declare(strict_types=1)が全ファイルに存在する
- [ ] すべてのメソッドに型ヒントと戻り値の型が指定されている
- [ ] コミットメッセージが適切に記載されている
