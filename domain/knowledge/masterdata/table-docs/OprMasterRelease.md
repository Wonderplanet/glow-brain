# OprMasterRelease 詳細説明

> CSVパス: `projects/glow-masterdata/OprMasterRelease.csv`（CSVファイルは存在しない。サーバーDBで直接管理）

---

## 概要

リリース済みマスターデータの情報を管理するテーブル。`release_key` をプライマリキーとして、各リリースキーが「有効（enabled=1）」かどうか、そしてどのバージョン（`opr_master_release_versions`）に対応しているかを管理する。

CSVファイルは存在せず、サーバーDB上でマスターデータ配信スクリプトやデプロイ処理によって直接管理される運用テーブルである。

---

## 全カラム一覧

| カラム名 | 型 | NULL | デフォルト | 説明 |
|---------|-----|------|-----------|------|
| id | varchar(255) | NO | - | UUID |
| release_key | int unsigned | NO | - | リリースキー（UNIQUE制約あり） |
| enabled | tinyint unsigned | NO | 0 | リリース状態（0=無効、1=有効） |
| target_release_version_id | varchar(255) | YES | - | 適用バージョン（opr_master_release_versions.id） |

---

## 他テーブルとの連携

| 関連テーブル | 関係 | 内容 |
|------------|------|------|
| opr_master_release_versions | N:1 | target_release_version_id でバージョン情報を参照 |
| opr_*（各運営テーブル） | 1:N | 各テーブルの release_key がこのテーブルの release_key を参照 |

UNIQUE制約: `release_key` で一意（同一リリースキーは1レコードのみ）

---

## リリースキーの命名規則

リリースキーは `YYYYMM###` の形式で管理される。

| 部位 | 説明 |
|------|------|
| YYYY | 西暦年（例: 2025） |
| MM | 月（例: 09） |
| ### | 連番（例: 010, 020） |

例: `202509010`（2025年9月の1回目リリース）

特殊値:
- `999999999` - テストデータや開発環境専用の永続データに使用される

---

## 実データ例（DBスキーマから推定）

**例1: 有効なリリースキー**
```
id: <UUID>
release_key: 202509010
enabled: 1
target_release_version_id: <version_uuid>
```

**例2: 無効（まだリリースされていない）リリースキー**
```
id: <UUID>
release_key: 202512010
enabled: 0
target_release_version_id: NULL
```

---

## 設定時のポイント

1. **CSVによる管理ではなくサーバー側で直接管理**: マスターデータのリリース・ロールバック処理と連動して自動更新される。
2. **enabled=0 のリリースキーのデータはクライアントに配信されない**: リリースキーを有効化することでそのキーに紐づくデータが公開される。
3. **各運営テーブル（opr_*）の release_key はこのテーブルの release_key と対応**: 運営データを追加する際は対応する release_key が本テーブルに存在している必要がある。
4. **target_release_version_id は opr_master_release_versions と連携**: どのバージョンのマスターデータが適用されているかを追跡できる。
5. **UNIQUE制約により同一 release_key は1レコードのみ**: 同じリリースキーを複数回登録することはできない。
6. **ロールバック時は enabled を 0 に戻す**: 問題が発生したリリースキーを無効化することで即座にロールバック対応が可能。
