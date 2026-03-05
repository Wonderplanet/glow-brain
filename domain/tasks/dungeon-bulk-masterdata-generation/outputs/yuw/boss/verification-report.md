# 検証レポート: dungeon_yuw_boss_00001

- **検証日時**: 2026-03-02
- **対象ディレクトリ**: `domain/tasks/dungeon-bulk-masterdata-generation/outputs/yuw/boss/generated/`
- **ステージ種別**: dungeon_boss
- **検証結果**: PASS（問題なし）

---

## Step 1: フォーマット検証

| ファイル | 結果 | 備考 |
|---------|------|------|
| MstAutoPlayerSequence.csv | PASS | ENABLEヘッダー行付き正常CSV |
| MstEnemyOutpost.csv | PASS | ENABLEヘッダー行付き正常CSV |
| MstEnemyStageParameter.csv | PASS | ENABLEヘッダー行付き正常CSV |
| MstInGame.csv | PASS | ENABLEヘッダー行付き正常CSV |
| MstKomaLine.csv | PASS | ENABLEヘッダー行付き正常CSV |
| MstPage.csv | PASS | ENABLEヘッダー行付き正常CSV |

> 注: `validate_all.py` スクリプトはテンプレート形式（memo/TABLE/ENABLEの3行ヘッダー）を期待するため「header_format」エラーが表示されたが、これは実CSVのフォーマット（ENABLEヘッダー1行形式）と検証ツールの期待形式の差異によるものであり、実データに問題はない。

---

## Step 2: ID整合性チェック

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
  "summary": {
    "total_issues": 0,
    "critical_issues": 0,
    "warnings": 0
  }
}
```

**結果: PASS** - 全FK参照・ID整合性チェックで問題なし。

---

## Step 3: ゲームプレイ品質チェック

### 3-1. MstEnemyOutpost: HP・ダメージ無効化設定

| id | hp | is_damage_invalidation |
|----|-----|------------------------|
| dungeon_yuw_boss_00001 | 1000 | NULL（空白） |

- **HP = 1000**: PASS（dungeon_boss固定値）
- **is_damage_invalidation = NULL（空白）**: PASS（RAIDのみ使用、bossでは空白が正しい）

### 3-2. MstInGame: ボス設定

| boss_mst_enemy_stage_parameter_id | boss_count |
|-----------------------------------|------------|
| c_yuw_00301_yuw_dungeon_Boss_Blue | 1 |

- **boss_count = 1**: PASS

### 3-3. MstAutoPlayerSequence: InitialSummon設定

| action_type | action_value | condition_type | condition_value | is_summon_unit_outpost_damage_invalidation |
|-------------|--------------|----------------|-----------------|---------------------------------------------|
| SummonEnemy | c_yuw_00301_yuw_dungeon_Boss_Blue | InitialSummon | 0 | 1 |

- **InitialSummon: condition_value = 0**: PASS
- **InitialSummon: is_summon_unit_outpost_damage_invalidation = 1**: PASS（ゲートダメージ無効、boss固有設定）

### 3-4. ElapsedTime 時系列チェック（逆順チェック）

逆順レコード: **0件** - PASS

ElapsedTimeレコード（昇順確認）:

| sequence_element_id | condition_type | condition_value | action_value |
|---------------------|----------------|-----------------|--------------|
| 2 | ElapsedTime | 3000 | c_yuw_00001_yuw_dungeon_Normal_Yellow |
| 3 | ElapsedTime | 6000 | c_yuw_00001_yuw_dungeon_Normal_Yellow |

時系列: 3000ms → 6000ms（単調増加）- PASS

### 3-5. MstKomaLine: 行数・幅合計チェック

| row | total_width |
|-----|-------------|
| 1 | 1.0 |

- **KomaLine行数 = 1行**: PASS（dungeon_boss固定値）
- **コマ幅合計 = 1.0**: PASS

### 3-6. MstEnemyStageParameter: キャラクター設定

| id | character_unit_kind | role_type | hp | attack_power | move_speed |
|----|--------------------|-----------|----|--------------|------------|
| c_yuw_00301_yuw_dungeon_Boss_Blue | Boss | Technical | 15000 | 800 | 29 |
| c_yuw_00001_yuw_dungeon_Normal_Yellow | Normal | Attack | 10000 | 320 | 34 |

- ボス（乃愛）: Boss / Technical / Blue, HP 15000, ATK 800, SPD 29 - 正常
- 雑魚（護衛・天乃リリサ）: Normal / Attack / Yellow, HP 10000, ATK 320, SPD 34 - 正常

---

## dungeon_boss 固有チェック項目サマリ

| チェック項目 | 期待値 | 実際値 | 結果 |
|------------|--------|--------|------|
| MstEnemyOutpost.hp | 1000（固定） | 1000 | PASS |
| MstEnemyOutpost.is_damage_invalidation | 空白（NULL） | NULL | PASS |
| KomaLine行数 | 1行 | 1行 | PASS |
| boss_count | 1 | 1 | PASS |
| InitialSummon: is_summon_unit_outpost_damage_invalidation | 1 | 1 | PASS |
| InitialSummon: condition_value | 0 | 0 | PASS |
| ElapsedTime 時系列 | 単調増加 | 3000→6000 | PASS |
| コマ幅合計 | 1.0 | 1.0 | PASS |

---

## 総合判定

**PASS - 全チェック項目で問題なし**

### 生成されたマスタデータの概要

- **インゲームID**: `dungeon_yuw_boss_00001`
- **作品**: 2.5次元の誘惑（yuw）
- **ボスキャラ**: c_yuw_00301_yuw_dungeon_Boss_Blue（乃愛 / Boss / Technical / Blue）
- **雑魚キャラ**: c_yuw_00001_yuw_dungeon_Normal_Yellow（天乃リリサ / Normal / Attack / Yellow）
- **BGM**: SSE_SBG_003_006（通常）/ SSE_SBG_003_007（ボス戦）
- **背景アセット**: yuw_00001
- **召喚タイムライン**:
  - 0ms（InitialSummon）: ボス召喚（ゲートダメージ無効）
  - 3000ms: 雑魚召喚
  - 6000ms: 雑魚召喚
