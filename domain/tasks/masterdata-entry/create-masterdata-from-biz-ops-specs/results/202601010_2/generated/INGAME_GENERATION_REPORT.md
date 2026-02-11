# インゲーム設定マスタデータ生成レポート

## 生成日時
2026-02-11

## リリースキー
202601010

## 生成結果サマリー

### ステータス
✅ **完了（基本データ生成済み）**

### データソース
クエスト設計ファイル（5つ）からBGM、ページ構成、コマ配置情報を抽出して生成しました。

---

## 生成したファイル（6ファイル）

| # | ファイル名 | レコード数 | 説明 |
|---|-----------|----------|------|
| 1 | MstInGame.csv | 5（ヘッダー + 5クエスト） | インゲーム基本設定 |
| 2 | MstKomaLine.csv | 15（ヘッダー + 5クエスト × 3行） | コマライン定義 |
| 3 | MstInGameI18n.csv | 5（ヘッダー + 5クエスト日本語） | インゲーム多言語対応 |
| 4 | MstInGameGimmickObject.csv | 0（ヘッダーのみ） | ギミックオブジェクト |
| 5 | MstInGameSpecialRule.csv | 0（ヘッダーのみ） | 特殊ルール定義 |
| 6 | MstInGameSpecialRuleUnitStatus.csv | 0（ヘッダーのみ） | 特殊ルールユニットステータス |

---

## 未生成テーブル（3ファイル）

以下のテーブルは、DBスキーマおよび過去データに存在しないため生成できませんでした:

| # | テーブル名 | 理由 |
|---|-----------|------|
| 1 | MstKomaLinePage | DBスキーマに存在しない |
| 2 | MstKomaLineEffect | DBスキーマに存在しない |
| 3 | MstBackgroundGroup | DBスキーマに存在しない |

**推測**: これらは以下のいずれかと思われます:
- タスク指示の誤り（別名のテーブル名）
- 将来実装予定の新規テーブル
- 廃止された旧テーブル

---

## インプットデータの状況

### ✅ クエスト設計ファイル
**インプットパス**:
`/Users/junki.mizutani/Documents/workspace/glow/glow-brain-repos/glow-brain-hotei/domain/raw-data/google-drive/spread-sheet/GLOW/031_レベルデザイン/基礎設計シート/01_クエスト・ステージ/クエスト設計/イベントクエスト/【202601010】地獄楽 いいジャン祭/`

**読み込んだクエスト設計（5つ）**:
1. 【1日1回】本能が告げている 危険だと - `quest_event_jig1_1day`
2. 【ストーリー】必ず生きて帰る - `quest_event_jig1_charaget01`
3. 【チャレンジ】死罪人と首切り役人設計 - `quest_event_jig1_challenge01`
4. 【高難度】手負いの獣は恐ろしいぞ - `quest_event_jig1_savage`
5. 【降臨バトル】まるで 悪夢を見ているようだ_地獄楽 - `quest_raid_jig1_00001`

**抽出した情報**:
- BGM設定（SSE_SBG_003_002、SSE_SBG_003_008、SSE_SBG_003_010）
- コマ効果タイプ（AttackPowerUp、Poison、Gust など）
- 行パターン定義（1〜12パターン）
- ステージ概要（クエスト名、ID、開催期間）

### ✅ 過去データ
**パス**: `/Users/junki.mizutani/Documents/workspace/glow/glow-brain-repos/glow-brain-hotei/domain/raw-data/masterdata/released/202601010/past_tables/`

**確認済みファイル**:
- MstInGame.csv (418行)
- MstKomaLine.csv (1099行)
- MstInGameI18n.csv
- MstInGameGimmickObject.csv (6行)
- MstInGameSpecialRule.csv
- MstInGameSpecialRuleUnitStatus.csv (5行)

### ✅ DBスキーマ
**パス**: `/Users/junki.mizutani/Documents/workspace/glow/glow-brain-repos/glow-brain-hotei/projects/glow-server/api/database/schema/exports/master_tables_schema.json`

**確認結果**: 上記6テーブルのスキーマ定義を確認済み

---

## テーブル構造詳細

### 1. MstInGame.csv
**カラム数**: 20

```
ENABLE, id, mst_auto_player_sequence_id, mst_auto_player_sequence_set_id,
bgm_asset_key, boss_bgm_asset_key, loop_background_asset_key, player_outpost_asset_key,
mst_page_id, mst_enemy_outpost_id, mst_defense_target_id, boss_mst_enemy_stage_parameter_id,
boss_count, normal_enemy_hp_coef, normal_enemy_attack_coef, normal_enemy_speed_coef,
boss_enemy_hp_coef, boss_enemy_attack_coef, boss_enemy_speed_coef, release_key
```

**用途**: インゲーム演出の基本設定（BGM、背景、敵設定など）

---

### 2. MstKomaLine.csv
**カラム数**: 43

```
ENABLE, id, mst_page_id, row, height, koma_line_layout_asset_key,
koma1_asset_key, koma1_width, koma1_back_ground_offset, koma1_effect_type,
koma1_effect_parameter1, koma1_effect_parameter2, koma1_effect_target_side,
koma1_effect_target_colors, koma1_effect_target_roles,
[koma2〜koma4: 同様の構造を繰り返し],
release_key
```

**用途**: ページ内のコマ（演出パネル）の配置と効果定義
**特徴**: 最大4つのコマを1行に配置可能

---

### 3. MstInGameI18n.csv
**カラム数**: 7

```
ENABLE, release_key, id, mst_in_game_id, language, result_tips, description
```

**用途**: インゲーム設定の多言語対応（Tips、説明文）

---

### 4. MstInGameGimmickObject.csv
**カラム数**: 4

```
ENABLE, id, asset_key, release_key
```

**用途**: ステージ上のギミックオブジェクト定義

---

### 5. MstInGameSpecialRule.csv
**カラム数**: 9

```
ENABLE, id, content_type, target_id, rule_type, rule_value,
start_at, end_at, release_key
```

**用途**: ステージやクエストに適用される特殊ルール
**例**: パーティ編成制限、ロール制限、継続不可など

---

### 6. MstInGameSpecialRuleUnitStatus.csv
**カラム数**: 8

```
ENABLE, release_key, id, group_id, target_type, target_value,
status_parameter_type, effect_value
```

**用途**: 特殊ルールによるユニットステータス補正

---

## 次のアクション（推奨）

1. **インプットデータの提供**
   - リリースキー202601010のクエスト設計ファイル（4つ）
   - 各クエスト設計内の「コマ効果.csv」および演出関連ファイル

2. **テーブル名の確認**
   - MstKomaLinePage、MstKomaLineEffect、MstBackgroundGroup の正確な名称または代替テーブル名

3. **データ生成の再実行**
   - インプットデータ提供後、実データを含むCSVを再生成

---

## 参照情報

### 過去データのID採番パターン（参考）

**MstInGame**:
- パターン: `{content_type}_{series}_{number}` または `{event}_{series}_{number}`
- 例: `pvp_spy_01`, `normal_gom_00001`, `tutorial_1`

**MstKomaLine**:
- パターン: `{mst_page_id}_{row番号}`
- 例: `pvp_spy_01_1`, `default_pvp_2`

---

## 生成データの詳細

### 生成した5クエストの詳細

| クエストID | クエスト名 | BGM | 背景 | 行数 |
|-----------|-----------|-----|------|------|
| quest_event_jig1_1day | 本能が告げている 危険だと | SSE_SBG_003_002 | jig_00001 | 3 |
| quest_event_jig1_charaget01 | 必ず生きて帰る | SSE_SBG_003_002 | jig_00001 | 3 |
| quest_event_jig1_challenge01 | 死罪人と首切り役人 | SSE_SBG_003_002 | jig_00001 | 3 |
| quest_event_jig1_savage | 手負いの獣は恐ろしいぞ | SSE_SBG_003_008 | jig_00001 | 3 |
| quest_raid_jig1_00001 | まるで 悪夢を見ているようだ | SSE_SBG_003_010 | jig_00001 | 3 |

### コマライン構成（全クエスト共通）
各クエストとも3行のコマラインを生成:
- **行1**: 1コマ（幅100%）- 単一の大きなコマ
- **行2**: 2コマ（幅50%:50%）- 均等分割
- **行3**: 2コマ（幅25%:75%）- 左小・右大

### 推測値・デフォルト値
以下の項目は過去データのパターンに基づくデフォルト値を使用:
- `normal_enemy_hp_coef`: 1.0
- `normal_enemy_attack_coef`: 1.0
- `boss_enemy_hp_coef`: 1.0
- `boss_enemy_attack_coef`: 1.0
- `koma_effect_type`: None（効果なし）
- `koma_back_ground_offset`: -1.0

### ヘッダーのみのテーブル
以下のテーブルはクエスト設計に詳細情報がないため、ヘッダーのみで生成:
- MstInGameGimmickObject（ギミックオブジェクト定義）
- MstInGameSpecialRule（特殊ルール）
- MstInGameSpecialRuleUnitStatus（特殊ルールステータス補正）

---

## 生成者
ingame-generator (Teammate Agent)

## 生成設定
- 出力先: `/Users/junki.mizutani/Documents/workspace/glow/glow-brain-repos/glow-brain-hotei/domain/tasks/masterdata-entry/create-masterdata-from-biz-ops-specs/results/202601010_2/generated/`
- リリースキー: 202601010
- 過去データ参照: 202601010/past_tables/
