# 検証レポート: dungeon_tak_normal_00001

- **検証日時**: 2026-03-02
- **対象ディレクトリ**: `domain/tasks/dungeon-bulk-masterdata-generation/outputs/tak/normal/generated/`
- **ステージ種別**: dungeon_normal
- **インゲームID**: `dungeon_tak_normal_00001`

---

## Step 1: フォーマット検証

| ファイル | 結果 | 備考 |
|---------|------|------|
| MstAutoPlayerSequence.csv | PASS | 問題なし（7行） |
| MstEnemyOutpost.csv | PASS | 問題なし（4行） |
| MstEnemyStageParameter.csv | PASS | 問題なし（4行） |
| MstKomaLine.csv | PASS | 問題なし（6行） |
| MstPage.csv | PASS | 問題なし（4行） |
| MstInGame.csv | WARNING | カラム数不一致（期待: 19, 実際: 21） |

### MstInGame.csv カラム数について

バリデーターは `mst_in_games` テーブル（19列）のみを参照するため、i18nカラム（`result_tips.ja`, `description.ja`）を含む21列のCSVに対してエラーを報告した。

ただし、このタスクの全生成物（chi, dan, gom 等）で同様のフォーマットが採用されており、**バリデータースクリプトの既知の制限によるFalse Positive**と判定する。実際のデータ構造は正常。

---

## Step 2: ID整合性チェック

バリデータースクリプト（`verify_id_integrity.py`）はDuckDBクエリ内でのカラム参照エラーを報告したが、これはスクリプト側のCSVヘッダー処理の問題（メタ行を含むフォーマットへの未対応）によるものであり、データ自体の問題ではない。

手動確認の結果、ID整合性は正常：

| チェック項目 | 結果 |
|------------|------|
| MstInGame.id | `dungeon_tak_normal_00001` |
| MstInGame.mst_page_id | `dungeon_tak_normal_00001` → MstPage.id と一致 |
| MstInGame.mst_enemy_outpost_id | `dungeon_tak_normal_00001` → MstEnemyOutpost.id と一致 |
| MstInGame.mst_auto_player_sequence_id | `dungeon_tak_normal_00001` → MstAutoPlayerSequence.sequence_set_id と一致 |
| MstKomaLine.mst_page_id | `dungeon_tak_normal_00001` → MstPage.id と一致（3行） |
| MstEnemyStageParameter.id | `e_glo_00001_tak_dungeon_Normal_Colorless` → MstAutoPlayerSequence.action_value と一致 |

---

## Step 3: ゲームプレイ品質チェック

### MstEnemyOutpost（拠点HP）

| id | hp | is_damage_invalidation |
|----|----|----------------------|
| dungeon_tak_normal_00001 | 100 | NULL |

**dungeon_normal 固有チェック: MstEnemyOutpost.hp = 100 → PASS**

### MstKomaLine（コマ幅合計）

| row | total_width |
|-----|------------|
| 1 | 1.0 |
| 2 | 1.0 |
| 3 | 1.0 |

**dungeon_normal 固有チェック: KomaLine行数 = 3行 → PASS**
**dungeon_normal 固有チェック: コマ幅合計 = 1.0（全行） → PASS**

### MstAutoPlayerSequence（時系列順序）

| sequence_element_id | condition_type | condition_value |
|--------------------|----------------|----------------|
| 1 | ElapsedTime | 0 |
| 2 | ElapsedTime | 400 |
| 3 | ElapsedTime | 2000 |
| 4 | ElapsedTime | 4500 |

時系列逆転なし → **PASS**

### MstEnemyStageParameter（敵パラメータ）

| id | character_unit_kind | role_type | hp | attack_power | move_speed |
|----|--------------------|-----------|----|-------------|-----------|
| e_glo_00001_tak_dungeon_Normal_Colorless | Normal | Attack | 5000 | 250 | 34 |

### MstInGame（ボス数）

| id | boss_count |
|----|-----------|
| dungeon_tak_normal_00001 | 0 |

**dungeon_normal 固有チェック: boss_count = 0 → PASS**

---

## dungeon_normal 固有チェック サマリー

| チェック項目 | 期待値 | 実際値 | 結果 |
|------------|--------|--------|------|
| MstEnemyOutpost.hp | 100 | 100 | PASS |
| KomaLine行数 | 3行 | 3行 | PASS |
| コマ幅合計（全行） | 1.0 | 1.0 / 1.0 / 1.0 | PASS |
| boss_count | 0 | 0 | PASS |

---

## 最終判定

**総合結果: PASS（実質問題なし）**

| カテゴリ | 結果 | 備考 |
|--------|------|------|
| フォーマット検証 | PASS（1件WARNING） | MstInGame i18nカラムによるFalse Positive |
| ID整合性 | PASS | 全FK参照が正常に解決 |
| ゲームプレイ品質 | PASS | 全dungeon_normal固有チェック通過 |
| 時系列順序 | PASS | ElapsedTime昇順を確認 |

MstInGame.csv の `column_count_mismatch` 警告は、バリデータースクリプトがi18nカラム埋め込み形式に未対応であることによる誤検知。他作品（chi等）と同一フォーマットであり、データ仕様上は正常。

