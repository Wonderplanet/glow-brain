# glow-schema PR実装ワークフロー

このドキュメントでは、glow-schemaのPR変更をglow-serverに反映する際の全体ワークフローを説明します。

## 目次

1. [glow-schema PRの解析](#1-glow-schema-prの解析)
2. [変更パターンの特定](#2-変更パターンの特定)
3. [影響範囲の確認](#3-影響範囲の確認)
4. [実装の実行](#4-実装の実行)
5. [テストと確認](#5-テストと確認)

---

## 1. glow-schema PRの解析

### PRの取得方法

ユーザーからglow-schema PRの情報を受け取るパターン：

```
パターン1: PR番号のみ
「glow-schema PR #474に対応して」

パターン2: PRリンク
「https://github.com/Wonderplanet/glow-schema/pull/474 に対応」

パターン3: glow-server PRから参照
「このPRで参照しているglow-schema PRに対応」
→ PR本文に "schema: https://github.com/Wonderplanet/glow-schema/pull/XXX" の形式で記載
```

### PR情報の確認コマンド

```bash
# glow-schema PR本文を確認
gh pr view {PR番号} --repo Wonderplanet/glow-schema --json title,body

# 変更ファイルを確認
gh pr diff {PR番号} --repo Wonderplanet/glow-schema
```

### 確認すべき情報

- [ ] PR タイトル
- [ ] PR 本文（やったことセクション）
- [ ] 変更されたYAMLファイルのパス
- [ ] API設計Confluenceリンク（ある場合）
- [ ] 関連するクライアント実装PR（ある場合）

---

## 2. 変更パターンの特定

glow-schemaの変更から、以下のいずれかのパターンを特定します：

### パターン一覧

| パターン | 説明 | キーワード |
|---------|------|-----------|
| **新規テーブル作成** | 新しいテーブル定義の追加 | `create`, `新規テーブル`, `table追加` |
| **カラム追加** | 既存テーブルへのカラム追加 | `カラム追加`, `add column`, `フィールド追加` |
| **カラム削除** | 既存テーブルからのカラム削除 | `カラム削除`, `削除`, `drop column`, `remove` |
| **カラム型変更** | データ型や属性の変更 | `型変更`, `enum→varchar`, `nullable` |
| **テーブル削除** | テーブル全体の削除 | `テーブル削除`, `drop table`, `不要` |
| **カラム名変更** | カラム名のリネーム | `rename`, `→`, `変更` |
| **テーブル構造変更** | テーブル分離・統合 | `分離`, `統合`, `移動` |
| **複合的な変更** | 上記の複数が組み合わさった変更 | 複数のキーワード |

### 判断方法

```yaml
# YAMLファイルの差分から判断
diff --git a/resources/opr/tables/opr_gacha_histories.yml b/resources/opr/tables/opr_gacha_histories.yml
new file mode 100644  # ← 新規ファイル = 新規テーブル作成

diff --git a/resources/mst/tables/mst_enemy_characters.yml b/resources/mst/tables/mst_enemy_characters.yml
+  is_phantomized:  # ← プロパティ追加 = カラム追加

-  bounding_range_front:  # ← プロパティ削除 = カラム削除
```

---

## 3. 影響範囲の確認

### 対象データベースの特定

YAMLファイルのパスからDB接続を特定：

```
resources/mst/tables/*.yml  → Database::MST_CONNECTION (MySQL)
resources/mng/tables/*.yml  → Database::MNG_CONNECTION (MySQL)
resources/opr/tables/*.yml  → Database::MNG_CONNECTION (MySQL)
resources/usr/tables/*.yml  → Database::USR_CONNECTION (TiDB)
resources/log/tables/*.yml  → Database::LOG_CONNECTION (TiDB)
resources/sys/tables/*.yml  → Database::SYS_CONNECTION (TiDB)
```

### 影響を受けるファイルの確認

変更に応じて、以下のファイルが影響を受ける可能性があります：

#### マイグレーションファイル（必須）
```
api/database/migrations/{db}/*.php
```

#### Entityクラス（カラム追加・削除・変更時）
```
api/app/Domain/{Domain}/Entities/*.php
```

#### Modelクラス（テーブル変更時）
```
api/app/Infrastructure/Database/Models/*.php
```

#### Repositoryクラス（新規テーブル・大きな変更時）
```
api/app/Domain/{Domain}/Repositories/*Repository.php
api/app/Infrastructure/Database/Repositories/*RepositoryImpl.php
```

#### Resourceファイル（レスポンス変更時）
```
api/app/Domain/{Domain}/Resources/*.php
```

---

## 4. 実装の実行

### ステップ1: マイグレーションファイルの作成

**migrationスキルを使用:**

```
Skill: migration

手順:
1. 対象DB（mst/mng/usr/log/sys）を確認
2. 変更パターンに応じたマイグレーションを作成
3. migration naming conventionsに従う
4. up()とdown()の両方を実装
```

詳細は [migration skill](../migration/SKILL.md) を参照。

### ステップ2: Entity/Modelの更新

**新規カラム追加の場合:**

```php
// Entity クラスに property を追加
class MstEnemyCharacter
{
    public function __construct(
        // ... 既存プロパティ
        private int $isPhantomized,  // ← 追加
    ) {
    }
}
```

**カラム削除の場合:**

```php
// Entity クラスから property を削除
- private float $boundingRangeFront,  // ← 削除
```

**カラム名変更の場合:**

```php
// property名を変更
- private string $serif1,
+ private string $speechBalloonText1,
```

### ステップ3: Resourceファイルの更新

APIレスポンスに含まれる場合は、Resourceファイルも更新：

```php
public function toArray(): array
{
    return [
        // ... 既存フィールド
        'isPhantomized' => $this->isPhantomized,  // ← 追加
    ];
}
```

### ステップ4: Repositoryの更新（必要に応じて）

新規テーブルの場合は、Repositoryの作成が必要：

```php
// Domain層
interface GachaHistoryRepository
{
    public function add(...): void;
    public function findByUserId(string $usrUserId): Collection;
}

// Infrastructure層
class GachaHistoryRepositoryImpl implements GachaHistoryRepository
{
    // 実装
}
```

---

## 5. テストと確認

### マイグレーションの実行確認

```bash
# マイグレーション実行
./tools/bin/sail-wp migrate

# ロールバック確認
./tools/bin/sail-wp migrate:rollback --step=1
```

### テーブル構造の確認

```bash
# MySQL (mst/mng)
docker exec -it mysql_container mysql -u root -p localDB
> DESCRIBE {table_name};

# TiDB (usr/log/sys)
docker exec -it tidb_container mysql -u root -p localDB
> DESCRIBE {table_name};
```

### テストの実行

```bash
# 関連テストの実行
./tools/bin/sail-wp test --filter={TestClass}

# 全テスト実行
./tools/bin/sail-wp test
```

### 確認チェックリスト

実装完了前に以下を確認：

- [ ] マイグレーションファイルが作成されている
- [ ] マイグレーションが正常に実行できる
- [ ] ロールバックが正常に実行できる
- [ ] Entity/Modelが更新されている
- [ ] Resourceファイルが更新されている（必要な場合）
- [ ] Repositoryが作成/更新されている（必要な場合）
- [ ] テストが通る
- [ ] 命名規則に従っている

---

## 実装パターン別ガイド

具体的な実装方法は、[patterns.md](guides/patterns.md) を参照してください。

## 実装例

実際のPRを基にした実装例は、以下のファイルを参照：

- [new-table.md](examples/new-table.md)
- [column-changes.md](examples/column-changes.md)
- [table-deletion.md](examples/table-deletion.md)
- [complex.md](examples/complex.md)
