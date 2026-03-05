# MstStage カラム別データ取得元調査

**調査対象テーブル**: `MstStage`
**調査対象リリースキー**: `202602015`（幼稚園WARS いいジャン祭）
**調査日**: 2026-03-06

---

## 概要

MstStageの全カラムについて、どの設計書・仕様書・ルールから値を取得できるかを調査した結果。
調査対象は202602015の20ステージ（デイリー1・ストーリー2本×6話・チャレンジ4・高難易度3）。

---

## カラム一覧と取得元

| カラム名 | 取得元カテゴリ | 詳細 |
|---------|--------------|------|
| `ENABLE` | 固定値 | 常に `e` |
| `id` | 命名規則 | `event_{作品ID}1_{クエスト種別}_{連番5桁}` |
| `mst_quest_id` | クエスト設計書 | `ステージ概要.csv` の「クエストID」欄 |
| `mst_in_game_id` | id と同一 | 全件 `id` と完全一致。MstInGameに同IDが存在する |
| `stage_number` | 連番 | ステージの話数（1, 2, 3...） |
| `recommended_level` | 運営仕様書 | `02_施策.csv` のステージ定義テーブル「推奨Lv」列 |
| `cost_stamina` | 運営仕様書 | `02_施策.csv` のステージ定義テーブル「スタミナ」列 |
| `exp` | 運営仕様書 | `02_施策.csv` のステージ定義テーブル「獲得リーダーEXP」列 |
| `coin` | 運営仕様書 | `02_施策.csv` のステージ報酬テーブル「クリア」行のコイン列 |
| `prev_mst_stage_id` | 設計書なし（推論） | 1話目は空。2話目以降は前話のIDを自動設定。クエスト間の解放関係（チャレンジ/高難易度はストーリー最終話を参照）は設計書に明示なし |
| `mst_stage_tips_group_id` | 固定値 | 常に `1`（全20件で確認） |
| `auto_lap_type` | クエスト種別ルール | ストーリー＝`AfterClear`、デイリー/チャレンジ/高難易度＝`__NULL__` |
| `max_auto_lap_count` | クエスト種別ルール | ストーリー＝`5`、デイリー/チャレンジ/高難易度＝`1` |
| `sort_order` | stage_number と同一 | 全件 `stage_number` と一致 |
| `asset_key` | クエスト設計書 + 命名規則 | 話数・クエスト種別による。汎用アセット（`general_diamond`等）または施策固有アセット（`event_you1_00001`等）。施策固有IDはアセット管理で別途定義 |
| `mst_stage_limit_status_id` | 固定値 | 常に空（NULL）（全20件で確認） |
| `release_key` | 固定値 | 施策のリリースキー |
| `mst_artwork_fragment_drop_group_id` | 命名規則 | ストーリーのみ設定。`event_you_{a/b}_{連番4桁}`（例: `event_you_a_0001`）。デイリー/チャレンジ/高難易度は `__NULL__` |
| `start_at` | 運営仕様書 | `02_施策.csv` の全体概要「開始日」列（クエスト種別ごとの開始日） |
| `end_at` | 運営仕様書 | `02_施策.csv` の全体概要「終了日」列 |

---

## カテゴリ別まとめ

### ✅ 運営仕様書（`02_施策.csv`）から取得できるカラム

- `recommended_level` ← 「推奨Lv」列
- `cost_stamina` ← 「スタミナ」列
- `exp` ← 「獲得リーダーEXP」列
- `coin` ← ステージ報酬テーブルの「クリア」行・コイン列
- `start_at` / `end_at` ← 全体概要の開始日・終了日

### ✅ クエスト設計書（`ステージ概要.csv`）から取得できるカラム

- `mst_quest_id` ← 「クエストID」欄に直接記載（例: `quest_event_you1_charaget01`）

### ✅ 命名規則・自動生成できるカラム

- `id` ← `event_{作品ID}1_{クエスト種別}_{連番5桁}`
- `mst_in_game_id` ← `id` と同一
- `stage_number` ← 話数の連番
- `sort_order` ← `stage_number` と同一
- `release_key` ← 施策リリースキー

### ✅ クエスト種別ルールで決まるカラム

- `auto_lap_type` ← ストーリー：`AfterClear` / その他：`__NULL__`
- `max_auto_lap_count` ← ストーリー：`5` / その他：`1`
- `mst_artwork_fragment_drop_group_id` ← ストーリーのみ `event_you_{a/b}_{連番4桁}` / その他：`__NULL__`

### ✅ 固定値カラム

- `ENABLE` ← 常に `e`
- `mst_stage_tips_group_id` ← 常に `1`
- `mst_stage_limit_status_id` ← 常に空（NULL）

### ❌ 設計書から明示的に取得できないカラム

| カラム | 現状 | 課題 |
|--------|------|------|
| `prev_mst_stage_id` | 推論で設定 | クエスト間の解放関係（チャレンジ・高難易度がストーリー最終話を参照）が設計書に記載されていない。施策ごとに構造が変わる可能性あり |
| `asset_key`（施策固有分） | 命名規則から推定 | `event_you1_00001` 等の施策固有アセットIDは運営仕様書の「ステージサムネイル」列に名称記載はあるがID形式での記載なし |

---

## 調査時の実データ観察

```
# 全20件のMstStageレコード（release_key=202602015）の構造

# デイリー（1件）
event_you1_1day_00001
  mst_in_game_id = event_you1_1day_00001  ← id と一致
  auto_lap_type  = __NULL__
  max_auto_lap_count = 1
  asset_key = general_diamond
  prev_mst_stage_id = （空）
  mst_artwork_fragment_drop_group_id = __NULL__

# ストーリー1（6話）
event_you1_charaget01_00001〜00006
  auto_lap_type  = AfterClear
  max_auto_lap_count = 5
  mst_artwork_fragment_drop_group_id = event_you_a_0001〜0006
  prev_mst_stage_id: 1話のみ空、2〜6話は前話ID

# ストーリー2（6話）
event_you1_charaget02_00001〜00006
  auto_lap_type  = AfterClear
  max_auto_lap_count = 5
  mst_artwork_fragment_drop_group_id = event_you_b_0001〜0006

# チャレンジ（4話）
event_you1_challenge_00001〜00004
  auto_lap_type  = __NULL__
  max_auto_lap_count = 1
  mst_artwork_fragment_drop_group_id = __NULL__
  prev_mst_stage_id: 1話＝event_you1_charaget01_00006（ストーリー1の最終話）

# 高難易度（3話）
event_you1_savage_00001〜00003
  auto_lap_type  = __NULL__
  max_auto_lap_count = 1
  mst_artwork_fragment_drop_group_id = __NULL__
  prev_mst_stage_id: 1話＝event_you1_charaget01_00006（ストーリー1の最終話）
```

---

## 参照した設計書・仕様書ファイル

| ファイルパス | 用途 |
|------------|------|
| `domain/raw-data/google-drive/spread-sheet/GLOW/080_運営/いいジャン祭（施策）/運営_仕様書/20260202_幼稚園WARS いいジャン祭_仕様書/02_施策.csv` | recommended_level / cost_stamina / exp / coin / start_at / end_at の取得元 |
| `domain/raw-data/google-drive/spread-sheet/GLOW/031_レベルデザイン/基礎設計シート/01_クエスト・ステージ/クエスト設計/イベントクエスト/【202602010】幼稚園WARS いいジャン祭 & バレンタインCP/*/ステージ概要.csv` | mst_quest_id の取得元 |
| `domain/raw-data/masterdata/released/202602015/tables/MstStage.csv` | 実データの観察・パターン検証 |
| `domain/raw-data/masterdata/released/202602015/tables/MstInGame.csv` | mst_in_game_id と id の一致確認 |
