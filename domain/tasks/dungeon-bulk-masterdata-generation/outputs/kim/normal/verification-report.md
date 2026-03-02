# インゲームマスタデータ検証レポート

- **対象**: `dungeon_kim_normal_00001`（dungeon_normal）
- **検証日時**: 2026-03-02
- **生成ディレクトリ**: `domain/tasks/dungeon-bulk-masterdata-generation/outputs/kim/normal/generated/`

---

## 判定サマリー

### 条件付き合格（要確認事項あり）

| フェーズ | 結果 | 備考 |
|---------|------|------|
| A: フォーマット | INFO | ヘッダー形式は生成仕様に準拠（3行形式ではなく1行形式）。実データは正常 |
| B: ID整合性 | OK | 全FK参照一致 |
| C: ゲームプレイ品質 | OK | コマ幅・シーケンス・ボス設定すべて正常 |
| D: バランス比較 | WARNING | 既存マスタに同一IDのレコードが存在する。well_distance の差異あり |
| E: アセットキー | OK | 必須アセットキーはすべて設定済み |

---

## Step 1: フォーマット検証

### 結果: INFO（問題なし）

`validate_all.py` は3行ヘッダー形式（`memo` / `TABLE` / `ENABLE` 行）を期待するため `valid: false` を報告しているが、
生成CSVは1行ヘッダー形式（`ENABLE,...` から始まる形式）で統一されており、これはSPY×FAMILYの参考ファイルと同一の仕様である。

**確認済みファイル（6件）**:
- MstAutoPlayerSequence.csv
- MstEnemyOutpost.csv
- MstEnemyStageParameter.csv
- MstInGame.csv
- MstKomaLine.csv
- MstPage.csv

実際のCSV内容は各テーブルの仕様に沿ったカラム構成で記載されている。

---

## Step 2: ID整合性チェック

### 結果: OK（全チェック通過）

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

| チェック項目 | 値 | 結果 |
|------------|-----|------|
| MstInGame.mst_auto_player_sequence_set_id | `dungeon_kim_normal_00001` | OK |
| MstInGame.mst_page_id | `dungeon_kim_normal_00001` | OK |
| MstInGame.mst_enemy_outpost_id | `dungeon_kim_normal_00001` | OK |
| MstInGame.boss_mst_enemy_stage_parameter_id | NULL（ボスなし） | OK |
| sequence_set_id の一貫性 | 全行 `dungeon_kim_normal_00001` | OK |
| SummonEnemy の action_value FK | MstEnemyStageParameter.id と一致 | OK |

---

## Step 3: ゲームプレイ品質チェック

### 3-1. 敵パラメータの妥当性

| id | character_unit_kind | role_type | hp | attack_power | move_speed | well_distance |
|----|--------------------|-----------|----|-------------|-----------|---------------|
| e_glo_00001_kim1_challenge_Normal_Colorless | Normal | Attack | 10,000 | 100 | 40 | 0.25 |
| e_glo_00001_kim1_challenge_Normal_Red | Normal | Attack | 10,000 | 100 | 40 | 0.25 |

エネミーステータスシート基準値（Normal / Attack / イベントチャレンジ相当）:
- 雑魚HP Atk: 4,900〜16,800 が標準範囲
- HP=10,000 は範囲内（既存dungeon challengeのNormal Attackと同水準）

**判定**: 妥当

### 3-2. コマ配置の整合性

**コマ幅合計（全行1.0であること）**:

| row | total_width |
|-----|------------|
| 1 | 1.0 |
| 2 | 1.0 |
| 3 | 1.0 |

- **行数**: 3行（dungeon_normal仕様 = 3行固定 に適合）
- **height合計**: 0.33 + 0.34 + 0.33 = 1.0

**判定**: OK（仕様完全準拠）

### 3-3. シーケンスの合理性

**召喚パターン**:

| sequence_element_id | condition_type | condition_value(秒) | action_type | action_value | summon_count |
|--------------------|---------------|-------------------|-------------|-------------|-------------|
| 1 | ElapsedTime | 3 | SummonEnemy | e_glo_00001_kim1_challenge_Normal_Colorless | 1 |
| 2 | ElapsedTime | 10 | SummonEnemy | e_glo_00001_kim1_challenge_Normal_Colorless | 2 |
| 3 | ElapsedTime | 20 | SummonEnemy | e_glo_00001_kim1_challenge_Normal_Red | 1 |
| 4 | ElapsedTime | 30 | SummonEnemy | e_glo_00001_kim1_challenge_Normal_Colorless | 2 |
| 5 | ElapsedTime | 40 | SummonEnemy | e_glo_00001_kim1_challenge_Normal_Red | 2 |
| 6 | ElapsedTime | 55 | SummonEnemy | e_glo_00001_kim1_challenge_Normal_Colorless | 3 |

- **時系列**: 3 → 10 → 20 → 30 → 40 → 55 秒（単調増加）
- **action_type**: 全行 `SummonEnemy`（ボスなし仕様に合致）
- **ElapsedTime逆行**: なし

**判定**: OK（緩急のある召喚設計になっている）

### 3-4. ステージ種別固有ルール

| 項目 | 期待値 | 実際値 | 判定 |
|------|--------|--------|------|
| MstEnemyOutpost.hp | 100（dungeon_normal固定） | 100 | OK |
| MstEnemyOutpost.is_damage_invalidation | - | NULL（= 0） | OK |
| コマ行数 | 3行（dungeon_normal固定） | 3行 | OK |
| boss_mst_enemy_stage_parameter_id | NULL（normalはボスなし） | NULL | OK |

### 3-5. ボス設定

- `boss_mst_enemy_stage_parameter_id` = NULL（設定なし）
- `boss_count` = NULL（設定なし）
- dungeon_normal ブロックのためボスなし仕様に合致

**判定**: OK

---

## Step 4: バランス比較

### [WARNING] 既存マスタに同一IDのレコードが存在する

既存の `projects/glow-masterdata/MstEnemyStageParameter.csv` に以下の同一IDのレコードが確認された:

| 項目 | 既存マスタ | 今回生成 | 差異 |
|------|-----------|---------|------|
| e_glo_00001_kim1_challenge_Normal_Colorless の well_distance | 0.18 | **0.25** | 差異あり |
| e_glo_00001_kim1_challenge_Normal_Red の well_distance | 0.18 | **0.25** | 差異あり |
| hp | 10,000 | 10,000 | 一致 |
| attack_power | 100 | 100 | 一致 |
| move_speed | 40 | 40 | 一致 |

**注意事項**: これらの `MstEnemyStageParameter` IDは既存マスタに存在するレコードと同一IDである。本生成データをそのまま投入すると既存レコードとの重複が発生する可能性がある。

**確認事項**:
1. dungeon_kim_normal_00001 は既存のkimチャレンジ（別コンテンツ）で使用されている同じ雑魚敵IDを再利用する設計か？
2. `well_distance=0.25`（今回）と `well_distance=0.18`（既存）の差異は意図的なものか？
3. 同一IDで異なるパラメータを投入する場合、既存データの上書き更新となる点を確認すること。

**既存dungeon challengeパラメータ分布（比較参考）**:

| character_unit_kind | role_type | HP範囲 | ATK範囲 | speed範囲 |
|--------------------|-----------|--------|---------|----------|
| Normal | Attack | 1,000〜10,000 | 100〜300 | 25〜40 |
| Normal | Defense | 1,000 | 100 | 30 |

今回生成値（HP=10,000、ATK=100、speed=40）は既存の最大値付近に位置するが、範囲内。

---

## Step 5: アセットキーチェック

### 結果: OK

| テーブル | カラム | 値 | 判定 |
|---------|--------|-----|------|
| MstInGame | bgm_asset_key | `SSE_SBG_003_002` | OK |
| MstInGame | boss_bgm_asset_key | NULL（ボスなしのため空欄） | OK |
| MstInGame | loop_background_asset_key | `kim_00001` | OK |
| MstEnemyOutpost | artwork_asset_key | `kim_00001` | OK |
| MstKomaLine（row=1） | koma1_asset_key | `kim_00001` | OK |
| MstKomaLine（row=2） | koma1_asset_key | `kim_00001` | OK |
| MstKomaLine（row=3） | koma1_asset_key | `kim_00001` | OK |

MstInGame の `player_outpost_asset_key` は NULL（空欄）。SPY×FAMILYの参考データでも空欄のため、許容範囲と判断する。

---

## Step 6: 最終判定

### 条件付き合格

技術的なID整合性・ゲームプレイ仕様はすべて満たしている。ただし以下の確認事項がある。

#### [WARNING] 既存マスタとの重複IDおよびパラメータ差異

- **対象**: `MstEnemyStageParameter.id` = `e_glo_00001_kim1_challenge_Normal_Colorless` / `e_glo_00001_kim1_challenge_Normal_Red`
- **状況**: これらのIDは既存 `projects/glow-masterdata/MstEnemyStageParameter.csv` にすでに存在する
- **差異**: `well_distance` が既存 0.18 → 今回 0.25（変更となる）
- **確認事項**: dungeon（限界チャレンジ）コンテンツでは既存チャレンジ用エネミーIDをそのまま再利用する設計かどうか、および `well_distance` の変更が意図的かどうかを確認すること

#### 確認が取れれば実機プレイに問題なし

上記WARNING の意図が確認・承認されれば、インゲームの実機プレイ観点では問題ない。

---

## データ概要

| テーブル | 設定値 |
|---------|--------|
| MstInGame.id | `dungeon_kim_normal_00001` |
| MstEnemyOutpost.hp | 100（dungeon_normal固定値） |
| MstKomaLine 行数 | 3行（dungeon_normal固定値） |
| MstAutoPlayerSequence 行数 | 6行（召喚イベント数） |
| BGM | `SSE_SBG_003_002` |
| 背景アセット | `kim_00001` |
| 雑魚敵 | `e_glo_00001_kim1_challenge_Normal_Colorless`（無属性）、`e_glo_00001_kim1_challenge_Normal_Red`（赤属性） |
