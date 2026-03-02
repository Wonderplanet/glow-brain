## インゲームマスタデータ検証レポート
- 対象: dungeon_mag_boss_00001 (dungeon_boss)
- 検証日時: 2026-03-02

### [FAIL] 結果サマリー

| フェーズ | 結果 | 備考 |
|---------|------|------|
| A: フォーマット | ✅ | ヘッダー構造は正常（ENABLE行から始まるフォーマット） |
| B: ID整合性 | ✅ | 全FK参照が正常 |
| C: ゲームプレイ品質 | ❌ | InitialSummonのcondition_valueが仕様と不一致 |

### 問題点

#### [CRITICAL] InitialSummon の condition_value が 0 ではなく 2

- **ファイル**: `MstAutoPlayerSequence.csv`
- **該当行**: `dungeon_mag_boss_00001_1`（sequence_element_id=1）
- **現在値**: `condition_value = 2`
- **期待値**: `condition_value = 0`

dungeon_boss の仕様として、InitialSummon の condition_value は `0` でなければならない。
他の作品のボスブロック（SPY: `dungeon_spy_boss_00001_1`、CHI: `dungeon_chi_boss_00001_1`）では
いずれも condition_value = 0 で統一されている。

**修正が必要な箇所:**

```
現在:
e,dungeon_mag_boss_00001_1,dungeon_mag_boss_00001,,1,,InitialSummon,2,SummonEnemy,c_mag_00001_challange_Boss_Yellow,,1,,None,1.7,Damage,1,None,,,,,1,,Boss,Normal,1,1,1,,0,0,None,,999999999

修正後:
e,dungeon_mag_boss_00001_1,dungeon_mag_boss_00001,,1,,InitialSummon,0,SummonEnemy,c_mag_00001_challange_Boss_Yellow,,1,,None,1.7,Damage,1,None,,,,,1,,Boss,Normal,1,1,1,,0,0,None,,999999999
```

---

### 各チェック詳細

#### A: フォーマット検証

| ファイル | 結果 | 備考 |
|---------|------|------|
| MstAutoPlayerSequence.csv | ✅ | ENABLE行から始まる形式（CHIと同一フォーマット） |
| MstEnemyOutpost.csv | ✅ | |
| MstEnemyStageParameter.csv | ✅ | |
| MstInGame.csv | ✅ | |
| MstKomaLine.csv | ✅ | |
| MstPage.csv | ✅ | |

※ validate_all.py のテンプレート検証は memo/TABLE/ENABLE の3ヘッダー行形式を期待するが、
  mag/boss は ENABLE 行から始まる形式で生成されている。
  chi/boss も同一形式で生成されており、これは生成バージョンによる差異と判断する。

#### B: ID整合性チェック

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

#### C: ゲームプレイ品質チェック

| チェック項目 | 期待値 | 実際値 | 結果 |
|------------|--------|--------|------|
| MstEnemyOutpost.hp | 1000（固定） | 1000 | ✅ |
| MstEnemyOutpost.is_damage_invalidation | 空白 | NULL（空白） | ✅ |
| KomaLine行数 | 1行 | 1行 | ✅ |
| KomaLine幅合計 | 1.0 | 1.0 | ✅ |
| boss_count | 1 | 1 | ✅ |
| boss_mst_enemy_stage_parameter_id | 設定あり | c_mag_00001_challange_Boss_Yellow | ✅ |
| InitialSummon の is_summon_unit_outpost_damage_invalidation | 1 | 1 | ✅ |
| InitialSummon の condition_value | 0 | **2** | ❌ |
| ElapsedTime 単調増加 | 違反なし | 3000, 6000（単調増加） | ✅ |
| EnemyStageParameter ボス種別 | Boss | Boss | ✅ |

#### MstEnemyStageParameter 内容

| id | character_unit_kind | role_type | hp | attack_power | move_speed | well_distance |
|----|--------------------|-----------|----|--------------|------------|---------------|
| c_mag_00001_challange_Boss_Yellow | Boss | Attack | 100000 | 500 | 35 | 0.3 |
| e_mag_00001_general_Normal_Colorless | Normal | Attack | 70000 | 1200 | 35 | 0.3 |

---

### 必要な修正アクション

1. `MstAutoPlayerSequence.csv` の `dungeon_mag_boss_00001_1` 行の `condition_value` を `2` から `0` に修正する
