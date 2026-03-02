# インゲームマスタデータ検証レポート

- 対象: `dungeon_kim_boss_00001`（dungeon_boss）
- 検証日時: 2026-03-02
- 検証対象ディレクトリ: `domain/tasks/dungeon-bulk-masterdata-generation/outputs/kim/boss/generated/`

---

## 総合判定

### ⚠️ 条件付き合格（要確認事項あり）

データの内容自体は概ね正しいが、以下の点について確認・対応が必要です。

| フェーズ | 結果 | 備考 |
|---------|------|------|
| A: フォーマット | ⚠️ 警告あり | バリデータ形式の差異（実害なし）+ MstEnemyStageParameterが余分 |
| B: ID整合性 | OK | 全FK参照一致（スクリプト検証済み） |
| C: ゲームプレイ品質 | OK | コマ幅・シーケンス・ボス設定すべて正常 |
| D: バランス比較 | OK | 既存kimシリーズと同等パラメータ |
| E: アセットキー | ⚠️ 警告あり | MstEnemyOutpost の outpost_asset_key が空白 |

---

## Step 1: フォーマット検証

### validate_all.py の実行結果サマリー

| ファイル | valid | 主なエラー |
|---------|-------|----------|
| MstAutoPlayerSequence.csv | false | ヘッダー形式エラー（バリデータ期待形式との差異） |
| MstEnemyOutpost.csv | false | ヘッダー形式エラー（同上） |
| MstEnemyStageParameter.csv | false | ヘッダー形式エラー（同上） |
| MstInGame.csv | false | ヘッダー形式エラー（同上） |
| MstKomaLine.csv | false | ヘッダー形式エラー（同上） |
| MstPage.csv | false | ヘッダー形式エラー（同上） |

### 判定: 実害なし（バリデータ形式差異）

`validate_all.py` は「1行目=memo、2行目=TABLE、3行目=ENABLE」の3行ヘッダー形式を期待しています。
しかし、実際のプロジェクトのCSVは「1行目=ENABLE」の1行ヘッダー形式が標準です（`projects/glow-masterdata/` 内の既存CSVもすべて同じ形式）。
このエラーは検証ツールとの形式差異であり、マスタデータ投入時の実問題ではありません。

### [WARNING] MstEnemyStageParameter.csv が余分に含まれている

設計書（`design.md`）に「既存マスタデータに存在するため新規生成しない」と明記されています。

- `c_kim_00001_kim1_challenge_Boss_Red` → 既存MstEnemyStageParameterに存在確認済み（HP: 50,000）
- `e_glo_00001_kim1_challenge_Normal_Colorless` → 既存MstEnemyStageParameterに存在確認済み（HP: 10,000）

**MstEnemyStageParameter.csv はXLSX提出から除外することを推奨します。**
（MstAutoPlayerSequenceとMstInGameからの参照は既存IDへの参照であり、問題ありません。）

---

## Step 2: ID整合性チェック

`verify_id_integrity.py` の実行結果:

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

**全FK参照が一致しています。ID整合性に問題はありません。**

---

## Step 3: ゲームプレイ品質チェック

### 3-1. 敵パラメータ

| ID | character_unit_kind | role_type | color | HP | 攻撃力 | 速度 |
|----|---------------------|-----------|-------|-----|--------|------|
| `c_kim_00001_kim1_challenge_Boss_Red` | Boss | Defense | Red | 50,000 | 100 | 40 |
| `e_glo_00001_kim1_challenge_Normal_Colorless` | Normal | Attack | Colorless | 10,000 | 100 | 40 |

- ボス（Defense/Red）: 既存Defense/Red ボスの分布（HP: 10,000〜300,000、攻撃力: 50〜1,500）と比較して正常範囲内
- 護衛雑魚（Normal/Attack/Colorless）: kimシリーズの既存チャレンジデータと同一パラメータ
- 判定: **OK**

### 3-2. コマ配置の整合性

| row | total_width |
|-----|------------|
| 1 | 1.0 |

- コマ幅合計: 1.0（正常）
- コマ行数: 1行（dungeon_boss固定仕様: 1行 → 正常）
- 判定: **OK**

### 3-3. シーケンスの合理性

| sequence_element_id | condition_type | condition_value | action_type | summon_count | aura_type |
|---------------------|----------------|-----------------|-------------|--------------|-----------|
| 1 | InitialSummon | 0 | SummonEnemy | 1 | Boss |
| 2 | ElapsedTime | 1500 | SummonEnemy | 1 | Default |
| 3 | ElapsedTime | 3000 | SummonEnemy | 2 | Default |

- ElapsedTime の時系列: 1500 → 3000（単調増加、逆行なし）
- SummonEnemy 行数: 3行（適切な範囲内）
- 判定: **OK**

### 3-4. ステージ種別固有ルール（dungeon_boss）

| チェック項目 | 期待値 | 実際値 | 判定 |
|------------|--------|--------|------|
| MstEnemyOutpost.hp | 1,000（固定） | 1,000 | OK |
| コマ行数 | 1行（固定） | 1行 | OK |
| ボスダメージ無効（MstAutoPlayerSequence） | InitialSummonの is_summon_unit_outpost_damage_invalidation = 1 | 1 | OK |

### 3-5. ボス設定の二重チェック

- `boss_mst_enemy_stage_parameter_id`: `c_kim_00001_kim1_challenge_Boss_Red`（設定あり）
- `boss_count`: 1
- InitialSummon でボスID `c_kim_00001_kim1_challenge_Boss_Red` が召喚設定されている
- 判定: **OK**

---

## Step 4: バランス比較

Defense/Red ボスの既存パラメータ分布（既存MstEnemyStageParameter.csvより）:

| HP範囲 | 攻撃力範囲 | 移動速度範囲 |
|--------|----------|------------|
| 10,000 〜 5,000,000 | 50 〜 1,500 | 20 〜 40 |

生成データ（ボス）: HP=50,000, 攻撃力=100, 速度=40

- 同じ `c_kim_00001_kim1_challenge_Boss_Red` が既存データにそのまま存在し、HP=50,000・攻撃力=100・速度=40 で一致
- 判定: **OK**（既存kimシリーズのchallengeボスと同一パラメータ）

---

## Step 5: アセットキーチェック

| テーブル | カラム | 値 | 判定 |
|---------|--------|-----|------|
| MstInGame | bgm_asset_key | `SSE_SBG_003_009` | OK |
| MstInGame | boss_bgm_asset_key | `SSE_SBG_003_007` | OK |
| MstInGame | loop_background_asset_key | `kim_00001` | OK |
| MstInGame | player_outpost_asset_key | NULL（空白） | OK（任意項目） |
| MstEnemyOutpost | outpost_asset_key | NULL（空白） | **WARNING** |
| MstEnemyOutpost | artwork_asset_key | NULL（空白） | OK（DEFAULT=空白が許容） |
| MstKomaLine | koma1_asset_key | `kim_00001` | OK |

### [WARNING] MstEnemyOutpost.outpost_asset_key が空白

DBスキーマでは `outpost_asset_key` は `NOT NULL` と定義されていますが、既存データ（532件中498件）でも空白になっているため、実際の運用では空白が許容されています。

ただし、SPY bossブロック（参考データ）では `outpost_asset_key = spy_00005` が設定されています。kimシリーズに対応するアセットキーが判明した場合は設定を推奨します。

---

## Step 6: 最終判定

### 総括

dungeon_kim_boss_00001 の生成CSVは、ゲームプレイ上の動作に関わる以下の項目をすべて満たしています:

- HP・攻撃力・速度パラメータが適切
- コマ幅合計が正確に 1.0
- コマ行数が dungeon_boss 仕様（1行）に準拠
- ID整合性が完全（FK参照切れなし）
- ボス設定（boss_mst_enemy_stage_parameter_id, boss_count）が正しく設定
- ボスが InitialSummon で設定され、is_summon_unit_outpost_damage_invalidation=1 でゲートダメージ無効が機能する
- BGM・ループ背景アセットキーが設定されている

### 要対応事項

#### [WARNING] MstEnemyStageParameter.csv の除外推奨

設計書通り、以下2件は既存マスタデータに存在するため、**XLSX提出時にMstEnemyStageParameter.csvを除外すること**を強く推奨します。
既存IDへの重複投入はデータ不整合の原因になる可能性があります。

- `c_kim_00001_kim1_challenge_Boss_Red`（既存に存在確認済み）
- `e_glo_00001_kim1_challenge_Normal_Colorless`（既存に存在確認済み）

#### [INFO] outpost_asset_key は空白のまま可

既存データの93.6%（498/532件）が空白であり、運用上は問題ありません。
キムシリーズのアセットキーが確定したタイミングで設定することを推奨します（SPY bossでは `spy_00005` が使用されている）。

### XLSX提出対象ファイル（MstEnemyStageParameterを除外した場合）

| ファイル | 提出 | 備考 |
|---------|------|------|
| MstEnemyOutpost.csv | 提出 | |
| MstPage.csv | 提出 | |
| MstKomaLine.csv | 提出 | |
| MstAutoPlayerSequence.csv | 提出 | |
| MstInGame.csv | 提出 | |
| MstEnemyStageParameter.csv | **除外推奨** | 既存IDを参照するのみ・新規生成不要 |
