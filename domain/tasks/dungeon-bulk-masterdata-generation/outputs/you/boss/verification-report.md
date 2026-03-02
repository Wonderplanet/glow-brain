# 検証レポート: dungeon_you_boss_00001

- **検証日時**: 2026-03-02
- **対象ディレクトリ**: `domain/tasks/dungeon-bulk-masterdata-generation/outputs/you/boss/generated/`
- **ステージ種別**: dungeon_boss
- **インゲームID**: `dungeon_you_boss_00001`

---

## 総合判定: PASS

全チェック項目を通過。dungeon_boss 固有の必須要件もすべて満たしています。

---

## Step 1: フォーマット検証

| ファイル | 結果 | 備考 |
|---------|------|------|
| MstAutoPlayerSequence.csv | PASS | 7行（ヘッダー含む） |
| MstEnemyOutpost.csv | PASS | 4行（ヘッダー含む） |
| MstEnemyStageParameter.csv | PASS | 5行（ヘッダー含む） |
| MstInGame.csv | PASS（擬似エラーあり） | 詳細は下記参照 |
| MstKomaLine.csv | PASS | 4行（ヘッダー含む） |
| MstPage.csv | PASS | 4行（ヘッダー含む） |

### MstInGame.csv のカラム数に関する注記

バリデータが「カラム数不一致（期待: 19, 実際: 21）」と報告しましたが、これは **擬似エラー（誤検知）** です。

- DBスキーマ（`mst_in_games`）は 19 カラム
- boss 用 CSV は i18n カラム（`result_tips.ja`, `description.ja`）を追加した 21 カラム
- 他の boss 生成済み作品（chi, dan 等）も同じ 21 カラム形式を採用しており、正規フォーマットです

---

## Step 2: ID整合性チェック

> 注: `verify_id_integrity.py` スクリプトがカラム名解決に失敗するバグが確認されています（CSV のヘッダー行が DuckDB のメタデータ行と競合）。手動で DuckDB クエリにて確認しました。

### MstInGame → MstPage FK

- `mst_page_id`: `dungeon_you_boss_00001`
- MstPage に `dungeon_you_boss_00001` が存在: **OK**

### MstInGame → MstEnemyOutpost FK

- `mst_enemy_outpost_id`: `dungeon_you_boss_00001`
- MstEnemyOutpost に `dungeon_you_boss_00001` が存在: **OK**

### MstInGame → MstEnemyStageParameter FK（ボスID）

- `boss_mst_enemy_stage_parameter_id`: `c_you_00001_you_dungeon_Boss_Red`
- MstEnemyStageParameter に `c_you_00001_you_dungeon_Boss_Red` が存在: **OK**

### MstInGame → MstAutoPlayerSequence FK

- `mst_auto_player_sequence_id` / `mst_auto_player_sequence_set_id`: `dungeon_you_boss_00001`
- MstAutoPlayerSequence の全行の `sequence_set_id` が `dungeon_you_boss_00001` で統一: **OK**

### MstAutoPlayerSequence → MstEnemyStageParameter FK（action_value）

- InitialSummon: `c_you_00001_you_dungeon_Boss_Red` → MstEnemyStageParameter に存在: **OK**
- ElapsedTime 各行: `e_glo_00001_you_dungeon_Normal_Colorless` → MstEnemyStageParameter に存在: **OK**

### MstKomaLine → MstPage FK

- `mst_page_id`: `dungeon_you_boss_00001`
- MstPage に `dungeon_you_boss_00001` が存在: **OK**

---

## Step 3: ゲームプレイ品質チェック

### MstEnemyOutpost

| id | hp | is_damage_invalidation |
|----|----|----------------------|
| dungeon_you_boss_00001 | 1000 | NULL（空白） |

- HP = 1000（固定要件）: **PASS**
- is_damage_invalidation = NULL（空白、RAID専用）: **PASS**

### MstInGame

| boss_mst_enemy_stage_parameter_id | boss_count |
|----------------------------------|------------|
| c_you_00001_you_dungeon_Boss_Red | 1 |

- boss_count = 1（固定要件）: **PASS**
- boss_mst_enemy_stage_parameter_id が設定済み: **PASS**

### MstAutoPlayerSequence: InitialSummon チェック

| condition_type | condition_value | action_type | is_summon_unit_outpost_damage_invalidation |
|---------------|----------------|-------------|------------------------------------------|
| InitialSummon | 0 | SummonEnemy | 1 |

- `condition_value` = 0（固定要件）: **PASS**
- `is_summon_unit_outpost_damage_invalidation` = 1（ゲートダメージ無効、boss 固有要件）: **PASS**

### MstAutoPlayerSequence: ElapsedTime 昇順チェック

| sequence_element_id | condition_type | condition_value |
|--------------------|---------------|----------------|
| 2 | ElapsedTime | 2000 |
| 3 | ElapsedTime | 5000 |
| 4 | ElapsedTime | 9000 |

- 降順逆転なし（2000 → 5000 → 9000）: **PASS**

### MstKomaLine: コマ幅合計チェック

| row | koma1_width | total_width |
|-----|------------|-------------|
| 1 | 1.0 | 1.0（単一コマ） |

- KomaLine 行数 = 1（boss 固有要件）: **PASS**
- コマ幅合計 = 1.0: **PASS**

### MstEnemyStageParameter

| id | character_unit_kind | role_type | hp | attack_power | move_speed |
|----|--------------------|-----------|----|-------------|------------|
| c_you_00001_you_dungeon_Boss_Red | Boss | Attack | 10000 | 500 | 45 |
| e_glo_00001_you_dungeon_Normal_Colorless | Normal | Balance | 3000 | 400 | 45 |

- ボスは `character_unit_kind = Boss` で設定済み: **PASS**
- 雑魚敵は `character_unit_kind = Normal` で設定済み: **PASS**

---

## dungeon_boss 固有チェック項目まとめ

| チェック項目 | 期待値 | 実際値 | 判定 |
|------------|-------|-------|------|
| MstEnemyOutpost.hp | 1000（固定） | 1000 | PASS |
| MstEnemyOutpost.is_damage_invalidation | 空白 | NULL（空白） | PASS |
| KomaLine 行数 | 1行 | 1行 | PASS |
| boss_count | 1 | 1 | PASS |
| InitialSummon: is_summon_unit_outpost_damage_invalidation | 1 | 1 | PASS |
| InitialSummon: condition_value | 0 | 0 | PASS |

---

## データ内容サマリー

- **ボスキャラ**: 幼稚園WARS キャラ（`chara_you_00001`）、赤属性、Boss/Attack役
- **ボスID**: `c_you_00001_you_dungeon_Boss_Red`
- **雑魚敵**: `e_glo_00001_you_dungeon_Normal_Colorless`（無色属性汎用）
- **BGM**: `SSE_SBG_003_001`（通常）/ `SSE_SBG_003_004`（ボス）
- **背景**: `you_00001`
- **シーケンス**: InitialSummon（ボス）+ ElapsedTime 3回（雑魚2体→3体→3体）
- **result_tips.ja**: 赤属性の強敵（ボス）が登場する（青属性有利）
- **description.ja**: 必殺ワザを使ってくる元殺し屋に注意。ボスを先に倒さないとゲートにダメージが入らない。

---

## 注意事項・備考

1. **MstInGame カラム数不一致の誤検知**: バリデータの DBスキーマ比較は i18n カラムを別テーブル扱いするため、boss の CSV（21カラム）が19カラム期待で失敗しますが、実際のフォーマットは正しいです。他の boss 生成済み作品（chi, dan 等）も同様の 21 カラム構成です。

2. **ID整合性スクリプトのバグ**: `verify_id_integrity.py` は DuckDB の AUTO_DETECT 読み込み時のカラム名解決に失敗します。手動クエリにて問題がないことを確認済みです。

3. **`e_glo_00001_you_dungeon_Normal_Colorless` の命名**: `e_glo_` プレフィックスはグローバル共通の汎用敵。幼稚園WARS専用の雑魚敵がないため共通IDを使用しており、これは正常です。
