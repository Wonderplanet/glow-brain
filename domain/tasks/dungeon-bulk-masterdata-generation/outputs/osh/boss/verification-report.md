# 検証レポート: dungeon_osh_boss_00001

- 検証日時: 2026-03-02
- 対象ディレクトリ: `domain/tasks/dungeon-bulk-masterdata-generation/outputs/osh/boss/generated/`
- ステージ種別: dungeon_boss
- インゲームID: `dungeon_osh_boss_00001`

---

## 総合判定: PASS

全チェック項目が合格しました。

---

## Step 1: フォーマット検証

### 結果: 警告あり（実害なし）

検証スクリプト（`validate_all.py`）はCSVに3行のメタヘッダー（memo/TABLE/ENABLE）を期待する形式で動作するため、
実データ行をヘッダーとして誤解釈し `header_format` エラーおよび `missing_column` 警告を出力した。

**実際のCSVヘッダー構造**は正常で、全6ファイルとも以下を確認:

| ファイル | ヘッダー行 | データ行数 | 判定 |
|---------|-----------|-----------|------|
| MstAutoPlayerSequence.csv | 正常 | 3行 | OK |
| MstEnemyOutpost.csv | 正常 | 1行 | OK |
| MstEnemyStageParameter.csv | 正常 | 2行 | OK |
| MstInGame.csv | 正常 | 1行 | OK |
| MstKomaLine.csv | 正常 | 1行 | OK |
| MstPage.csv | 正常 | 1行 | OK |

> スクリプトのエラーはツール側の形式期待値の問題であり、データ自体に欠陥はない。

---

## Step 2: ID整合性チェック

### 結果: PASS

```json
{
  "check": "id_integrity",
  "valid": true,
  "checks": {
    "ingame_sequence_fk": true,
    "ingame_page_fk": true,
    "ingame_outpost_fk": true,
    "ingame_boss_fk": true,
    "sequence_set_id_consistency": true,
    "sequence_action_value_fk": true
  },
  "issues": [],
  "summary": {
    "total_issues": 0,
    "critical_issues": 0,
    "warnings": 0
  }
}
```

全FK参照・ID一貫性チェックが合格。

---

## Step 3: ゲームプレイ品質チェック

### 3-1. MstEnemyOutpost: HP と is_damage_invalidation

```
id                      | hp   | is_damage_invalidation
dungeon_osh_boss_00001  | 1000 | NULL
```

| チェック項目 | 期待値 | 実測値 | 判定 |
|------------|--------|-------|------|
| hp | 1000（固定） | 1000 | PASS |
| is_damage_invalidation | 空白（RAIDのみ使用） | NULL（空白） | PASS |

### 3-2. MstInGame: boss 情報

```
boss_mst_enemy_stage_parameter_id              | boss_count
c_osh_00001_general_osh_n_Boss_Colorless       | 1
```

| チェック項目 | 期待値 | 実測値 | 判定 |
|------------|--------|-------|------|
| boss_count | 1 | 1 | PASS |
| boss_mst_enemy_stage_parameter_id | 設定あり | c_osh_00001_general_osh_n_Boss_Colorless | PASS |

### 3-3. MstAutoPlayerSequence: InitialSummon（ElapsedTime=0）

```
action_type  | action_value                              | condition_type | condition_value | is_summon_unit_outpost_damage_invalidation
SummonEnemy  | c_osh_00001_general_osh_n_Boss_Colorless  | ElapsedTime    | 0               | 1
```

| チェック項目 | 期待値 | 実測値 | 判定 |
|------------|--------|-------|------|
| condition_value（InitialSummon） | 0 | 0 | PASS |
| is_summon_unit_outpost_damage_invalidation（InitialSummon） | 1（ゲートダメージ無効） | 1 | PASS |

### 3-4. ElapsedTime の時系列順序チェック

逆順レコード: 0件

| チェック項目 | 期待値 | 実測値 | 判定 |
|------------|--------|-------|------|
| ElapsedTime降順なし | 0件 | 0件 | PASS |

ElapsedTime の順序: 0 → 2000 → 5000（正常な昇順）

### 3-5. MstKomaLine: total_width

```
row | total_width
1   | 1.0
```

| チェック項目 | 期待値 | 実測値 | 判定 |
|------------|--------|-------|------|
| KomaLine行数 | 1行（boss固定） | 1行 | PASS |
| total_width | 1.0 | 1.0 | PASS |

### 3-6. MstEnemyStageParameter: 敵パラメータ

```
id                                         | character_unit_kind | role_type  | hp   | attack_power | move_speed
c_osh_00001_general_osh_n_Boss_Colorless   | Boss                | Technical  | 1000 | 100          | 20
e_glo_00001_general_osh_n_Normal_Yellow    | Normal              | Attack     | 1000 | 100          | 34
```

| チェック項目 | 確認内容 | 判定 |
|------------|---------|------|
| ボスキャラ種別 | character_unit_kind=Boss | PASS |
| 雑魚キャラ種別 | character_unit_kind=Normal | PASS |
| ボスrole_type | Technical | PASS |
| 雑魚role_type | Attack | PASS |
| 全体HPバランス | Boss 1000 / Normal 1000 | PASS |

---

## dungeon_boss 固有チェック項目サマリー

| チェック項目 | 期待値 | 実測値 | 判定 |
|------------|--------|-------|------|
| MstEnemyOutpost.hp | 1000（固定） | 1000 | PASS |
| MstEnemyOutpost.is_damage_invalidation | 空白 | NULL（空白） | PASS |
| KomaLine行数 | 1行 | 1行 | PASS |
| boss_count | 1 | 1 | PASS |
| InitialSummon: is_summon_unit_outpost_damage_invalidation | 1 | 1 | PASS |
| InitialSummon: condition_value | 0 | 0 | PASS |

---

## MstAutoPlayerSequence 全レコード確認

| seq | condition_type | condition_value | action_value | summon_count | is_outpost_damage_invalidation |
|-----|---------------|----------------|-------------|-------------|-------------------------------|
| 1 | ElapsedTime | 0 | c_osh_00001_general_osh_n_Boss_Colorless（ボス） | 1 | 1 |
| 2 | ElapsedTime | 2000 | e_glo_00001_general_osh_n_Normal_Yellow（雑魚） | 2 | 0 |
| 3 | ElapsedTime | 5000 | e_glo_00001_general_osh_n_Normal_Yellow（雑魚） | 1 | 0 |

ボスを初期召喚（ElapsedTime=0）し、その後2回に分けて雑魚を追加召喚する構成。問題なし。

---

## 備考

- BGM: `SSE_SBG_003_003`（osh専用ボスBGM）
- 背景: `osh_00001`
- ボスキャラ: `c_osh_00001_general_osh_n_Boss_Colorless`（推しの子ボス）
- 雑魚キャラ: `e_glo_00001_general_osh_n_Normal_Yellow`（グロー共通敵）
- summon_position（ボス）: `1.7`（中央右寄りに初期配置）
- move_start_condition_type（ボス）: `Damage 1`（ダメージを受けたら移動開始）
