# OprMasterReleaseVersion 詳細説明

> CSVパス: `projects/glow-masterdata/OprMasterReleaseVersion.csv`（CSVファイルは存在しない。サーバーDBで直接管理）

---

## 概要

マスターデータ配信バージョン情報を管理するテーブル。リリースキーに対応する Git リビジョンとデータハッシュを一元管理し、サーバー・クライアント間でのデータ同期状態を追跡する。

`opr_master_releases.target_release_version_id` から参照され、特定リリースキーのどのバージョンのデータが実際に適用されているかを記録するバージョン管理テーブルである。CSVファイルは存在せず、マスターデータのインポート・デプロイ処理で自動生成される。

---

## 全カラム一覧

| カラム名 | 型 | NULL | デフォルト | 説明 |
|---------|-----|------|-----------|------|
| id | varchar(255) | NO | - | UUID |
| release_key | int unsigned | NO | - | リリースキー |
| git_revision | varchar(255) | NO | - | 適用した Git リビジョン（コミットハッシュ） |
| master_scheme_version | varchar(255) | NO | - | マスターデータテーブルスキーマのハッシュ値 |
| data_hash | varchar(255) | NO | - | 全実データを一意に識別するハッシュ値 |
| server_db_hash | varchar(255) | NO | - | サーバーDBのハッシュ値 |
| client_mst_data_hash | varchar(255) | NO | - | クライアントマスターデータのハッシュ値 |
| client_mst_data_i18n_ja_hash | varchar(255) | NO | - | クライアントマスター多言語（日本語）のハッシュ値 |
| client_mst_data_i18n_en_hash | varchar(255) | NO | - | クライアントマスター多言語（英語）のハッシュ値 |
| client_mst_data_i18n_zh_hash | varchar(255) | NO | - | クライアントマスター多言語（繁体字中国語）のハッシュ値 |
| client_opr_data_hash | varchar(255) | NO | - | クライアント運用データのハッシュ値 |
| client_opr_data_i18n_ja_hash | varchar(255) | NO | - | クライアント運用多言語（日本語）のハッシュ値 |
| client_opr_data_i18n_en_hash | varchar(255) | NO | - | クライアント運用多言語（英語）のハッシュ値 |
| client_opr_data_i18n_zh_hash | varchar(255) | NO | - | クライアント運用多言語（繁体字中国語）のハッシュ値 |

---

## ハッシュ値の種類と責務

| ハッシュカラム | スコープ | 説明 |
|-------------|---------|------|
| master_scheme_version | スキーマ定義 | テーブル構造の変更を検知するためのスキーマハッシュ |
| data_hash | 全データ | 全マスターデータを包括する総合ハッシュ |
| server_db_hash | サーバーDB | サーバー側DBに格納された全データのハッシュ |
| client_mst_data_hash | クライアント（mst_*、共通） | 言語非依存の固定マスターデータ |
| client_mst_data_i18n_*_hash | クライアント（mst_*、言語別） | 各言語版の固定マスター多言語データ |
| client_opr_data_hash | クライアント（opr_*、共通） | 言語非依存の運用データ |
| client_opr_data_i18n_*_hash | クライアント（opr_*、言語別） | 各言語版の運用多言語データ |

---

## 他テーブルとの連携

| 関連テーブル | 関係 | 内容 |
|------------|------|------|
| opr_master_releases | 1:N | target_release_version_id でこのテーブルを参照 |
| opr_master_release_controls | 参照 | release_key と git_revision で紐付く |

---

## opr_master_releases との違い

| テーブル | 役割 |
|---------|------|
| opr_master_releases | 「リリースキーが有効か否か」の管理（enabled フラグ） |
| opr_master_release_controls | 「いつどのデータをリリースするか」のスケジュール管理 |
| opr_master_release_versions | 「実際に適用されたデータがどのバージョンか」のバージョン記録 |

---

## 実データ例（DBスキーマから推定）

**例1: バージョンレコード**
```
id: <UUID>
release_key: 202509010
git_revision: abc1234567890def...
master_scheme_version: <schema_hash>
data_hash: <total_data_hash>
server_db_hash: <server_hash>
client_mst_data_hash: <mst_hash>
client_mst_data_i18n_ja_hash: <mst_ja_hash>
client_mst_data_i18n_en_hash: <mst_en_hash>
client_mst_data_i18n_zh_hash: <mst_zh_hash>
client_opr_data_hash: <opr_hash>
client_opr_data_i18n_ja_hash: <opr_ja_hash>
client_opr_data_i18n_en_hash: <opr_en_hash>
client_opr_data_i18n_zh_hash: <opr_zh_hash>
```

**例2: 同一リリースキーに複数バージョン（修正リリース）**
```
id: <UUID_v2>
release_key: 202509010
git_revision: def9876543210abc...   ← 修正コミット
master_scheme_version: <schema_hash>
data_hash: <updated_total_hash>     ← 変更されたハッシュ
...
```

---

## 設定時のポイント

1. **このテーブルはデプロイ自動化によって生成される**: 手動での操作は基本的に不要。
2. **master_scheme_version はスキーマ変更の検知に使用**: テーブル構造に変更があると異なる値になる。
3. **data_hash は全データの整合性検証に使用**: リリース前後でデータの一致確認が可能。
4. **同一 release_key に対して複数バージョンが存在し得る**: データ修正による再デプロイで新しいバージョンレコードが追加される。
5. **opr_master_releases.target_release_version_id で最新の適用バージョンを指す**: 複数バージョンがある場合は最後に適用されたものが参照される。
6. **クライアントとサーバーのハッシュを分離管理**: サーバーDBとクライアント配信データを独立して追跡できる。
7. **i18n データは言語ごとにハッシュが分離**: 特定言語のデータ変更のみを効率的に検知できる。
