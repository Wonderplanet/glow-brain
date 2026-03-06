# OprAssetReleaseVersion 詳細説明

> CSVパス: CSVでの管理なし（アセットビルドパイプラインから自動登録）

---

## 概要

クライアントアプリのアセット（Addressable Assets）ビルド情報を管理するテーブル。
アセットビルド時のGitリビジョン・ブランチ・カタログハッシュ・プラットフォーム・クライアントバージョンおよびアセット容量情報を記録する。
`opr_asset_releases` テーブルの `target_release_version_id` から参照され、現在クライアントに配信するアセットバージョンを特定するために使用される。

---

## 全カラム一覧（テーブル形式）

| カラム名 | 型 | NULL | デフォルト | 説明 |
|---|---|---|---|---|
| id | varchar(255) | NOT NULL | - | UUID |
| release_key | int unsigned | NOT NULL | - | リリースキー |
| git_revision | varchar(255) | NOT NULL | - | ビルドを行ったクライアントリポジトリのGitリビジョン（コミットSHA） |
| git_branch | varchar(255) | NOT NULL | - | ビルドを行ったクライアントリポジトリのカレントブランチ名 |
| catalog_hash | varchar(255) | NOT NULL | - | AddressableAssetをビルドした時のCatalogハッシュ値 |
| platform | enum('1','2') | NOT NULL | - | プラットフォーム識別子（1=iOS, 2=Android） |
| build_client_version | varchar(255) | NOT NULL | - | ビルドを行ったクライアントアプリのバージョン |
| asset_total_byte_size | bigint unsigned | NOT NULL | - | アセット全体のバイト容量 |
| catalog_byte_size | bigint unsigned | NOT NULL | - | カタログファイルのバイト容量 |
| catalog_file_name | varchar(255) | NOT NULL | - | カタログファイル名 |
| catalog_hash_file_name | varchar(255) | NOT NULL | - | カタログハッシュファイル名 |
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

---

## 他テーブルとの連携

| 関連テーブル | 連携カラム | 説明 |
|---|---|---|
| opr_asset_releases | target_release_version_id → id | 有効なアセットリリースから本テーブルへの参照 |

---

## サーバーでの使われ方

`OprAssetReleaseVersionRepository::getCurrent()` で `opr_asset_releases` とJOINして使用される。
クライアントのプラットフォームと `build_client_version` を条件に、最新の有効なアセットビルド情報を取得する。

```php
// opr_asset_releases と JOIN して現在有効なアセットバージョンを取得
OprAssetReleaseVersion::query()
    ->select('opr_asset_release_versions.*')
    ->join('opr_asset_releases', 'opr_asset_releases.target_release_version_id', '=', 'opr_asset_release_versions.id')
    ->whereNotNull('opr_asset_releases.target_release_version_id')
    ->where([
        'opr_asset_releases.platform' => $platform,       // プラットフォームで絞り込み
        'opr_asset_releases.enabled' => true,             // 有効なリリースのみ
        'opr_asset_release_versions.build_client_version' => $clientVersion,  // クライアントバージョンで絞り込み
    ])
    ->orderBy('opr_asset_releases.release_key', 'desc')   // 最新リリースキーを優先
    ->limit(1)
    ->first();
```

---

## 実データ例

CSVファイルは存在しない。アセットビルドパイプライン（CI/CD）から自動的に登録されるテーブルのため、実際のデータはDBまたは管理ツールを参照する。

### 想定されるレコード構造の例

| id | release_key | git_revision | git_branch | catalog_hash | platform | build_client_version | asset_total_byte_size | catalog_byte_size | catalog_file_name |
|---|---|---|---|---|---|---|---|---|---|
| {UUID} | 202509010 | abc123def456 | release/v1.5.0 | xyz789 | 1 | 1.5.0 | 524288000 | 102400 | catalog_v1.5.0.json |
| {UUID} | 202509010 | abc123def456 | release/v1.5.0 | xyz789 | 2 | 1.5.0 | 524288000 | 102400 | catalog_v1.5.0.json |

---

## 設定時のポイント

1. **本テーブルはビルドパイプラインから自動登録される**: 手動での設定は行わず、CI/CDのアセットビルドジョブが完了時に自動的に登録する設計。
2. **iOS と Android は別レコードで管理**: プラットフォームごとにビルド結果が異なるため、それぞれ独立したレコードを持つ。
3. **build_client_version でアセットのバージョン互換性を管理**: クライアントアプリのバージョンに紐付いたアセットを配信するため、この値は正確に設定する必要がある。
4. **catalog_hash でアセット更新要否を判定**: クライアントが保持しているカタログハッシュと比較してアセットのダウンロードが必要かを判断する。
5. **asset_total_byte_size と catalog_byte_size は容量管理に使用**: アセットダウンロードの事前通知や容量警告のUI表示に利用される可能性がある。
6. **opr_asset_releases との整合性が重要**: 本テーブルのレコードが opr_asset_releases.target_release_version_id に設定されて初めて有効なリリースとなる。
7. **CSVでの管理は行わない**: 本テーブルはマスタデータCSVではなく、ビルドパイプラインが自動管理する運営インフラテーブル。
8. **git_revision と git_branch はデバッグ・追跡用途**: どのコミット・ブランチからビルドされたかを追跡するためのメタ情報として保持する。
