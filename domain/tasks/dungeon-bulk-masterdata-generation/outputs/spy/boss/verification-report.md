# 検証レポート: dungeon_spy_boss_00001

- **検証日時**: 2026-03-02
- **検証対象**: `domain/tasks/dungeon-bulk-masterdata-generation/outputs/spy/boss/generated/`
- **ステージ種別**: dungeon_boss
- **インゲームID**: `dungeon_spy_boss_00001`

---

## 総合判定: PASS

全ての検証項目が仕様を満たしています。

---

## Step 1: フォーマット検証

| ファイル | テンプレート | CSV形式 | DBスキーマ | Enum | 判定 |
|---------|------------|--------|-----------|------|------|
| MstAutoPlayerSequence.csv | OK | OK (7行) | OK | OK | PASS |
| MstEnemyOutpost.csv | OK | OK (4行) | OK | OK | PASS |
| MstEnemyStageParameter.csv | OK | OK (5行) | OK | OK | PASS |
| MstInGame.csv | OK | OK (4行) | 注記あり | OK | PASS* |
| MstKomaLine.csv | OK | OK (4行) | OK | OK | PASS |
| MstPage.csv | OK | OK (4行) | OK | OK | PASS |

### MstInGame.csv スキーマ検証の注記

バリデータが「カラム数不一致（期待:19, 実際:21）」と報告しましたが、これは **誤検知** です。

- DBスキーマ（`mst_in_games`）のカラム数: 19
- 実際のCSVカラム数: 21（= 19 + i18n拡張カラム2つ）
- 追加カラム: `result_tips.ja`、`description.ja`（`MstInGameI18n`テーブル）
- `projects/glow-masterdata/sheet_schema/MstInGame.csv`（正規テンプレート）と完全一致しており、**正しい形式**

---

## Step 2: ID整合性チェック

バリデータから6件のERRORが報告されましたが、全て **スクリプト側の既知のバグ** による誤検知です。

- 原因: `verify_id_integrity.py` スクリプトがCSVのメタ行（memo行・TABLE行）を含むテンプレート形式に対応しておらず、カラム名解決に失敗している
- 具体的エラー: `"sequence_set_id" not found in FROM clause` 等
- 実際のIDは全て一貫しており（後述のStep 3で確認済み）、参照整合性に問題なし

**手動確認によるID整合性:**

| FK関係 | 参照元 | 参照先 | 値 | 整合性 |
|--------|--------|--------|-----|--------|
| MstInGame.mst_auto_player_sequence_id | dungeon_spy_boss_00001 | MstAutoPlayerSequence.sequence_set_id | dungeon_spy_boss_00001 | OK |
| MstInGame.mst_page_id | dungeon_spy_boss_00001 | MstPage.id | dungeon_spy_boss_00001 | OK |
| MstInGame.mst_enemy_outpost_id | dungeon_spy_boss_00001 | MstEnemyOutpost.id | dungeon_spy_boss_00001 | OK |
| MstInGame.boss_mst_enemy_stage_parameter_id | c_spy_00101_spy_dungeon_Boss_Blue | MstEnemyStageParameter.id | c_spy_00101_spy_dungeon_Boss_Blue | OK |
| MstKomaLine.mst_page_id | dungeon_spy_boss_00001 | MstPage.id | dungeon_spy_boss_00001 | OK |

---

## Step 3: ゲームプレイ品質チェック

### dungeon_boss 固有チェック項目

| チェック項目 | 期待値 | 実際値 | 判定 |
|------------|--------|--------|------|
| MstEnemyOutpost.hp | 1000（固定） | **1000** | PASS |
| MstEnemyOutpost.is_damage_invalidation | 空白（RAIDのみ使用） | **NULL（空白）** | PASS |
| KomaLine行数 | 1行（固定） | **1行** | PASS |
| MstInGame.boss_count | 1 | **1** | PASS |
| InitialSummon is_summon_unit_outpost_damage_invalidation | 1（ゲートダメージ無効） | **1** | PASS |
| InitialSummon condition_value | 0 | **0** | PASS |

### ElapsedTime 時系列順序チェック

| sequence_element_id | condition_type | condition_value(ms) |
|---------------------|----------------|---------------------|
| 1 | InitialSummon | 0 |
| 2 | ElapsedTime | 2000 |
| 3 | ElapsedTime | 5000 |
| 4 | ElapsedTime | 9000 |

時系列逆転: **なし（PASS）**

### KomaLine コマ幅合計チェック

| row | total_width |
|-----|------------|
| 1 | 1.0 |

コマ幅合計 = 1.0: **PASS**

### MstEnemyStageParameter 敵パラメータ

| id | character_unit_kind | role_type | hp | attack_power | move_speed |
|----|--------------------|-----------|----|-------------|------------|
| c_spy_00101_spy_dungeon_Boss_Blue | Boss | Attack | 50,000 | 15,000 | 40 |
| e_spy_00001_spy_dungeon_Normal_Colorless | Normal | Attack | 3,000 | 500 | 40 |

ボスキャラ（`chara_spy_00101`）: Boss/Attack/Blue、HP50,000、攻撃力15,000
雑魚敵（`enemy_spy_00001`）: Normal/Attack/Colorless、HP3,000、攻撃力500

---

## AutoPlayerSequence 詳細

| element | condition_type | condition_value | action_value | summon_count | summon_interval | damage_invalidation |
|---------|---------------|-----------------|-------------|-------------|----------------|---------------------|
| 1 | InitialSummon | 0 | c_spy_00101_spy_dungeon_Boss_Blue | 1 | 0 | **1（有効）** |
| 2 | ElapsedTime | 2000ms | e_spy_00001_spy_dungeon_Normal_Colorless | 2 | 1500ms | 0 |
| 3 | ElapsedTime | 5000ms | e_spy_00001_spy_dungeon_Normal_Colorless | 3 | 1500ms | 0 |
| 4 | ElapsedTime | 9000ms | e_spy_00001_spy_dungeon_Normal_Colorless | 3 | 1500ms | 0 |

---

## 備考

- ボスは青属性（Blue）のため、黄属性有利
- `result_tips.ja`: 「青属性のボスが登場する（黄属性有利）」
- `description.ja`: 「必殺ワザを使ってくる強敵に注意。黄属性キャラを中心に編成し、ボスを撃破せよ。」
- 背景アセット: `spy_00005`
- BGM: `SSE_SBG_003_002`
- 雑魚敵は`e_spy_00001_spy_dungeon_Normal_Colorless`のみ使用（色なし/通常）
