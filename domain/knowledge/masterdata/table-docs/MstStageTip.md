# MstStageTip 詳細説明

> CSVパス: CSVファイルが確認できなかった（`MstStageTip.csv` 未存在）

---

## 概要

ステージのロード中またはプレイ中に表示するTips（ヒント）テキストの設定を管理するテーブル。`mst_stage_tips_group_id` でグループを作り、同一グループ内の複数の言語テキストをまとめて管理する構造になっている。

- `mst_stage_tips_group_id` でTipsのグループを管理。同一ステージで複数の Tips を表示したい場合、同じグループIDを付けた複数レコードを設定できる
- `language` カラムで多言語対応を行う（同一グループに日本語・英語など）
- クライアントクラスは `MstStageTipsTemplateI18nData` という名前になっており、Tipsテンプレートとして扱われている
- 現在のCSVには対応ファイルが存在しないため、未使用またはマスタデータ管理外の可能性がある

---

## 全カラム一覧

| カラム名 | 型 | NULL | デフォルト | 説明 |
|---|---|---|---|---|
| id | varchar(255) | 不可 | - | UUID |
| language | varchar(255) | 不可 | - | 言語設定（例: `ja`・`en`） |
| mst_stage_tips_group_id | varchar(255) | 不可 | - | TipsグループID（同一ステージのTips群を束ねる） |
| title | varchar(255) | 不可 | - | Tipsのタイトル |
| description | varchar(255) | 不可 | - | Tipsの本文 |
| release_key | bigint unsigned | 不可 | `1` | リリースキー |

---

## 命名規則 / IDの生成ルール

- `id` は UUID 形式
- `mst_stage_tips_group_id` は任意のグループ識別子。同じグループIDを持つレコードが一つのTipsセットを構成する

---

## 他テーブルとの連携

| 関連テーブル | 説明 |
|---|---|
| `mst_stages` | ステージ設定側から `mst_stage_tips_group_id` を参照してステージ表示Tipsを決定する想定 |

---

## 実データ例

CSVファイルが存在しないため実データを確認できない。

DBスキーマ定義より、以下のような設定が想定される:

```
id                                   | language | mst_stage_tips_group_id | title                    | description                      | release_key
xxxxxxxx-xxxx-xxxx-xxxx-xxxxxxxxxxxx | ja       | tips_group_basic         | コマを召喚しよう!        | バトル中はキャラコマを召喚して... | 202509010
yyyyyyyy-yyyy-yyyy-yyyy-yyyyyyyyyyyy | en       | tips_group_basic         | Summon your units!       | During battle, summon your...     | 202509010
```

---

## 設定時のポイント

1. **グループIDで複数Tipsを管理**: 同一ステージに複数のTipsを表示する場合、同じ `mst_stage_tips_group_id` を付けた複数レコードを作成する
2. **多言語対応**: 同一グループIDに対して各言語分のレコードを追加することで多言語Tipsを実現できる
3. **クライアントクラス**: クライアントでは `MstStageTipsTemplateI18nData`（`GLOW.Core.Data.Data`名前空間）として定義されており、`id`・`language`（`Language` enum）・`description` が配信される。DBスキーマの `title` フィールドはクライアントデータには含まれていない点に注意
4. **CSVファイル未確認**: 2026年3月時点でマスタデータCSVに `MstStageTip.csv` は存在しない。このテーブルは機能が未実装または将来の拡張用として定義されている可能性がある
5. **テーブルとCSVのネーミング差異**: DBスキーマ名は `mst_stage_tips` だが、クライアントクラスは `MstStageTipsTemplateI18n` という名前になっており、「テンプレートi18n」として設計されている点に注意
