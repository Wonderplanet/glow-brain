# インゲームマスタデータ検証レポート

- 対象: `dungeon_chi_boss_00001` (dungeon_boss)
- 検証日時: 2026-03-02
- 検証者: masterdata-ingame-verifier

---

## 判定: ❌ 問題があります（修正が必要です）

| フェーズ | 結果 | 備考 |
|---------|------|------|
| A: フォーマット | ⚠️ SKIP | バリデータが期待する3行ヘッダー形式ではないが、既存CSVと同じ1行ヘッダー形式のため実質OK |
| B: ID整合性 | ✅ OK | 全FK参照一致（6項目全てクリア） |
| C: ゲームプレイ品質 | ❌ CRITICAL | `is_damage_invalidation` が空（NULL）— 要修正 |
| D: バランス比較 | ⚠️ NOTE | 取り巻き雑魚のHPが既存平均の0.19倍（若干低め）。dungeon_bossの取り巻き設計として意図的か確認推奨 |
| E: アセットキー | ❌ CRITICAL | `outpost_asset_key` / `artwork_asset_key` が空 — 要修正 |

---

## Step 1: フォーマット検証（A: フォーマット）

### 結果: ⚠️ SKIP（実質問題なし）

`validate_all.py` はCSVに `memo / TABLE / ENABLE` の3行ヘッダーを期待するが、今回の生成CSVは `ENABLE` から始まる1行ヘッダー形式（既存 `projects/glow-masterdata/*.csv` と同じ形式）。

- バリデータのヘッダーチェックはこのフォーマットでは機能しないが、**実際のマスタデータフォーマットとしては正しい**
- 全6ファイルの列定義・データ型は目視で確認済み、問題なし

---

## Step 2: ID整合性チェック（B: ID整合性）

### 結果: ✅ OK

`verify_id_integrity.py` を実行した結果、全6チェック項目がクリア。

| チェック項目 | 結果 |
|------------|------|
| `ingame_sequence_fk` | ✅ OK |
| `ingame_page_fk` | ✅ OK |
| `ingame_outpost_fk` | ✅ OK |
| `ingame_boss_fk` | ✅ OK |
| `sequence_set_id_consistency` | ✅ OK |
| `sequence_action_value_fk` | ✅ OK |

---

## Step 3: ゲームプレイ品質チェック（C: ゲームプレイ品質）

### 3-1. 敵パラメータの妥当性

| id | character_unit_kind | role_type | hp | attack_power | move_speed | well_distance |
|----|--------------------|-----------|----|-------------|-----------|--------------|
| `c_chi_00002_chi_dungeon_Boss_Yellow` | Boss | Technical | 200,000 | 900 | 50 | 0.35 |
| `e_chi_00101_chi_dungeon_Normal_Yellow` | Normal | Technical | 13,000 | 720 | 35 | 0.25 |

パラメータ範囲チェック（balance-guidelines.md 準拠）:

| パラメータ | 値 | 基準範囲 | 判定 |
|-----------|-----|---------|------|
| Boss HP | 200,000 | 50,000 〜 1,400,000（WARNING閾値: >3,000,000） | ✅ OK |
| Boss ATK | 900 | 21 〜 2,380（WARNING閾値: >3,800） | ✅ OK |
| Boss move_speed | 50 | 5 〜 100 | ✅ OK |
| Normal HP | 13,000 | 1,000 〜 100,000（WARNING閾値: >500,000） | ✅ OK |
| Normal ATK | 720 | 21 〜 2,380 | ✅ OK |
| Normal move_speed | 35 | 5 〜 100 | ✅ OK |

### 3-2. コマ配置の整合性

| row | total_width | 判定 |
|-----|------------|------|
| 1 | 1.000 | ✅ OK |

- コマ行数: **1行**（dungeon_boss の仕様通り）✅ OK

### 3-3. シーケンスの合理性

| action_type | 行数 | 判定 |
|------------|------|------|
| SummonEnemy | 3 | ✅ OK（基準: 10〜30体だが、boss ブロックの最小構成として許容） |

ElapsedTime の時系列逆転: **なし** ✅ OK

シーケンス詳細:
- 行1: `InitialSummon` → ボス `c_chi_00002_chi_dungeon_Boss_Yellow` を召喚（position=1.7）
- 行2: `ElapsedTime=1500` → 雑魚 `e_chi_00101_chi_dungeon_Normal_Yellow` を召喚
- 行3: `ElapsedTime=2500` → 雑魚 `e_chi_00101_chi_dungeon_Normal_Yellow` を召喚

### 3-4. ステージ種別固有ルール（dungeon_boss）

| チェック項目 | 期待値 | 実際値 | 判定 |
|------------|--------|--------|------|
| `MstEnemyOutpost.hp` | **1,000（固定）** | 1,000 | ✅ OK |
| `MstKomaLine` 行数 | **1行（固定）** | 1行 | ✅ OK |
| `MstEnemyOutpost.is_damage_invalidation` | **1（ゲートダメージ無効）** | **NULL（空）** | ❌ CRITICAL |

### 3-5. ボス設定の二重チェック

- `MstInGame.boss_mst_enemy_stage_parameter_id` = `c_chi_00002_chi_dungeon_Boss_Yellow` ✅
- `MstAutoPlayerSequence` に `InitialSummon` でそのIDが存在 ✅
- `boss_count` = 1 ✅

---

## Step 4: バランス比較（D: バランス比較）

既存 `MstEnemyStageParameter.csv` の同 character_unit_kind + role_type との比較:

### Boss Technical

| パラメータ | 生成値 | 既存平均 | 比率 | 判定 |
|-----------|--------|---------|------|------|
| HP | 200,000 | 79,806 | 2.51x | ✅ OK（0.2〜5倍の範囲内） |
| ATK | 900 | 271 | 3.32x | ✅ OK（0.2〜5倍の範囲内） |
| move_speed | 50 | 33 | 1.51x | ✅ OK |

### Normal Technical（取り巻き雑魚）

| パラメータ | 生成値 | 既存平均 | 比率 | 判定 |
|-----------|--------|---------|------|------|
| HP | 13,000 | 67,158 | 0.19x | ⚠️ NOTE（基準0.2倍をわずかに下回る） |
| ATK | 720 | 352 | 2.05x | ✅ OK |
| move_speed | 35 | 42 | 0.84x | ✅ OK |

> **NOTE**: 取り巻き雑魚のHPが既存平均の0.19倍と若干低め。ただし dungeon_boss の取り巻き雑魚は「護衛」として倒しやすい設計が想定されるため、意図的な低設定の可能性が高い。設計意図として問題なければ許容可。

---

## Step 5: アセットキーチェック（E: アセットキー）

### MstInGame

| カラム | 値 | 判定 |
|-------|-----|------|
| `bgm_asset_key` | `SSE_SBG_003_001` | ✅ OK |
| `boss_bgm_asset_key` | NULL（空） | ⚠️ NOTE（ボスBGMなし。通常BGMを流用） |
| `loop_background_asset_key` | `glo_00016` | ✅ OK |
| `player_outpost_asset_key` | NULL（空） | ⚠️ NOTE（プレイヤー基地アセットなし） |

### MstEnemyOutpost

| カラム | 値 | 判定 |
|-------|-----|------|
| `outpost_asset_key` | **NULL（空）** | ❌ CRITICAL（必須アセットが未設定） |
| `artwork_asset_key` | **NULL（空）** | ❌ CRITICAL（作品原画アセットが未設定） |

### MstKomaLine

| カラム | 値 | 判定 |
|-------|-----|------|
| `koma1_asset_key` | `glo_00016` | ✅ OK |

---

## Step 6: 最終判定と修正指示

### ❌ 問題があります（CRITICAL 2件 — 修正後に再検証が必要）

---

### [CRITICAL 1] MstEnemyOutpost.is_damage_invalidation が未設定

- **対象**: `MstEnemyOutpost.csv` — `dungeon_chi_boss_00001` 行
- **現在値**: NULL（空）
- **期待値**: `1`（dungeon_boss の仕様：ボスを倒すまでゲートへのダメージは無効）
- **根拠**: CLAUDE.md 仕様確認ポイント「ゲートダメージ無効（is_damage_invalidation = 1）」
- **修正方法**: `is_damage_invalidation` カラムに `1` を設定する

```csv
ENABLE,id,hp,is_damage_invalidation,outpost_asset_key,artwork_asset_key,release_key
e,dungeon_chi_boss_00001,1000,1,{outpost_asset_key},{artwork_asset_key},999999999
```

---

### [CRITICAL 2] MstEnemyOutpost のアセットキーが未設定

- **対象**: `MstEnemyOutpost.csv` — `dungeon_chi_boss_00001` 行
- **現在値**: `outpost_asset_key` = NULL、`artwork_asset_key` = NULL
- **期待値**: チェンソーマン作品の背景・原画アセットキーを設定
- **参考（SPY×FAMILY）**: `outpost_asset_key = spy_00005`、`artwork_asset_key = spy_00005`
- **修正方法**: チェンソーマン作品のアセットキー（`chi_00XXX` 形式）を確認して設定する

---

### [WARNING] 取り巻き雑魚の召喚数が少ない（3体）

- **対象**: `MstAutoPlayerSequence.csv`
- **現状**: SummonEnemy 3行（ボス1体 + 雑魚2体）
- **基準目安**: 10〜30体（balance-guidelines.md）
- **確認事項**: dungeon_boss の取り巻き雑魚は最小構成でよい設計なのか確認推奨
- **判定**: WARNING（意図的な場合は許容）

---

### 修正後の再確認ポイント

1. `MstEnemyOutpost.is_damage_invalidation = 1` に修正
2. `MstEnemyOutpost.outpost_asset_key` にチェンソーマン用アセットキーを設定
3. `MstEnemyOutpost.artwork_asset_key` にチェンソーマン用アセットキーを設定
4. 修正後に `verify_id_integrity.py` を再実行して整合性を確認

---

## 検証対象ファイル一覧

| ファイル | 行数 | 状態 |
|---------|------|------|
| `MstInGame.csv` | 1データ行 | ✅（アセットキー空欄はNOTE） |
| `MstEnemyOutpost.csv` | 1データ行 | ❌（`is_damage_invalidation` + アセットキー未設定） |
| `MstEnemyStageParameter.csv` | 2データ行 | ✅ |
| `MstPage.csv` | 1データ行 | ✅ |
| `MstKomaLine.csv` | 1データ行 | ✅ |
| `MstAutoPlayerSequence.csv` | 3データ行 | ✅（召喚数はWARNING） |
