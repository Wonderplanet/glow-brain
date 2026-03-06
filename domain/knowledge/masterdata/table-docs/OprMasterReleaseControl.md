# OprMasterReleaseControl 詳細説明

> CSVパス: `projects/glow-masterdata/OprMasterReleaseControl.csv`（CSVファイルは存在しない。サーバーDBで直接管理）

---

## 概要

マスターデータの配信制御情報を管理するテーブル。リリースキーごとに、対応する Git リビジョン・リリース予定日時・クライアントへ配信するデータのハッシュ値を保持する。

マスターデータのリリーススケジュール管理と、クライアントが取得するデータの整合性検証（ハッシュによる変更検知）に使用される運用テーブルである。CSVファイルは存在せず、デプロイ処理によって自動的に登録・更新される。

---

## 全カラム一覧

| カラム名 | 型 | NULL | デフォルト | 説明 |
|---------|-----|------|-----------|------|
| id | varchar(255) | NO | - | UUID |
| release_key | bigint | NO | - | リリースキー |
| git_revision | varchar(255) | NO | - | マスターデータのコミットハッシュ |
| release_at | timestamp | NO | - | リリース予定日時 |
| release_description | varchar(255) | YES | - | リリース内容のメモ（人間向け説明） |
| client_data_hash | varchar(255) | NO | - | クライアント共通データのハッシュ値 |
| zh-Hant_client_i18n_data_hash | varchar(255) | YES | - | クライアント多言語データ（繁体字中国語）のハッシュ値 |
| en_client_i18n_data_hash | varchar(255) | YES | - | クライアント多言語データ（英語）のハッシュ値 |
| ja_client_i18n_data_hash | varchar(255) | YES | - | クライアント多言語データ（日本語）のハッシュ値 |
| client_opr_data_hash | varchar(255) | NO | - | クライアント運用データのハッシュ値 |
| zh-Hant_client_opr_i18n_data_hash | varchar(255) | YES | - | クライアント運用多言語データ（繁体字中国語）のハッシュ値 |
| en_client_opr_i18n_data_hash | varchar(255) | YES | - | クライアント運用多言語データ（英語）のハッシュ値 |
| ja_client_opr_i18n_data_hash | varchar(255) | YES | - | クライアント運用多言語データ（日本語）のハッシュ値 |
| created_at | timestamp | YES | - | 作成日時 |
| updated_at | timestamp | YES | - | 更新日時 |

---

## ハッシュ値の種類と用途

| ハッシュカラム | 対象データ | 説明 |
|-------------|-----------|------|
| client_data_hash | クライアント共通（mst_*系）データ | 言語非依存のマスタデータ全体 |
| ja/en/zh-Hant_client_i18n_data_hash | 各言語版の mst_*_i18n データ | 言語別の固定マスタ多言語データ |
| client_opr_data_hash | クライアント運用（opr_*系）データ | 言語非依存の運用データ全体 |
| ja/en/zh-Hant_client_opr_i18n_data_hash | 各言語版の opr_*_i18n データ | 言語別の運用多言語データ |

---

## 他テーブルとの連携

| 関連テーブル | 関係 | 内容 |
|------------|------|------|
| opr_master_releases | N:1 (release_key経由) | release_key でリリース有効状態と紐付く |
| opr_master_release_versions | 参照 | release_at と git_revision でバージョン管理 |

UNIQUE制約: `(git_revision, release_key, client_data_hash)` の組み合わせで一意

---

## 実データ例（DBスキーマから推定）

**例1: 定期リリースの配信制御レコード**
```
id: <UUID>
release_key: 202509010
git_revision: abc1234567890def...
release_at: 2025-09-24 14:00:00
release_description: 2025年9月 第1回リリース
client_data_hash: <hash>
ja_client_i18n_data_hash: <hash>
en_client_i18n_data_hash: <hash>
zh-Hant_client_i18n_data_hash: <hash>
client_opr_data_hash: <hash>
ja_client_opr_i18n_data_hash: <hash>
```

**例2: 多言語データなし（日本語のみ）のリリース**
```
id: <UUID>
release_key: 202510010
git_revision: def4567890abc123...
release_at: 2025-10-06 15:00:00
release_description: 10月ピックアップガシャ追加
client_data_hash: <hash>
ja_client_i18n_data_hash: <hash>
en_client_i18n_data_hash: NULL
zh-Hant_client_i18n_data_hash: NULL
client_opr_data_hash: <hash>
ja_client_opr_i18n_data_hash: <hash>
```

---

## 設定時のポイント

1. **CSVではなくデプロイスクリプトが自動登録する運用テーブル**: 手動での編集は原則不要。
2. **git_revision でどのバージョンのコードがリリースされたかを追跡できる**: 問題発生時の原因特定に使用。
3. **release_at で時刻指定のリリーススケジュールを管理**: 設定した日時にマスタデータが配信される。
4. **ハッシュ値はクライアントのデータ更新チェックに使用**: クライアントが持つデータのハッシュと比較して更新が必要かを判定する。
5. **データの種類は「共通データ」と「運用データ」に分離**: mst_* 系と opr_* 系を独立して管理することで差分配信を最適化している。
6. **多言語データのハッシュは言語ごとに独立**: 特定言語のデータのみ変更があった場合でも、その言語のクライアントのみが更新を受け取る。
7. **idx_release_at インデックスにより日時検索が高速化**: リリーススケジュールの照会に最適化されている。
