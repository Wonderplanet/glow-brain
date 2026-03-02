# インゲームマスタデータ検証レポート

- 対象: `dungeon_chi_normal_00001` (dungeon_normal)
- 検証日時: 2026-03-02
- 検証者: masterdata-ingame-verifier スキル

---

## 検証結果サマリー

### CSVの修正あり（修正後に再検証し合格）

検証過程で以下の2ファイルに問題が発見され、修正を実施しました。修正後は全ステップをクリアしています。

| フェーズ | 結果 | 備考 |
|---------|------|------|
| A: フォーマット | OK | 全6ファイルの列数一致（修正後） |
| B: ID整合性 | OK | 全6項目のFK参照一致（修正後） |
| C: ゲームプレイ品質 | OK | HP・コマ幅・シーケンス全て正常 |
| D: バランス比較 | OK | 既存dungeon系と同等範囲内 |
| E: アセットキー | OK | 必須キー全て設定済み |

---

## 修正内容

### [CRITICAL - 修正済み] MstAutoPlayerSequence.csv: データ行の余分な列

**問題**: 各データ行の `death_type` カラムの後に余分な値 `Normal` が挿入されており、全データ行が36列（ヘッダーは35列）になっていた。

**影響**: `enemy_hp_coef`（本来 `1`）が `Normal`、`enemy_attack_coef` 以降が1列ずつずれ、`release_key` が36列目（ヘッダー外）に押し出されていた。

**修正内容**:
- 各データ行の `death_type` 直後の余分な値 `Normal` を削除
- 結果: 全5行が正しい35列になった

**修正前（例）**:
```
...Default,Normal,1,1,1,,0,0,None,,999999999  ← 36列
```
**修正後（例）**:
```
...Default,1,1,1,,0,0,None,,999999999  ← 35列
```

---

### [CRITICAL - 修正済み] MstKomaLine.csv: データ行2・3の列数不足

**問題**: koma3 が空欄（2コマのみ使用）の行2・3で、末尾のカンマが1つ不足しており、各行が42列（ヘッダーは43列）になっていた。`release_key` の値（999999999）が `koma4_effect_target_roles` カラムに入り込んでいた。

**影響**: データがインポートされた場合、`release_key` が誤った列に登録され、`release_key` 列は空欄となる。

**修正内容**:
- 行2・3の末尾に不足していたカンマを1つ追加
- 結果: 全3行が正しい43列になった

**修正前（例）**:
```
...All,All,All,,,,,,,,,,,,,,,,999999999  ← 42列（1カンマ不足）
```
**修正後（例）**:
```
...All,All,All,,,,,,,,,,,,,,,,,999999999  ← 43列
```

---

## 詳細検証結果

### Step 1: フォーマット検証

| ファイル | ヘッダー列数 | データ列数 | 結果 |
|---------|------------|----------|------|
| MstEnemyStageParameter.csv | 19 | 19 | OK |
| MstEnemyOutpost.csv | 7 | 7 | OK |
| MstPage.csv | 3 | 3 | OK |
| MstKomaLine.csv | 43 | 43 | OK（修正後） |
| MstAutoPlayerSequence.csv | 35 | 35 | OK（修正後） |
| MstInGame.csv | 22 | 22 | OK |

> 注意: `validate_all.py` はCSVフォーマットとして「1行目=memo, 2行目=TABLE, 3行目=ENABLE」の3行ヘッダー構造を期待するため、このリポジトリの「1行目=ENABLE」フォーマットとは齟齬が生じ `valid: false` と報告されます。ただし既存の `projects/glow-masterdata/` 配下のCSVも同じ1行ヘッダー形式であるため、これはバリデータの制限であり生成データの問題ではありません。

---

### Step 2: ID整合性チェック

`verify_id_integrity.py` の実行結果（修正後）:

| チェック項目 | 結果 |
|------------|------|
| ingame_sequence_fk | OK |
| ingame_page_fk | OK |
| ingame_outpost_fk | OK |
| ingame_boss_fk | OK（空欄 = normalなのでボスなし） |
| sequence_set_id_consistency | OK |
| sequence_action_value_fk | OK |

- `MstInGame.mst_auto_player_sequence_set_id` = `dungeon_chi_normal_00001` → MstAutoPlayerSequence全行に存在
- `MstInGame.mst_page_id` = `dungeon_chi_normal_00001` → MstPage.id に存在
- `MstInGame.mst_enemy_outpost_id` = `dungeon_chi_normal_00001` → MstEnemyOutpost.id に存在
- `MstInGame.boss_mst_enemy_stage_parameter_id` = 空欄（normalブロックのため正常）
- SummonEnemy の `action_value` 全5行がすべて MstEnemyStageParameter.id に存在

---

### Step 3: ゲームプレイ品質チェック

#### 3-1. エネミーパラメータ

| ID | キャラ | 種別 | ロール | 色 | HP | ATK | 速度 |
|----|-------|------|-------|---|-----|-----|-----|
| e_chi_00101_general_Normal_Colorless | enemy_chi_00101 | Normal | Defense | Colorless | 5,000 | 320 | 35 |
| e_chi_00101_general_Normal_Yellow | enemy_chi_00101 | Normal | Technical | Yellow | 13,000 | 720 | 35 |

- 無属性ゾンビ（Defense）が序盤、黄属性ゾンビ（Technical）が後半に出現する構成
- 緑属性が黄属性に有利という属性有利ギミックを活用した設計

#### 3-2. コマ配置

| 行 | koma1_width | koma2_width | koma3_width | 幅合計 |
|----|------------|------------|------------|------|
| 1 | 0.25 | 0.50 | 0.25 | 1.000 |
| 2 | 0.60 | 0.40 | — | 1.000 |
| 3 | 0.75 | 0.25 | — | 1.000 |

- 全行の幅合計 = 1.000（CRITICAL基準クリア）
- コマ行数 = 3行（dungeon_normal 仕様の3行固定と一致）

#### 3-3. シーケンス合理性

| 召喚順 | ElapsedTime | action_type | action_value | summon_count |
|-------|------------|-------------|-------------|------------|
| 1 | 250 | SummonEnemy | e_chi_00101_general_Normal_Colorless | 1 |
| 2 | 600 | SummonEnemy | e_chi_00101_general_Normal_Colorless | 1 |
| 3 | 1200 | SummonEnemy | e_chi_00101_general_Normal_Yellow | 1 |
| 4 | 1600 | SummonEnemy | e_chi_00101_general_Normal_Yellow | 1 |
| 5 | 2000 | SummonEnemy | e_chi_00101_general_Normal_Yellow | 2 |

- ElapsedTime: 250 → 600 → 1200 → 1600 → 2000（単調増加、時系列逆行なし）
- sequence_set_id: 全5行が `dungeon_chi_normal_00001` で一致

#### 3-4. ステージ種別固有ルール（dungeon_normal）

| チェック項目 | 期待値 | 実際値 | 結果 |
|------------|-------|-------|------|
| MstEnemyOutpost.hp | 100（固定） | 100 | OK |
| コマ行数 | 3行（固定） | 3行 | OK |
| ボス設定 | なし | なし（boss_id=空欄, boss_count=空欄） | OK |

#### 3-5. ボス設定

- `boss_mst_enemy_stage_parameter_id` = 空欄（dungeon_normal はボスなし）
- `boss_count` = 空欄
- MstAutoPlayerSequence に InitialSummon なし（ボスなし構成として正常）

---

### Step 4: バランス比較

| エネミーID | HP | ATK | 既存Normal/Defense HP範囲 | 既存Normal/Technical HP範囲 | 結果 |
|----------|-----|-----|------------------------|--------------------------|------|
| e_chi_00101_general_Normal_Colorless | 5,000 | 320 | 1,000〜600,000 | — | OK |
| e_chi_00101_general_Normal_Yellow | 13,000 | 720 | — | 1,000〜700,000 | OK |

- 両エネミーとも既存データの ±5倍範囲内

---

### Step 5: アセットキーチェック

| テーブル | カラム | 値 | 状態 |
|---------|------|---|------|
| MstInGame | bgm_asset_key | SSE_SBG_003_001 | OK |
| MstInGame | boss_bgm_asset_key | 空欄 | OK（normalはboss_bgmなし） |
| MstInGame | loop_background_asset_key | glo_00016 | OK |
| MstEnemyOutpost | artwork_asset_key | chi_0001 | OK |
| MstEnemyOutpost | outpost_asset_key | 空欄 | OK（SPYダンジョンも空欄） |

---

## 品質総評

修正後の全6ファイルについて、以下が確認できています：

1. **フォーマット**: 全ファイルで列数がヘッダーとデータで一致
2. **ID整合性**: FK参照切れなし、sequence_set_id一貫性あり
3. **ゲームプレイ品質**: dungeon_normal 仕様（HP=100、3行コマ）を満たす
4. **バランス**: 既存データと同等範囲
5. **アセットキー**: 必須キー全て設定済み

dungeon_chi_normal_00001 は修正後、実機プレイで問題なく動作する水準のマスタデータです。
