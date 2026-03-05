# インゲームマスタデータ検証レポート

- 対象: `dungeon_kai_boss_00001` (dungeon_boss)
- 検証日時: 2026-03-02
- 検証ディレクトリ: `domain/tasks/dungeon-bulk-masterdata-generation/outputs/kai/boss/generated/`

---

## 判定: 問題があります（修正が必要です）

---

## フェーズ別結果サマリ

| フェーズ | 結果 | 備考 |
|---------|------|------|
| A: フォーマット | 警告あり | ヘッダー形式は生成フォーマット仕様通り（許容）。MstEnemyStageParameterはデータ行なし |
| B: ID整合性 | CRITICAL x4 | MstEnemyStageParameterにデータがなくFKエラー全件発生 |
| C: ゲームプレイ品質 | 部分的OK / 問題あり | コマ行数・シーケンス時系列は正常。MstEnemyOutpostのis_damage_invalidation未設定 |
| D: バランス比較 | スキップ | MstEnemyStageParameterにデータなし（比較不可） |
| E: アセットキー | 警告あり | artwork_asset_key・boss_bgm_asset_keyが空 |

---

## Step 1: フォーマット検証

### テンプレートヘッダー形式エラーについて

`validate_all.py` の `template` 検証では全ファイルで `header_format` エラーが報告されました。
これはバリデータが3行ヘッダー形式（memo行/TABLE行/ENABLE行）を期待しているのに対し、
生成CSVが1行ヘッダー形式（ENABLE行のみ）であるためです。

SPY参考ファイル（`domain/tasks/masterdata-entry/masterdata-ingame-creator/20260301_131508_dungeon_spy_normal_block/generated/`）も同じ1行ヘッダー形式であり、**masterdata-ingame-creatorの標準出力フォーマットとして許容される**。

### 実質的なフォーマット問題（CRITICAL）

**MstEnemyStageParameter.csv がヘッダー行のみでデータ行が0件**

```
ENABLE,release_key,id,mst_enemy_character_id,character_unit_kind,role_type,...
（データ行なし）
```

ボスキャラ（`c_kai_00001_general_Normal_Yellow`）および雑魚敵（`e_kai_00101_general_Normal_Colorless`）のパラメータ定義が存在しない。

---

## Step 2: ID整合性チェック（verify_id_integrity.py）

```
valid: false
CRITICAL issues: 4件
```

### [CRITICAL] ingame_boss_fk

- 対象: `MstInGame.boss_mst_enemy_stage_parameter_id` = `c_kai_00001_general_Normal_Yellow`
- 内容: 参照先の `MstEnemyStageParameter.id` に該当レコードが存在しない
- 原因: MstEnemyStageParameter.csvにデータ行がない

### [CRITICAL] sequence_action_value_fk（3件）

| sequence_id | element_id | action_value |
|-------------|------------|-------------|
| dungeon_kai_boss_00001_1 | 1 | `c_kai_00001_general_Normal_Yellow` |
| dungeon_kai_boss_00001_2 | 2 | `e_kai_00101_general_Normal_Colorless` |
| dungeon_kai_boss_00001_3 | 3 | `e_kai_00101_general_Normal_Colorless` |

- 内容: `MstAutoPlayerSequence.action_value（SummonEnemy）` が `MstEnemyStageParameter.id` に存在しない
- 原因: MstEnemyStageParameter.csvにデータ行がない

**チェック結果:**

| チェック項目 | 結果 |
|-------------|------|
| ingame_sequence_fk | OK |
| ingame_page_fk | OK |
| ingame_outpost_fk | OK |
| ingame_boss_fk | CRITICAL |
| sequence_set_id_consistency | OK |
| sequence_action_value_fk | CRITICAL |

---

## Step 3: ゲームプレイ品質チェック

### 3-1. 敵パラメータ（MstEnemyStageParameter）

MstEnemyStageParameter.csvにデータ行がないため、パラメータ妥当性チェック不可。

### 3-2. コマ配置（MstKomaLine）

| チェック項目 | 値 | 期待値 | 判定 |
|-------------|-----|--------|------|
| コマ行数 | 1行 | 1行（dungeon_boss仕様） | OK |
| row=1 コマ幅合計 | 1.0 | 1.0 | OK |
| koma1_asset_key | `kai_00001` | 設定あり | OK |
| koma_line_layout_asset_key | `1` | 数値 | OK（既存データと同形式） |

### 3-3. シーケンス合理性（MstAutoPlayerSequence）

| チェック項目 | 値 | 判定 |
|-------------|-----|------|
| action_typeの構成 | SummonEnemy x3 | OK |
| ElapsedTime 単調増加 | 30秒→60秒（逆転なし） | OK |

シーケンス内容:
- element 1: `InitialSummon` → ボス `c_kai_00001_general_Normal_Yellow` を召喚（位置 1.7）
- element 2: `ElapsedTime=30` → 雑魚 `e_kai_00101_general_Normal_Colorless` x1
- element 3: `ElapsedTime=60` → 雑魚 `e_kai_00101_general_Normal_Colorless` x1

### 3-4. ステージ種別固有ルール（dungeon_boss）

| チェック項目 | 値 | 期待値 | 判定 |
|-------------|-----|--------|------|
| MstEnemyOutpost.hp | 1,000 | 1,000（固定） | OK |
| MstEnemyOutpost.is_damage_invalidation | NULL（未設定） | 1（要設定） | **CRITICAL** |
| コマ行数 | 1行 | 1行（固定） | OK |

### 3-5. ボス設定二重チェック

| チェック項目 | 値 | 判定 |
|-------------|-----|------|
| boss_mst_enemy_stage_parameter_id | `c_kai_00001_general_Normal_Yellow` | 設定あり |
| boss_count | 1 | OK |
| InitialSummonでボスID召喚設定 | `c_kai_00001_general_Normal_Yellow` | FK整合性以外はOK |

---

## Step 4: バランス比較

MstEnemyStageParameter.csvにデータ行がないため比較不可。

既存kai系 MstEnemyStageParameter データ（参考）:
- Boss/Attack: hp=(10,000~1,500,000 avg=367,600), atk=(100~2,000 avg=726)
- Normal/Attack: hp=(1,000~650,000 avg=105,738), atk=(100~2,000 avg=563)

---

## Step 5: アセットキー形式チェック

| アセットキー | 値 | 判定 |
|------------|-----|------|
| MstInGame.bgm_asset_key | `SSE_SBG_003_001` | OK |
| MstInGame.boss_bgm_asset_key | NULL（空） | WARNING |
| MstEnemyOutpost.artwork_asset_key | NULL（空） | WARNING |
| MstKomaLine.koma1_asset_key | `kai_00001` | OK |

---

## 問題一覧と修正提案

### [CRITICAL-1] MstEnemyStageParameter.csv のデータ行が欠損

- **対象**: `MstEnemyStageParameter.csv`
- **状態**: ヘッダー行のみ存在、データ行 0件
- **影響**: ID整合性チェック全4件がCRITICAL、ゲームプレイ不可
- **修正提案**: 以下2つのエネミーパラメータ行を追加する

```
ENABLE,release_key,id,mst_enemy_character_id,character_unit_kind,role_type,color,sort_order,hp,damage_knock_back_count,move_speed,well_distance,attack_power,attack_combo_cycle,mst_unit_ability_id1,drop_battle_point,mstTransformationEnemyStageParameterId,transformationConditionType,transformationConditionValue
e,999999999,c_kai_00001_general_Normal_Yellow,c_kai_00001,Boss,Attack,Yellow,1,500000,3,35,200,800,1,,200,,None,
e,999999999,e_kai_00101_general_Normal_Colorless,e_kai_00101,Normal,Attack,Colorless,2,80000,1,40,200,400,1,,100,,None,
```

> HP・ATK・Speedの値は設計書の意図に基づき調整すること（上記は参考値）。

### [CRITICAL-2] MstEnemyOutpost.is_damage_invalidation が未設定

- **対象**: `MstEnemyOutpost.csv` の `is_damage_invalidation` カラム
- **現在値**: NULL（空）
- **期待値**: `1`（dungeon_boss仕様: ゲートダメージ無効）
- **修正提案**: 以下に変更する

```
ENABLE,id,hp,is_damage_invalidation,outpost_asset_key,artwork_asset_key,release_key
e,dungeon_kai_boss_00001,1000,1,,,999999999
```

### [WARNING-1] boss_bgm_asset_key が空

- **対象**: `MstInGame.boss_bgm_asset_key`
- **現在値**: NULL（空）
- **確認事項**: ボスBGMを通常BGMと同じにする意図であれば許容。専用BGMが必要な場合は設定すること。

### [WARNING-2] MstEnemyOutpost.artwork_asset_key が空

- **対象**: `MstEnemyOutpost.artwork_asset_key`
- **現在値**: NULL（空）
- **確認事項**: 既存データでも空のケースが多いため許容範囲内。原画表示が必要な場合は設定すること。

---

## 結論

**修正なしでの本番投入は不可。** 以下2点の修正が必須:

1. `MstEnemyStageParameter.csv` にボス（`c_kai_00001_general_Normal_Yellow`）と雑魚（`e_kai_00101_general_Normal_Colorless`）のパラメータ行を追加する
2. `MstEnemyOutpost.csv` の `is_damage_invalidation` を `1` に設定する

修正後、再度 `verify_id_integrity.py` を実行してCRITICALエラーが解消されたことを確認すること。
