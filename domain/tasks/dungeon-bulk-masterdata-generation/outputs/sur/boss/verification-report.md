# 検証レポート: dungeon_sur_boss_00001

- 対象ディレクトリ: `domain/tasks/dungeon-bulk-masterdata-generation/outputs/sur/boss/generated/`
- ステージ種別: dungeon_boss
- 検証日時: 2026-03-02
- 検証者: インゲームマスタデータ検証エージェント

---

## 総合結果

**NG（要修正）**

| 項目 | 結果 |
|------|------|
| フォーマット検証 | NG（1件・後述参照） |
| ID整合性チェック | 参考情報あり（後述参照） |
| dungeon_boss固有チェック | NG（1件・要修正） |

---

## Step 1: フォーマット検証結果

| ファイル | 結果 | 備考 |
|---------|------|------|
| MstAutoPlayerSequence.csv | OK | 全検証パス |
| MstEnemyOutpost.csv | OK | 全検証パス |
| MstEnemyStageParameter.csv | OK | 全検証パス |
| MstInGame.csv | **NG** | カラム数不一致（後述） |
| MstKomaLine.csv | OK | 全検証パス |
| MstPage.csv | OK | 全検証パス |

### MstInGame.csv のカラム数不一致について

- 検証スクリプト検出: `カラム数が一致しません（期待: 19, 実際: 21）`
- **判定: 誤検知（問題なし）**
- 理由: CSVには `result_tips.ja` と `description.ja` という I18n 拡張カラムが含まれている。これらは DBスキーマ（`mst_in_games` テーブル）の 19 カラム外に定義されるものであり、他作品の同種 CSV でも同様の構成が採用されている。検証スクリプトが I18n 拡張カラムを考慮していないことによる既知の誤検知。

---

## Step 2: ID整合性チェック結果

- スクリプト実行結果: `"valid": true`（クリティカルエラー: 0件）
- 検出された Binder Error はすべて、スクリプトが GLOW 独自 CSV フォーマット（1行目: memo、2行目: TABLE 定義）に対応していないことによるツール側の既知制限によるもの。
- spy boss を含む他の全作品でも同一エラーが発生しており、CSV データ自体の問題ではない。
- **判定: 問題なし（スクリプト制限による誤検出）**

### 手動 ID 整合性確認

| チェック項目 | 結果 |
|-------------|------|
| MstInGame.id = `dungeon_sur_boss_00001` | OK |
| MstInGame.mst_auto_player_sequence_id = `dungeon_sur_boss_00001` | OK |
| MstInGame.mst_auto_player_sequence_set_id = `dungeon_sur_boss_00001` | OK |
| MstInGame.mst_page_id = `dungeon_sur_boss_00001` | OK |
| MstInGame.mst_enemy_outpost_id = `dungeon_sur_boss_00001` | OK |
| MstPage.id = `dungeon_sur_boss_00001` | OK |
| MstEnemyOutpost.id = `dungeon_sur_boss_00001` | OK |
| MstKomaLine.mst_page_id = `dungeon_sur_boss_00001` | OK |
| MstAutoPlayerSequence.sequence_set_id = `dungeon_sur_boss_00001` | OK（全3件） |
| MstAutoPlayerSequence の action_value が MstEnemyStageParameter.id に存在する | OK |

---

## Step 3: ゲームプレイ品質チェック結果

### MstEnemyOutpost

| カラム | 値 | 期待値 | 結果 |
|--------|---|--------|------|
| id | dungeon_sur_boss_00001 | - | OK |
| hp | 1000 | 1000（固定） | OK |
| is_damage_invalidation | 空白 | 空白（RAIDのみ） | OK |
| outpost_asset_key | sur_00001 | - | OK |

### MstInGame

| カラム | 値 | 期待値 | 結果 |
|--------|---|--------|------|
| bgm_asset_key | SSE_SBG_003_001 | - | OK |
| boss_bgm_asset_key | 空白 | - | OK |
| loop_background_asset_key | sur_00001 | - | OK |
| player_outpost_asset_key | sur_00001 | - | OK |
| boss_mst_enemy_stage_parameter_id | c_sur_00101_general_Boss_Blue | 値あり | OK |
| boss_count | 1 | 1（固定） | OK |
| normal_enemy_hp_coef | 1 | - | OK |
| normal_enemy_attack_coef | 1 | - | OK |
| normal_enemy_speed_coef | 1 | - | OK |
| boss_enemy_hp_coef | 1 | - | OK |
| boss_enemy_attack_coef | 1 | - | OK |
| boss_enemy_speed_coef | 1 | - | OK |

### MstAutoPlayerSequence

| シーケンス | condition_type | condition_value | action_value | is_summon_unit_outpost_damage_invalidation | 結果 |
|-----------|---------------|----------------|-------------|------------------------------------------|------|
| dungeon_sur_boss_00001_1 | InitialSummon | **1** | c_sur_00101_general_Boss_Blue | 1 | **NG** |
| dungeon_sur_boss_00001_2 | ElapsedTime | 2000 | e_sur_00101_general_Normal_Colorless | 0 | OK |
| dungeon_sur_boss_00001_3 | ElapsedTime | 4500 | e_sur_00101_general_Normal_Colorless | 0 | OK |

- ElapsedTime の condition_value 昇順: 2000 -> 4500 -> OK

### MstKomaLine

| カラム | 値 | 期待値 | 結果 |
|--------|---|--------|------|
| 行数 | 1行 | 1行（boss固定） | OK |
| row | 1 | - | OK |
| koma1_width | 1.0 | - | OK |
| total_width（koma合計） | 1.000 | 1.000 | OK |

### MstEnemyStageParameter

| id | character_unit_kind | role_type | hp | attack_power | move_speed |
|----|-------------------|-----------|-----|-------------|-----------|
| c_sur_00101_general_Boss_Blue | Boss | Attack | 400000 | 700 | 45 |
| e_sur_00101_general_Normal_Colorless | Normal | Defense | 3000 | 100 | 35 |

---

## dungeon_boss 固有チェック項目

| チェック項目 | 結果 | 詳細 |
|-------------|------|------|
| MstEnemyOutpost.hp = 1000 | OK | 値: 1000 |
| MstEnemyOutpost.is_damage_invalidation = 空白 | OK | 空白 |
| KomaLine 行数 = 1行 | OK | 1行 |
| boss_count = 1 | OK | 値: 1 |
| InitialSummon の is_summon_unit_outpost_damage_invalidation = 1 | OK | 値: 1 |
| InitialSummon の condition_value = 0 | **NG** | 値: **1**（期待値: 0） |

---

## 検出された問題と修正指示

### [ERROR] InitialSummon の condition_value が不正

- **ファイル**: `MstAutoPlayerSequence.csv`
- **対象行**: `dungeon_sur_boss_00001_1`（sequence_element_id=1、condition_type=InitialSummon）
- **問題**: `condition_value` が `1` になっているが、期待値は `0`
- **根拠**: spy/chi/dan/gom/jig/kai/kim/mag/sum/tak/you/yuw の全 boss ブロックで `InitialSummon` の `condition_value=0` を使用している
- **修正内容**: `condition_value` を `1` から `0` に変更する

```csv
修正前:
e,dungeon_sur_boss_00001_1,dungeon_sur_boss_00001,,1,,InitialSummon,1,SummonEnemy,...

修正後:
e,dungeon_sur_boss_00001_1,dungeon_sur_boss_00001,,1,,InitialSummon,0,SummonEnemy,...
```

---

## 修正後の再検証が必要な項目

- [ ] MstAutoPlayerSequence.csv の InitialSummon condition_value を `0` に修正
- [ ] 修正後、フォーマット検証を再実行して問題がないことを確認
