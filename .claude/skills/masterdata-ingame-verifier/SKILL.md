---
name: masterdata-ingame-verifier
description: インゲームマスタデータCSVの実機プレイ品質検証スキル。masterdata-ingame-creatorで生成したCSVが実際にゲームプレイ可能かをフォーマット・ID整合性・ゲームプレイ品質・バランスの4観点で検証します。問題があれば修正提案、問題なければ合格チェックを行います。「インゲーム検証」「インゲームCSV確認」「インゲーム品質チェック」「マスタデータ検証」などのキーワードで使用します。
---

# インゲームマスタデータ検証スキル

## 概要

`masterdata-ingame-creator` で生成したCSVが実機でゲームプレイ可能か、6ステップで検証します。

| フェーズ | 内容 | 手段 |
|---------|------|------|
| A: フォーマット | 列順・型・enum値 | masterdata-csv-validator委譲 |
| B: ID整合性 | FK参照切れ検出 | verify_id_integrity.py |
| C: ゲームプレイ品質 | パラメータ・コマ・シーケンス | DuckDB + Claude分析 |
| D: バランス比較 | 既存データとの乖離 | DuckDB |
| E: アセットキー | 必須キー空白チェック | Claude分析 |

---

## ワークフロー

### Step 0: 検証対象の特定

検証対象ディレクトリを確認する:
```
domain/tasks/masterdata-entry/masterdata-ingame-creator/{タイムスタンプ}_{名称}/generated/
```

MstInGame.csv の `id` 列からステージ種別を自動判定:
- `dungeon_boss_*` → dungeon_boss
- `dungeon_normal_*` → dungeon_normal
- `event_*` → event系
- `raid_*` → raid

存在するCSVファイルを列挙し、必須・オプションを仕分け:

**必須**（常に検証）:
1. MstEnemyStageParameter.csv
2. MstEnemyOutpost.csv
3. MstPage.csv
4. MstKomaLine.csv
5. MstAutoPlayerSequence.csv
6. MstInGame.csv

**オプション**（存在すれば検証）:
7. MstInGameI18n.csv
8. MstInGameSpecialRule.csv
9. MstStage.csv
10. MstStageEventSetting.csv
11. MstStageEventReward.csv

---

### Step 1: フォーマット検証（masterdata-csv-validator委譲）

各CSVに対して `validate_all.py` を実行:
```bash
for csv in {generated_dir}/*.csv; do
  python .claude/skills/masterdata-csv-validator/scripts/validate_all.py --csv "$csv"
done
```

- `valid: false` が1件でもあれば即時報告し、フォーマット修正を先行させる
- 全ファイル `valid: true` になってから Step 2 へ進む

---

### Step 2: ID整合性チェック

`verify_id_integrity.py` を実行してFKチェック:
```bash
python .claude/skills/masterdata-ingame-verifier/scripts/verify_id_integrity.py \
  --dir {generated_dir}
```

チェック項目:
- `ingame_sequence_fk`: MstInGame.mst_auto_player_sequence_set_id → 全MstAutoPlayerSequence行のsequence_set_idに存在するか
- `ingame_page_fk`: MstInGame.mst_page_id → MstPage.id
- `ingame_outpost_fk`: MstInGame.mst_enemy_outpost_id → MstEnemyOutpost.id
- `ingame_boss_fk`: MstInGame.boss_mst_enemy_stage_parameter_id → MstEnemyStageParameter.id（空欄は許可）
- `sequence_set_id_consistency`: 全MstAutoPlayerSequenceのsequence_set_idが同一値か
- `sequence_action_value_fk`: action_type=SummonEnemy の action_value → MstEnemyStageParameter.id

エラーがあれば CRITICAL として報告し修正を求める。

---

### Step 3: ゲームプレイ品質チェック

#### 3-1. 敵パラメータの妥当性

エネミーステータスシート（`domain/knowledge/masterdata/in-game/エネミーステータスシート.md`）の基準値と比較。
詳細基準: [balance-guidelines.md](references/balance-guidelines.md)

DuckDBでパラメータ確認:
```sql
SELECT id, mst_enemy_character_id, character_unit_kind, role_type,
       hp, attack_power, move_speed, well_distance
FROM read_csv('{generated}/MstEnemyStageParameter.csv', AUTO_DETECT=TRUE);
```

#### 3-2. コマ配置の整合性

MstKomaLineのカラム構造: `row`（行番号）、`height`、`koma1_width〜koma4_width`（コマごとの幅）

```sql
-- 各行のコマ幅合計が1.0か確認（komaX_width を合算）
SELECT row,
       ROUND(COALESCE(CAST(koma1_width AS DOUBLE), 0)
           + COALESCE(CAST(koma2_width AS DOUBLE), 0)
           + COALESCE(CAST(koma3_width AS DOUBLE), 0)
           + COALESCE(CAST(koma4_width AS DOUBLE), 0), 3) AS total_width
FROM read_csv('{generated}/MstKomaLine.csv', AUTO_DETECT=TRUE)
ORDER BY row;

-- 行数確認（参考）
SELECT COUNT(DISTINCT row) AS row_count
FROM read_csv('{generated}/MstKomaLine.csv', AUTO_DETECT=TRUE);
```

期待値:
- 各行のコマ幅合計 = 1.0（CRITICAL）
- 行数: ステージ種別による（dungeon_boss=1行、dungeon_normal=3行が設計仕様）

> **注意**: height合計に固定制限はない。既存データでは 1.1（2行コマ）、1.65（3行コマ）など多様な値をとる。

#### 3-3. シーケンスの合理性

```sql
-- 召喚数の確認（SummonEnemy行数 × summon_count列の積み上げ）
SELECT action_type, COUNT(*) AS row_count
FROM read_csv('{generated}/MstAutoPlayerSequence.csv', AUTO_DETECT=TRUE)
GROUP BY action_type;

-- ElapsedTimeの時系列確認（単調増加か）
WITH seq AS (
  SELECT
    sequence_element_id,
    condition_value,
    LAG(CAST(condition_value AS DOUBLE)) OVER (ORDER BY CAST(sequence_element_id AS INTEGER)) AS prev_condition_value
  FROM read_csv('{generated}/MstAutoPlayerSequence.csv', AUTO_DETECT=TRUE)
  WHERE condition_type = 'ElapsedTime'
)
SELECT sequence_element_id, condition_value, prev_condition_value,
       CAST(condition_value AS DOUBLE) < prev_condition_value AS is_time_regression
FROM seq
WHERE is_time_regression = true;
```

> **注意**: 召喚数に固定制限はない。既存データでは SummonEnemy行数が1〜53の幅がある。

#### 3-4. ステージ種別固有ルール

| ステージ種別 | MstEnemyOutpost.hp | コマ行数 | 追加チェック |
|-----------|-------------------|---------|------------|
| dungeon_boss | **1,000**（固定） | **1行** | — |
| dungeon_normal | **100**（固定） | **3行** | — |
| event_challenge | 任意 | 任意 | MstInGameSpecialRule に SpeedAttack が存在するか |
| raid | 1,000,000 | 任意 | MstEnemyOutpost.is_damage_invalidation = 1 |

```sql
-- MstEnemyOutpost.hp確認
SELECT id, hp, is_damage_invalidation FROM read_csv('{generated}/MstEnemyOutpost.csv', AUTO_DETECT=TRUE);
```

#### 3-5. ボス設定の二重チェック

```sql
-- ボスID設定確認
SELECT boss_mst_enemy_stage_parameter_id, boss_count
FROM read_csv('{generated}/MstInGame.csv', AUTO_DETECT=TRUE);

-- ボスがInitialSummonで召喚設定されているか
SELECT action_type, action_value, condition_type
FROM read_csv('{generated}/MstAutoPlayerSequence.csv', AUTO_DETECT=TRUE)
WHERE condition_type = 'InitialSummon';
```

`boss_mst_enemy_stage_parameter_id` が設定されている場合、MstAutoPlayerSequenceに InitialSummon でそのIDが含まれているか確認。

---

### Step 4: バランス比較（既存データとの比較）

```sql
-- 同種ステージ（dungeon_normal等）の既存パラメータ分布と比較
SELECT
  character_unit_kind,
  role_type,
  MIN(hp) AS hp_min, AVG(hp) AS hp_avg, MAX(hp) AS hp_max,
  MIN(attack_power) AS atk_min, AVG(attack_power) AS atk_avg, MAX(attack_power) AS atk_max
FROM read_csv('projects/glow-masterdata/MstEnemyStageParameter.csv', AUTO_DETECT=TRUE)
GROUP BY character_unit_kind, role_type;
```

生成データのパラメータが既存データの ±5倍範囲内か確認。
範囲外の場合は WARNING（意図的な場合あり）として報告。

---

### Step 5: アセットキー形式チェック

以下のアセットキーが空欄でないか確認:

```sql
-- MstInGame
SELECT bgm_asset_key, boss_bgm_asset_key FROM read_csv('{generated}/MstInGame.csv', AUTO_DETECT=TRUE);

-- MstEnemyOutpost
SELECT artwork_asset_key FROM read_csv('{generated}/MstEnemyOutpost.csv', AUTO_DETECT=TRUE);

-- MstKomaLine
SELECT koma_asset_key FROM read_csv('{generated}/MstKomaLine.csv', AUTO_DETECT=TRUE);
```

---

### Step 6: 最終判定と報告

全ステップの結果を集約して以下の形式で報告する。

#### 合格時

```markdown
## インゲームマスタデータ検証レポート
- 対象: {ingame_id} ({stage_type})
- 検証日時: {date}

### ✅ 確実に実機プレイで問題ありません

| フェーズ | 結果 | 備考 |
|---------|------|------|
| A: フォーマット | ✅ OK | 全N ファイルクリア |
| B: ID整合性 | ✅ OK | 全FK参照一致 |
| C: ゲームプレイ品質 | ✅ OK | |
| D: バランス比較 | ✅ OK | 既存{コンテンツ}系と同等範囲 |
| E: アセットキー | ✅ OK | |
```

#### 問題あり時

```markdown
## インゲームマスタデータ検証レポート
- 対象: {ingame_id} ({stage_type})
- 検証日時: {date}

### ❌ 問題があります（修正が必要です）

#### [CRITICAL] {問題名}
- 対象: {テーブル.カラム} = {値}
- 基準: {正常値の説明}
- 修正提案: {具体的な修正方法}

#### [WARNING] {問題名}
- 対象: {テーブル.カラム} = {値}
- 確認事項: {意図的かどうか確認すべき点}
```

---

## 重要ファイルパス

| 用途 | パス |
|------|------|
| フォーマット検証スクリプト | `.claude/skills/masterdata-csv-validator/scripts/validate_all.py` |
| ID整合性スクリプト | `.claude/skills/masterdata-ingame-verifier/scripts/verify_id_integrity.py` |
| エネミーステータスシート | `domain/knowledge/masterdata/in-game/エネミーステータスシート.md` |
| ステージ種別パターン | `.claude/skills/masterdata-ingame-creator/references/stage-type-patterns.md` |
| 既存MstEnemyCharacter | `projects/glow-masterdata/MstEnemyCharacter.csv` |
| 既存MstEnemyStageParameter | `projects/glow-masterdata/MstEnemyStageParameter.csv` |

## リファレンス

- [balance-guidelines.md](references/balance-guidelines.md) — パラメータバランス基準
- [verification-checklist.md](references/verification-checklist.md) — フェーズ別チェックリスト
- [verification-duckdb-queries.md](references/verification-duckdb-queries.md) — DuckDBクエリ集
- [common-issues.md](references/common-issues.md) — よくある問題と修正提案
