# MstMissionBeginnerPromptPhraseI18n 詳細説明

> CSVパス: `projects/glow-masterdata/MstMissionBeginnerPromptPhraseI18n.csv`

---

## 1. 概要

`MstMissionBeginnerPromptPhraseI18n` は**初心者ミッションUIの煽り文言を期間別に設定するテーブル**。初心者ミッション画面に表示される「今すぐ始めよう！」「残りN日！」などのプロモーション用テキストを、表示期間（`start_at` / `end_at`）とセットで管理する。

このテーブルは i18n 専用テーブルであり、ペアになるメインテーブル（`mst_mission_beginner_prompt_phrases`）は存在しない。多言語テキストと表示期間を1つのテーブルで完結している。

### ゲームプレイへの影響

- 初心者ミッションUI上部などに表示される煽り文言の内容と表示期間を制御する
- 期間外は表示されないため、キャンペーン期間に合わせた文言切り替えが可能
- `prompt_phrase_text` はリッチテキストやHTMLタグが使用できる（text型）

---

## 2. 全カラム一覧

| カラム名 | 型 | NULL許容 | デフォルト値 | 説明 |
|---------|----|----|------|------|
| `ENABLE` | varchar | - | - | CSVの有効フラグ。`e` = 有効 |
| `id` | varchar(255) | 不可 | - | 主キー |
| `language` | enum('ja') | 不可 | - | 言語コード |
| `prompt_phrase_text` | text | 不可 | - | 表示する煽り文言テキスト |
| `start_at` | timestamp | 不可 | - | 文言表示の開始日時（UTC） |
| `end_at` | timestamp | 不可 | - | 文言表示の終了日時（UTC） |
| `release_key` | bigint | 不可 | 1 | リリースキー |

---

## 5. 他テーブルとの連携

このテーブルは他テーブルとの外部キー制約を持たない独立テーブル。初心者ミッション全体の設定（`mst_mission_beginners`）とは概念的に紐づくが、直接の外部キーは存在しない。

---

## 6. 実データ例

### 現行設定

| id | language | prompt_phrase_text | start_at | end_at | release_key |
|---|---|---|---|---|---|
| beginner_mission_1 | ja | 1500 | 2024-04-30 15:00:00 | 2037-12-31 23:59:59 | 202509010 |

- `prompt_phrase_text` に数値 `1500` が設定されており、ポイント目標値などを表示するためのテキストとして使用されている
- `end_at` が `2037-12-31` と非常に長い期間に設定されており、事実上恒久表示として運用

---

## 7. 設定時のポイント

- `start_at` / `end_at` は日本時間（JST）から9時間引いた UTC で設定する（例: JST 15:00 = UTC 06:00）
- `end_at` を現行の実装（2037年）のように遠い未来にすることで恒久表示として運用できる
- 期間が重複する複数レコードを設定した場合の優先順位はクライアント実装依存なので確認が必要
- `language` は現状 `ja` のみサポート
- `prompt_phrase_text` は text 型なので長いテキストや改行を含むリッチなコンテンツを設定できる
- クライアントクラス: `MstMissionBeginnerPromptPhraseI18nData.cs`
