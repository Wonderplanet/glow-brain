# MstPvp 詳細説明

> CSVパス: `projects/glow-masterdata/MstPvp.csv`
> i18n CSVパス: `projects/glow-masterdata/MstPvpI18n.csv`

---

## 概要

`MstPvp` は**PvP（プレイヤー対戦）の週ごとの基本設定テーブル**。毎週開催されるPvPシーズンごとに、1日あたりの挑戦回数制限・使用するインゲーム設定・初期バトルポイントなどを定義する。

IDの命名規則として「西暦4桁 + 週番号2桁」の形式（例: `2025039` = 2025年39週）が採用されており、週次での管理が基本となっている。`default_pvp` というIDは汎用デフォルト設定として別途存在する。

`MstPvpI18n` テーブルと連携してPvP名・説明文を多言語対応する（説明文はコマ効果情報なども含む）。

### ゲームプレイへの影響

- `max_daily_challenge_count`: アイテム消費なしで1日に挑戦できる回数上限
- `max_daily_item_challenge_count`: アイテム消費ありで追加挑戦できる回数上限
- `item_challenge_cost_amount`: 追加挑戦1回あたりの消費アイテム数
- `initial_battle_point`: シーズン開始時のバトルポイント（通常200、特殊シーズンは1000など）
- `ranking_min_pvp_rank_class`: ランキングに含む最小ランク区分（この区分以上のプレイヤーがランキング対象）

---

## 全カラム一覧

### mst_pvps カラム一覧

| カラム名 | 型 | NULL許容 | デフォルト値 | 説明 |
|---------|----|---------|-----------|----|
| `ENABLE` | varchar | - | - | CSVの有効フラグ。`e` = 有効 |
| `id` | varchar(16) | 不可 | - | 主キー。`{年4桁}{週番号2桁}` 形式（例: `2025039`） |
| `release_key` | bigint | 不可 | 1 | リリースキー |
| `ranking_min_pvp_rank_class` | enum('Bronze','Silver','Gold','Platinum') | 可 | NULL | ランキングに含む最小PVPランク区分 |
| `max_daily_challenge_count` | int unsigned | 不可 | 0 | 1日のアイテム消費なし挑戦可能回数 |
| `max_daily_item_challenge_count` | int unsigned | 不可 | 0 | 1日のアイテム消費あり挑戦可能回数 |
| `item_challenge_cost_amount` | int unsigned | 不可 | 0 | アイテム消費あり挑戦時の消費アイテム数 |
| `initial_battle_point` | int | 不可 | - | 初期バトルポイント |
| `mst_in_game_id` | varchar(255) | 不可 | '' | 使用するインゲーム設定ID（`mst_in_games.id`） |

### MstPvpI18n カラム一覧

| カラム名 | 型 | NULL許容 | デフォルト値 | 説明 |
|---------|----|---------|-----------|----|
| `ENABLE` | varchar | - | - | CSVの有効フラグ。`e` = 有効 |
| `id` | varchar(255) | 不可 | - | 主キー |
| `release_key` | bigint | 不可 | 1 | リリースキー |
| `mst_pvp_id` | varchar(16) | 不可 | - | 親テーブルID（`mst_pvps.id`） |
| `language` | enum('ja') | 不可 | 'ja' | 言語コード |
| `name` | varchar(255) | 可 | NULL | PvP名（多くはNULL） |
| `description` | varchar(255) | 不可 | '' | PvP説明文（基本情報・コマ効果情報を含む） |

---

## PvpRankClassType（PVPランク区分）

| 値 | 説明 |
|----|------|
| `Bronze` | ブロンズ（最低ランク） |
| `Silver` | シルバー |
| `Gold` | ゴールド |
| `Platinum` | プラチナ（最高ランク） |

---

## 命名規則 / IDの生成ルール

- `id`: `{西暦4桁}{週番号2桁}` 形式（例: 2025年39週 → `2025039`）
  - 特例: `default_pvp` はデフォルト設定用の特別ID
- `MstPvpI18n.id`: `{mst_pvp_id}` と同一（例: `2025039`）
  - ただし多言語展開する場合は `{mst_pvp_id}_{言語}` も考えられる

---

## 他テーブルとの連携

```
MstPvp
  └─ mst_in_game_id → MstInGame.id（PvPで使用するインゲームの設定）
  └─ id → MstPvpI18n.mst_pvp_id（多言語名称・説明文）
  └─ id（週番号）→ OprPvp等で週ごとのシーズン管理と紐付く
```

---

## 実データ例

**パターン1: 通常シーズン（初期BP 200）**

| ENABLE | id | release_key | ranking_min_pvp_rank_class | max_daily_challenge_count | max_daily_item_challenge_count | item_challenge_cost_amount | mst_in_game_id | initial_battle_point |
|--------|-----|-------------|---------------------------|--------------------------|-------------------------------|---------------------------|----------------|---------------------|
| e | 2025039 | 202509010 | Bronze | 10 | 10 | 1 | pvp_202509010_01 | 200 |
| e | 2025043 | 202510010 | Bronze | 10 | 10 | 1 | pvp_dan_01 | 200 |

**パターン2: 特殊シーズン（初期BP 1000）**

| ENABLE | id | release_key | ranking_min_pvp_rank_class | max_daily_challenge_count | max_daily_item_challenge_count | item_challenge_cost_amount | mst_in_game_id | initial_battle_point |
|--------|-----|-------------|---------------------------|--------------------------|-------------------------------|---------------------------|----------------|---------------------|
| e | 2025046 | 202511010 | Bronze | 10 | 10 | 1 | pvp_mag_01 | 1000 |

**I18nデータ例（コマ効果情報を含む説明文）**

| id | mst_pvp_id | language | name | description |
|----|-----------|----------|------|-------------|
| 2025041 | 2025041 | ja | NULL | 【基本情報】\n3段のステージで戦うぞ！\n...\n【コマ効果情報】\n攻撃UPコマが登場するぞ! |
| 2025043 | 2025043 | ja | NULL | 【基本情報】\n3段のステージで戦うぞ！\n...\n【コマ効果情報】\n攻撃DOWNコマが登場するぞ! |

---

## 設定時のポイント

1. **idは週番号ベースで採番**: `{年4桁}{週番号2桁}` 形式を厳守。ISO週番号（月曜始まり）に基づく
2. **mst_in_game_idはPvP専用のインゲームを参照**: ステージ構成やコマ効果を変えたい場合は新しい `MstInGame` を作成してそのIDを指定する
3. **initial_battle_pointの設計**: 通常は200だが、特殊シーズン（スタート時に有利な状態にするなど）では1000を設定するケースがある
4. **I18nのdescriptionにコマ効果情報を記載**: 説明文にそのPvPのコマ効果の種類を記述することで、プレイヤーに事前情報を提供する。説明文の書式を統一する
5. **ranking_min_pvp_rank_classは基本Bronze**: ほぼすべてのシーズンでBronzeが設定されており、ブロンズ以上のプレイヤー全員がランキング対象となる
6. **毎週新しいレコードを追加**: PvPシーズンは毎週更新されるため、週ごとに新しいIDのレコードを事前に準備しておく
