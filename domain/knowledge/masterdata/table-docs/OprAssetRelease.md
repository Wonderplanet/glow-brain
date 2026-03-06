# OprAssetRelease 詳細説明

> CSVパス: CSVでの管理なし（管理ツール（Admin）から直接DB操作）

---

## 概要

クライアントアプリのアセット（Addressable Assets）リリース状態を管理するテーブル。
リリースキーとプラットフォーム（iOS/Android）の組み合わせごとに、そのアセットリリースが有効かどうか（`enabled`）と、配信対象のバージョン情報（`target_release_version_id`）を保持する。
`enabled=true` かつ `target_release_version_id` が設定されているレコードが、現在有効なアセットリリースとして扱われる。

---

## 全カラム一覧（テーブル形式）

| カラム名 | 型 | NULL | デフォルト | 説明 |
|---|---|---|---|---|
| id | varchar(255) | NOT NULL | - | UUID |
| release_key | int unsigned | NOT NULL | - | リリースキー |
| platform | enum('1','2') | NOT NULL | - | プラットフォーム識別子（1=iOS, 2=Android） |
| enabled | tinyint | NOT NULL | 0 | リリース状態（1=有効, 0=無効） |
| target_release_version_id | varchar(255) | NULL | - | 配信対象のアセットバージョンID（opr_asset_release_versions.id） |
| created_at | timestamp | NOT NULL | - | 作成日時 |
| updated_at | timestamp | NOT NULL | - | 更新日時 |

---

## Platform（platform の enum 値）

| 値 | 説明 |
|---|---|
| 1 | iOS |
| 2 | Android |

---

## インデックス情報

| インデックス名 | 種別 | 対象カラム | 説明 |
|---|---|---|---|
| PRIMARY | PRIMARY | id | 主キー |
| release_key_platform_unique | UNIQUE | release_key, platform | リリースキー+プラットフォームの一意制約 |
| platform_enabled_index | INDEX | platform, enabled | 有効なプラットフォーム別アセット検索用 |

---

## 他テーブルとの連携

| 関連テーブル | 連携カラム | 説明 |
|---|---|---|
| opr_asset_release_versions | target_release_version_id | 配信対象のアセットバージョン詳細 |

---

## サーバーでの使われ方

`OprAssetReleaseVersionRepository::getCurrent()` 内で `opr_asset_releases` と `opr_asset_release_versions` を JOIN して使用される。
クライアントのプラットフォームと `build_client_version` を基に、最新の有効なアセットバージョン情報を返す。

```php
// 現在有効なアセットリリース情報取得（opr_asset_release_versions と JOIN）
OprAssetReleaseVersion::query()
    ->join('opr_asset_releases', 'opr_asset_releases.target_release_version_id', '=', 'opr_asset_release_versions.id')
    ->whereNotNull('opr_asset_releases.target_release_version_id')
    ->where([
        'opr_asset_releases.platform' => $platform,
        'opr_asset_releases.enabled' => true,
        'opr_asset_release_versions.build_client_version' => $clientVersion,
    ])
    ->orderBy('opr_asset_releases.release_key', 'desc')
    ->limit(1)
    ->first();
```

---

## 実データ例

CSVファイルは存在しない。管理ツール（Admin）から直接操作されるテーブルのため、実際のデータ例はDBを直接参照する。

### 想定されるレコード構造の例

| id | release_key | platform | enabled | target_release_version_id |
|---|---|---|---|---|
| {UUID} | 202509010 | 1 | 1 | {opr_asset_release_versions.id} |
| {UUID} | 202509010 | 2 | 1 | {opr_asset_release_versions.id} |
| {UUID} | 202510010 | 1 | 0 | NULL |

---

## 設定時のポイント

1. **release_key と platform の組み合わせはユニーク**: 同一のリリースキー・プラットフォームの組み合わせでは1レコードしか登録できない（`release_key_platform_unique` 制約）。
2. **enabled は明示的に true にしなければ有効にならない**: デフォルト値は0（無効）であるため、アセットリリースを有効化するには管理ツールからの操作が必要。
3. **target_release_version_id は NULL 許容**: アセットビルドが完了していない段階では NULL のまま登録し、ビルド完了後に更新する運用が可能。
4. **iOS と Android を別レコードで管理**: プラットフォームごとに独立したリリース管理が行われる。
5. **release_key が高いレコードが優先される**: 複数の有効なレコードがある場合、release_key が最も高いものが有効なアセットリリースとして選択される。
6. **CSVでの管理は行わない**: 本テーブルはマスタデータCSVではなく、管理ツール（Admin UI）から直接操作される運営データテーブル。
7. **アセットリリースは opr_asset_release_versions と必ずセットで管理**: `target_release_version_id` に設定するバージョン情報が opr_asset_release_versions に存在することを確認する。
