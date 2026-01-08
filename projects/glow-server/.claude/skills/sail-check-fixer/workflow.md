# Workflow: エラー解消の全体フロー

## 目次

1. [概要](#概要)
2. [実行環境の確認](#実行環境の確認)
3. [エラー解消の基本戦略](#エラー解消の基本戦略)
4. [推奨実行順序](#推奨実行順序)
5. [各ステップの詳細](#各ステップの詳細)
6. [効率的な作業のコツ](#効率的な作業のコツ)
7. [完了条件](#完了条件)

## 概要

`sail check`は以下の5つのチェックを順次実行します：

```bash
# 実行されるコマンドの全体像
./tools/bin/sail-wp check

# 内部で以下が順次実行される：
# 1. phpcbf (自動修正)
# 2. phpcs (手動確認)
# 3. phpstan (静的解析)
# 4. deptrac (アーキテクチャ)
# 5. test (テスト実行)
```

**実装場所**: `tools/code_check.sh`

## 実行環境の確認

### 前提条件

```bash
# 1. Dockerコンテナが起動していることを確認
docker compose ps

# 2. PHPコンテナが正常に動作していることを確認
./tools/bin/sail-wp php -v
```

### 対象ディレクトリ

#### phpcs/phpcbf
- `api/app/Http/Controllers`
- `api/app/Domain`
- `local/lib/laravel-wp-billing/src`
- `local/lib/laravel-wp-currency/src`

#### phpstan
- `api/app/`
- `local/lib/laravel-wp-billing/src`
- `local/lib/laravel-wp-currency/src`
- 除外: `api/app/Console/Commands/*`, `api/app/Http/Resources/Api/*`

#### deptrac
- `api/app`
- `local/lib/laravel-wp-billing/src`
- `local/lib/laravel-wp-currency/src`

#### test
- `api/tests/`配下の全テスト

## エラー解消の基本戦略

### 優先順位の考え方

1. **自動修正可能なものを最優先** (phpcbf)
   - 手間がかからず、後続の手動修正の負担を減らせる

2. **手動修正が必要なものを次に** (phpcs)
   - コーディング規約を満たすことで、phpstanのエラーも減る可能性がある

3. **型エラーを解消** (phpstan)
   - 静的解析をクリアすることで、実行時エラーのリスクを減らす

4. **アーキテクチャ違反を修正** (deptrac)
   - 依存関係の修正は、テストの成功にも影響する場合がある

5. **最後にテストを修正** (test)
   - 上記の修正でテストが通る場合もあるため、最後に実施

### 作業の進め方

#### ✅ 推奨: 段階的アプローチ

各ステップを1つずつクリアしていく方式。

```bash
# 1. phpcbfを実行して自動修正
./tools/bin/sail-wp phpcbf

# 2. phpcsを実行して手動修正が必要な項目を確認
./tools/bin/sail-wp phpcs

# 手動修正後、再度phpcsで確認
./tools/bin/sail-wp phpcs

# 3. phpstanを実行
./tools/bin/sail-wp phpstan

# エラーを修正後、再度phpstanで確認
./tools/bin/sail-wp phpstan

# 4. deptracを実行
./tools/bin/sail-wp deptrac

# 5. テストを実行
./tools/bin/sail-wp test --coverage | grep -v '100.0 %'
```

#### ⚠️ 非推奨: 一括実行アプローチ

全チェックを一度に実行すると、エラーが大量に表示され優先順位がつけにくい。

```bash
# 非推奨: 最初から全チェックを実行
./tools/bin/sail-wp check
```

## 推奨実行順序

### ステップ1: phpcbf（自動修正）

**所要時間**: 1-2分

```bash
./tools/bin/sail-wp phpcbf
```

**目的**: 自動修正可能なコーディング規約違反を一括修正

**期待される結果**:
- インデント、空白、カンマの配置などが自動修正される
- `declare(strict_types=1);`が自動追加される
- use文がアルファベット順に並び替えられる

**次のアクション**: 修正内容を確認して、問題なければコミット

### ステップ2: phpcs（手動確認）

**所要時間**: 5-15分

```bash
./tools/bin/sail-wp phpcs
```

**目的**: 自動修正できない規約違反を検出

**期待される結果**:
- 0エラー、0警告

**よくあるエラー**:
- 複雑な配列構造の最後のカンマ不足
- 型ヒントの不足
- 厳密な比較（===）の使用漏れ

### ステップ3: phpstan（静的解析）

**所要時間**: 10-30分

```bash
./tools/bin/sail-wp phpstan
```

**目的**: 型エラーやコード品質の問題を検出

**期待される結果**:
- `[OK] No errors`

**よくあるエラー**:
- 型アノテーション不足 (@param, @return, @var)
- null許容型の扱い
- 配列型の指定不足
- プロパティアクセスの型エラー

### ステップ4: deptrac（アーキテクチャ）

**所要時間**: 5-15分

```bash
./tools/bin/sail-wp deptrac
```

**目的**: レイヤー間の依存関係違反を検出

**期待される結果**:
- `Violations: 0`

**よくある違反**:
- ControllerがServiceを直接呼び出している
- UseCaseがDelegatorを経由せず他ドメインに依存
- DelegatorがDomainEntityを返している

### ステップ5: test（テスト実行）

**所要時間**: 5-30分

```bash
./tools/bin/sail-wp test --coverage | grep -v '100.0 %'
```

**目的**: 全テストの成功とカバレッジの確認

**期待される結果**:
- 全テストがPASS
- カバレッジ100%未満の箇所が表示される（問題ない場合もある）

**よくあるエラー**:
- アサーションエラー（期待値と実際の値が異なる）
- セットアップエラー（テストデータの準備不足）
- 例外エラー（想定外の例外が発生）

## 各ステップの詳細

### phpcbf: 自動修正の仕組み

#### 修正対象

**PSR-12準拠**:
- インデント（スペース4つ）
- 行末の空白削除
- ファイル末尾の改行

**Slevomat Coding Standard**:
- `declare(strict_types=1);`の追加
- use文のアルファベット順並び替え
- 配列の最後のカンマ追加（単行配列は除く）
- `==`を`===`に変更

#### 実行後の確認

```bash
# 差分を確認
git diff

# 修正内容が適切であればステージング
git add .
```

### phpcs: 手動修正が必要な項目

#### エラー表示形式

```
FILE: /path/to/file.php
----------------------------------------------------------------------
FOUND 2 ERRORS AFFECTING 2 LINES
----------------------------------------------------------------------
 45 | ERROR | [x] Expected 1 space after USE keyword; 0 found
 67 | ERROR | [ ] Missing parameter type
----------------------------------------------------------------------
```

**記号の意味**:
- `[x]` : phpcbfで自動修正可能（通常はphpcbf実行済みなので出ないはず）
- `[ ]` : 手動修正が必要

#### 修正方法

各エラーメッセージに従って手動で修正。詳細は [phpcs-phpcbf-guide.md](phpcs-phpcbf-guide.md) 参照。

### phpstan: 型エラーの修正

#### エラー表示形式

```
------ -----------------------------------------------------------------------
Line   app/Domain/Example/Services/ExampleService.php
------ -----------------------------------------------------------------------
 45     Parameter #1 $userId of method getUserData() expects string, int given.
 67     Method getItems() should return array<Item> but returns array.
------ -----------------------------------------------------------------------
```

#### 修正の基本方針

1. 型アノテーションを追加
2. 型キャストを適切に行う
3. null許容型を正しく扱う

詳細は [phpstan-guide.md](phpstan-guide.md) 参照。

### deptrac: 依存関係違反の修正

#### エラー表示形式

```
Violations: 1

Controller must not depend on Service
app/Http/Controllers/ExampleController.php:45
  -> app/Domain/Example/Services/ExampleService.php:10
```

#### 修正の基本方針

1. Controllerは必ずUseCaseを経由
2. UseCaseは他ドメインにはDelegatorを経由
3. DelegatorはDomainEntityを返さない（ResourceEntity/UsrModelEntityを返す）

詳細は [deptrac-guide.md](deptrac-guide.md) 参照。

### test: テスト失敗の修正

#### エラー表示形式

```
FAILED  Tests\Feature\Domain\Example\ExampleServiceTest > test_example
  Failed asserting that 10 matches expected 5.

  at tests/Feature/Domain/Example/ExampleServiceTest.php:67
```

#### 修正の基本方針

1. エラーメッセージから原因を特定
2. テストコードまたは実装コードを修正
3. 必要に応じてテストデータを調整

詳細は [test-guide.md](test-guide.md) 参照。

## 効率的な作業のコツ

### 1. エラーログの保存

```bash
# エラーログをファイルに保存して分析
./tools/bin/sail-wp phpstan 2>&1 | tee phpstan_errors.log
```

### 2. ファイル単位での確認

```bash
# 特定ファイルのみチェック
./tools/bin/sail-wp phpstan analyze api/app/Domain/Example/Services/ExampleService.php
```

### 3. 並行作業の可否

**並行可能**:
- phpcs/phpcbfとphpstanは独立しているため、異なるファイルで並行作業可能

**並行不可**:
- deptracとtestは、実装コードの修正が影響するため、逐次作業推奨

### 4. コミットのタイミング

各ステップをクリアしたらコミットを推奨：

```bash
# phpcbf実行後
git add .
git commit -m "fix: コーディング規約違反を自動修正 (phpcbf)"

# phpcs修正後
git add .
git commit -m "fix: コーディング規約違反を手動修正 (phpcs)"

# phpstan修正後
git add .
git commit -m "fix: 静的解析エラーを修正 (phpstan)"

# deptrac修正後
git add .
git commit -m "fix: アーキテクチャ違反を修正 (deptrac)"

# test修正後
git add .
git commit -m "fix: テストエラーを修正 (test)"
```

### 5. エラーの優先順位付け

大量のエラーがある場合は、以下の基準で優先順位をつける：

1. **影響範囲が小さいもの** - 単一ファイル内で完結する修正
2. **自動修正に近いもの** - 型アノテーション追加など、機械的に対応可能
3. **複数エラーの根本原因** - 1つの修正で複数エラーが解消される場合

## 完了条件

### 最終確認

```bash
# 全チェックを実行
./tools/bin/sail-wp check
```

### 成功の基準

#### phpcbf
```
PHPCBF RESULT SUMMARY
----------------------------------------------------------------------
A TOTAL OF 0 ERRORS WERE FIXED IN 0 FILES
----------------------------------------------------------------------
```

#### phpcs
```
----------------------------------------------------------------------
FOUND 0 ERRORS AND 0 WARNINGS AFFECTING 0 LINES
----------------------------------------------------------------------
```

#### phpstan
```
[OK] No errors
```

#### deptrac
```
Violations: 0
```

#### test
```
Tests:    XXX passed (XXX assertions)
Duration: XX.XXs
```

### チェックリスト

- [ ] phpcbf実行後、自動修正内容を確認してコミット
- [ ] phpcsでエラー0、警告0を確認
- [ ] phpstanでエラー0を確認
- [ ] deptracで違反0を確認
- [ ] testで全テストPASSを確認
- [ ] `sail check`全体を実行して、全チェックが成功することを確認
- [ ] コミットメッセージが適切に記載されている
