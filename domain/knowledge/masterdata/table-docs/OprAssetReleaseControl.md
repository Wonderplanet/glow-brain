# OprAssetReleaseControl 詳細説明

> CSVパス: CSVでの管理なし（管理ツール（Admin）から直接DB操作）

---

## 概要

クライアントアプリに配信するアセット（Addressable Assets）のリリーススケジュールと配信URLを管理するテーブル。
ブランチ名・コミットハッシュ・バージョン指定・プラットフォーム・リリース予定日時を設定することで、指定日時以降にそのアセットがクライアントに配信される。
`getCurrent()` ではリリース日時が現在時刻以前のレコードのうち最新のものが有効な配信設定として返される。

---

## 全カラム一覧（テーブル形式）

| カラム名 | 型 | NULL | デフォルト | 説明 |
|---|---|---|---|---|
| id | varchar(255) | NOT NULL | - | UUID |
| release_key | bigint | NOT NULL | 1 | リリースキー |
| version | varchar(255) | NULL | - | バージョン指定（クライアントアプリのバージョン） |
| platform | int | NULL | 0 | プラットフォーム指定（0=全プラットフォーム, 1=iOS, 2=Android） |
| branch | varchar(255) | NOT NULL | - | アセットが配置されているブランチ名 |
| hash | varchar(255) | NOT NULL | - | アセットのコミットハッシュ |
| version_no | int | NOT NULL | 0 | バージョンナンバー |
| release_at | timestamp | NOT NULL | - | リリース予定日時 |
| release_description | varchar(255) | NULL | - | リリース内容のメモ |
| created_at | timestamp | NULL | - | 作成日時 |
| updated_at | timestamp | NULL | - | 更新日時 |

---

## Platform（platform の値）

| 値 | 説明 |
|---|---|
| 0 | 全プラットフォーム共通 |
| 1 | iOS |
| 2 | Android |

---

## インデックス情報

| インデックス名 | 種別 | 対象カラム | 説明 |
|---|---|---|---|
| PRIMARY | PRIMARY | id | 主キー |
| idx_release_at | INDEX | release_at | リリース日時での検索用 |

---

## 他テーブルとの連携

| 関連テーブル | 連携カラム | 説明 |
|---|---|---|
| opr_asset_releases | - | アセットリリース状態の管理（有効/無効） |
| opr_asset_release_versions | - | 実際のアセットビルド情報の管理 |

---

## サーバーでの使われ方

`OprAssetReleaseControlRepository::getCurrent()` で、指定プラットフォームとバージョンに合致する最新の有効なリリース設定を取得する。

```php
// プラットフォームとバージョンを条件に最新のリリース設定を取得
OprAssetReleaseControl::query()
    ->when(isset($version), function ($query) use ($version) {
        return $query->where('version', $version);
    })
    ->where('platform', $platform)
    ->where('release_at', '<=', $now)  // リリース日時が現在以前のもの
    ->orderBy('release_at', 'desc')    // 最新のリリース設定を優先
    ->orderBy('created_at', 'desc')
    ->first();
```

アセットURLの生成には `getUrl()` メソッドが使われ、`{branch}/{hash}` の形式で返される。
アップデート要否の判定には `isRequireUpdate(string $hash)` で現在のハッシュと比較する。

---

## 実データ例

CSVファイルは存在しない。管理ツール（Admin UI）から直接操作されるテーブルのため、実際のデータは管理ツールまたはDBを直接参照する。

### 想定されるレコード構造の例

| id | release_key | version | platform | branch | hash | version_no | release_at | release_description |
|---|---|---|---|---|---|---|---|---|
| {UUID} | 202509010 | 1.5.0 | 1 | release/v1.5.0 | abc123 | 1 | 2025-09-01 10:00:00 | v1.5.0 iOS アセットリリース |
| {UUID} | 202509010 | 1.5.0 | 2 | release/v1.5.0 | abc123 | 1 | 2025-09-01 10:00:00 | v1.5.0 Android アセットリリース |

---

## 設定時のポイント

1. **release_at を未来に設定することで事前登録が可能**: リリース日時になるまでクライアントには配信されないため、事前準備として登録しておける。
2. **platform=0 は全プラットフォーム共通**: iOS・Android 共通の設定には 0 を使い、個別設定には 1（iOS）または 2（Android）を使う。
3. **hash でアップデート要否を判定**: クライアントが保持しているハッシュと `hash` を比較してアセット更新が必要かを判断する。
4. **branch と hash でアセット取得URLが構成される**: `getUrl()` は `{branch}/{hash}` を返すため、両値は正確に設定する。
5. **version_no は整数の連番で管理**: 同一バージョン内での更新回数管理に使用する。
6. **CSVでの管理は行わない**: 本テーブルはマスタデータCSVではなく、管理ツール（Admin UI）から直接操作される運営データテーブル。
7. **release_description はメモ用途**: 何のアセットをリリースしたかを記録するためのメモフィールドで、ゲームロジックには影響しない。
8. **リリース日時の重複に注意**: 同一プラットフォーム・バージョンで release_at が完全に同じ場合は `created_at` で優先順が決まる。
