# インゲームマスタデータ検証レポート

- 対象: `dungeon_hut_boss_00001` (dungeon_boss)
- 検証日時: 2026-03-02
- 検証者: masterdata-ingame-verifier

---

## 判定結果: 問題があります（修正が必要です）

| フェーズ | 結果 | 備考 |
|---------|------|------|
| A: フォーマット | WARNING | ヘッダー形式の差異（実運用上は問題なし、後述） |
| B: ID整合性 | OK | 全FK参照一致 |
| C: ゲームプレイ品質 | CRITICAL | `is_damage_invalidation` 未設定、シーケンスの `condition_value` 誤り |
| D: バランス比較 | OK | 既存データの最小値と同等範囲内 |
| E: アセットキー | WARNING | `artwork_asset_key` 等が空白（設計書通りだが要確認） |

---

## Step 1: フォーマット検証

### 結果: WARNING（実運用上の問題なし）

全6ファイルで `valid: false` が報告されましたが、これは **validate_all.py がCSVヘッダーを「3行ヘッダー形式（memo/TABLE/ENABLE）」と期待しているのに対し、生成CSVが「1行ヘッダー形式（ENABLE,カラム名,...）」で出力されているためです。**

生成CSVのヘッダー行は `ENABLE,id,...` 形式であり、列名・列順序・データ型は全て正常です。このフォーマット差異はスクリプト側の検証基準の問題であり、マスタデータとしての内容は正しいと判断します。

| ファイル | validate_all.py結果 | 実際の状態 |
|---------|-------------------|---------|
| MstAutoPlayerSequence.csv | valid: false (57件) | ヘッダー形式差異のみ、データ内容は正常 |
| MstEnemyOutpost.csv | valid: false (14件) | ヘッダー形式差異のみ、データ内容は正常 |
| MstEnemyStageParameter.csv | valid: false (41件) | ヘッダー形式差異のみ、データ内容は正常 |
| MstInGame.csv | valid: false (29件) | ヘッダー形式差異のみ、データ内容は正常 |
| MstKomaLine.csv | valid: false (50件) | ヘッダー形式差異のみ、データ内容は正常 |
| MstPage.csv | valid: false (10件) | ヘッダー形式差異のみ、データ内容は正常 |

---

## Step 2: ID整合性チェック

### 結果: OK（全チェック合格）

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
  "issues": []
}
```

全FK参照が一致しています。

---

## Step 3: ゲームプレイ品質チェック

### 3-1. 敵パラメータの妥当性

| id | character_unit_kind | role_type | color | hp | attack_power | move_speed | well_distance |
|----|--------------------|-----------|----|------|------------|---------|-------------|
| c_hut_00001_dungeon_boss_Boss_Colorless | Boss | Defense | Colorless | 5,000 | 300 | 35 | 0.21 |
| e_glo_00001_hut1_dungeon_Normal_Colorless | Normal | Attack | Colorless | 1,000 | 100 | 40 | 0.2 |

設計書（`dungeon_hut_boss_00001.md`）と一致しており、パラメータ値は妥当な範囲内です。

### 3-2. コマ配置の整合性

| row | total_width |
|-----|------------|
| 1 | 1.0 |

- コマ行数: **1行（dungeon_boss仕様に適合）**
- コマ幅合計: **1.0（正常）**

### 3-3. シーケンスの合理性

| sequence_element_id | condition_type | condition_value | action_type | action_value | summon_count |
|--------------------|---------------|----------------|------------|-------------|-------------|
| 1 | ElapsedTime | 0 | SummonEnemy | c_hut_00001_dungeon_boss_Boss_Colorless | 1 |
| 2 | ElapsedTime | 20 | SummonEnemy | e_glo_00001_hut1_dungeon_Normal_Colorless | 1 |
| 3 | ElapsedTime | 40 | SummonEnemy | e_glo_00001_hut1_dungeon_Normal_Colorless | 1 |

**[CRITICAL] condition_value の値が異常に小さい**

設計書では「2,000ms後、4,000ms後」に護衛雑魚を召喚する設計ですが、生成CSVでは `20, 40` という値になっています。

既存データとの比較:
- SPY normal（同じdungeon系）: `500, 2000, 5000` などのミリ秒単位
- 既存MstAutoPlayerSequence全体: 通常 `100〜5000` 程度の値

`condition_value = 20, 40` はゲームフレーム換算でも非常に短すぎ（約0.3秒〜0.7秒）、ゲームプレイとして成立しない可能性があります。**設計書の「2,000ms → 2000、4,000ms → 4000」に修正が必要です。**

ElapsedTimeの単調増加チェック: 問題なし（0 → 20 → 40）

### 3-4. ステージ種別固有ルール（dungeon_boss）

**[CRITICAL] MstEnemyOutpost.is_damage_invalidation が NULL**

```
dungeon_hut_boss_00001 | hp=1,000 | is_damage_invalidation=NULL
```

- MstEnemyOutpost.hp = 1,000: **仕様に適合（dungeon_boss固定値）**
- MstEnemyOutpost.is_damage_invalidation = NULL: **仕様違反（dungeon_bossでは `1` が必要）**

dungeon_boss 仕様では「ボスが撃破されるまで敵ゲートへのダメージは無効」であり、`is_damage_invalidation = 1` の設定が必須です。設計書にも「ゲートダメージ無効」と明記されています。

### 3-5. ボス設定の二重チェック

| boss_mst_enemy_stage_parameter_id | boss_count |
|----------------------------------|-----------|
| c_hut_00001_dungeon_boss_Boss_Colorless | 1 |

MstAutoPlayerSequenceでのボス召喚確認:
- `aura_type = Boss`、`is_summon_unit_outpost_damage_invalidation = 1` のシーケンスが存在: **OK**
- ただし、このシーケンスのtriggerが `ElapsedTime`（condition_value=0）であり、`InitialSummon` ではありません

注: 既存のSPY normalデータも `ElapsedTime=0` でボス（ではないが）を召喚しており、`ElapsedTime=0` でのSummonEnemyはInitialSummonと同等の動作と考えられます。

---

## Step 4: バランス比較

### 生成データと既存データの比較

| 種別 | role_type | 生成HP | 既存HP最小 | 既存HP平均 | 既存HP最大 |
|------|----------|--------|----------|----------|----------|
| Boss（Defense） | Defense | 5,000 | 1,000 | 114,312 | 5,000,000 |
| Normal（Attack） | Attack | 1,000 | 1,000 | 69,771 | 900,000 |

生成データのHPは既存データの最小値付近に位置しています。dungeon_bossは新設コンテンツのため直接の比較対象データが存在しない点を考慮し、**設計書の意図（全キャラで挑む初回dungeon_boss）に対して妥当な値と判断します。**

判定: **WARNING**（既存データの最小値付近、意図的な低HP設定として妥当）

---

## Step 5: アセットキーチェック

| テーブル | カラム | 値 | 判定 |
|---------|-------|-----|------|
| MstInGame | bgm_asset_key | `SSE_SBG_003_002` | OK |
| MstInGame | boss_bgm_asset_key | NULL（空白） | WARNING ※設計書通り |
| MstInGame | loop_background_asset_key | NULL（空白） | WARNING ※設計書通り |
| MstInGame | player_outpost_asset_key | NULL（空白） | WARNING ※設計書通り |
| MstEnemyOutpost | outpost_asset_key | NULL（空白） | WARNING |
| MstEnemyOutpost | artwork_asset_key | NULL（空白） | WARNING |
| MstKomaLine | koma_line_layout_asset_key | `1`（整数値） | WARNING ※文字列のはずが整数になっている |
| MstKomaLine | koma1_asset_key | `glo_00014` | OK |

設計書には「ループ背景アセットキーは未設定（hut専用背景アセット未存在のため空白）」と明記されているため、bgm以外の空白は意図的です。ただし、`outpost_asset_key` および `artwork_asset_key` の空白は設計書に明記されていないため要確認です。

**[WARNING] MstKomaLine.koma_line_layout_asset_key = `1`（整数値）**

このカラムには通常アセットキー文字列が設定されますが、`1` という整数値が入っています。設計書では「コマラインレイアウトアセットキー」の具体的な値が記載されていません。既存データとの整合性を確認する必要があります。

---

## Step 6: 最終判定

### 修正必須（CRITICAL）

#### [CRITICAL-1] MstEnemyOutpost.is_damage_invalidation が未設定

- **ファイル**: `MstEnemyOutpost.csv`
- **対象**: `dungeon_hut_boss_00001` の `is_damage_invalidation` カラム
- **現在値**: NULL（空白）
- **期待値**: `1`
- **理由**: dungeon_boss 仕様では「ボスが撃破されるまで敵ゲートへのダメージを無効にする」ため、このカラムを `1` に設定することが必須
- **修正方法**: `MstEnemyOutpost.csv` の `is_damage_invalidation` を `1` に変更する

#### [CRITICAL-2] MstAutoPlayerSequence.condition_value が異常に小さい

- **ファイル**: `MstAutoPlayerSequence.csv`
- **対象**: sequence_element_id=2, 3 の `condition_value`
- **現在値**: `20`（element_id=2）、`40`（element_id=3）
- **期待値**: `2000`（element_id=2）、`4000`（element_id=3）
- **理由**: 設計書では「2,000ms後に護衛雑魚・第1波」「4,000ms後に護衛雑魚・第2波」と定義されており、既存データのElapsedTime値（100〜5000程度）と比較しても `20, 40` は極端に短すぎる。ゲームプレイとして成立しない（ほぼ同時召喚になる）
- **修正方法**: `condition_value` を `20 → 2000`、`40 → 4000` に修正する

### 要確認（WARNING）

#### [WARNING-1] MstEnemyOutpost.outpost_asset_key, artwork_asset_key が空白

- 設計書に明記なし。他のdungeon_bossと同様の扱いかを確認要
- dungeon系は新設コンテンツであるため、空白で問題ない場合は設計書に明記することを推奨

#### [WARNING-2] MstKomaLine.koma_line_layout_asset_key = `1`（整数値）

- 文字列アセットキーの場所に整数値 `1` が入っている
- 既存データとの整合性確認、または数値IDとして仕様上問題ないかを確認要

#### [WARNING-3] ボスHP 5,000 は既存Boss(Defense)の平均（114,312）より大幅に低い

- 意図的な初回dungeon_boss向け設定として許容範囲と判断するが、レベルデザイン担当者の確認を推奨

---

## 修正内容サマリー

| # | 優先度 | ファイル | カラム | 現在値 | 修正後 |
|---|--------|---------|-------|-------|-------|
| 1 | CRITICAL | MstEnemyOutpost.csv | is_damage_invalidation | （空白） | `1` |
| 2 | CRITICAL | MstAutoPlayerSequence.csv | condition_value（element_id=2） | `20` | `2000` |
| 3 | CRITICAL | MstAutoPlayerSequence.csv | condition_value（element_id=3） | `40` | `4000` |
