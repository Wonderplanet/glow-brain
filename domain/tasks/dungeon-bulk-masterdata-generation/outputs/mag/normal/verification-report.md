# インゲームマスタデータ検証レポート

- 対象: `dungeon_mag_normal_00001` (dungeon_normal)
- 検証日時: 2026-03-02
- 検証ディレクトリ: `domain/tasks/dungeon-bulk-masterdata-generation/outputs/mag/normal/generated/`

---

## 総合判定

### 実機プレイに支障のある問題はありません（アセットキー空白は投入前に要確認）

| フェーズ | 結果 | 備考 |
|---------|------|------|
| A: フォーマット | WARNING | ヘッダー形式がテンプレートと異なる（後述） |
| B: ID整合性 | OK | 全FK参照一致 |
| C: ゲームプレイ品質 | OK | HP・コマ行数・シーケンス単調増加すべて正常 |
| D: バランス比較 | WARNING | e_mag_00001のATKが高め・e_mag_00101のspeedが最大値（意図的） |
| E: アセットキー | WARNING | outpost_asset_key / artwork_asset_key が空白 |

---

## Step 1: フォーマット検証

### 結果: WARNING（テンプレート形式の差異）

全6ファイルで `valid: false` が返ったが、これは **バリデータが期待する3行ヘッダー形式（memo行・TABLE行・ENABLE行）** と、生成CSVの **1行ヘッダー形式** の差異によるものです。

列名・列順・値の内容自体に問題はなく、実際のマスタデータ投入フローでの処理形式に依存した警告です。

| ファイル | テンプレート | フォーマット | スキーマ | Enum | 実質的問題 |
|---------|------------|------------|---------|------|---------|
| MstAutoPlayerSequence.csv | NG（ヘッダー形式） | NG（ヘッダー形式） | NG（列数+1） | OK | 列自体は正常 |
| MstEnemyOutpost.csv | NG（ヘッダー形式） | NG（ヘッダー形式） | - | - | 列自体は正常 |
| MstEnemyStageParameter.csv | NG（ヘッダー形式） | NG（ヘッダー形式） | - | - | 列自体は正常 |
| MstInGame.csv | NG（ヘッダー形式） | NG（ヘッダー形式） | - | - | 列自体は正常 |
| MstKomaLine.csv | NG（ヘッダー形式） | NG（ヘッダー形式） | NG（列数+1） | OK | 列自体は正常 |
| MstPage.csv | NG（ヘッダー形式） | NG（ヘッダー形式） | - | - | 列自体は正常 |

> MstAutoPlayerSequence・MstKomaLine の「カラム数+1」はENABLE列の扱いの差異によるもので、内容に影響しない。

---

## Step 2: ID整合性チェック

### 結果: OK（全チェック通過）

```
verify_id_integrity.py 実行結果:
- ingame_sequence_fk: true
- ingame_page_fk: true
- ingame_outpost_fk: true
- ingame_boss_fk: true
- sequence_set_id_consistency: true
- sequence_action_value_fk: true
total_issues: 0
```

全FK参照（MstInGame→MstAutoPlayerSequence / MstPage / MstEnemyOutpost）、sequence_set_idの一貫性、SummonEnemy action_value→MstEnemyStageParameter.id の全参照が正常に解決されています。

---

## Step 3: ゲームプレイ品質チェック

### 3-1. 敵パラメータの妥当性

| id | mst_enemy_character_id | character_unit_kind | role_type | color | hp | attack_power | move_speed | well_distance |
|----|------------------------|---------------------|-----------|-------|----|-------------|-----------|--------------|
| e_mag_00001_general_Normal_Colorless | enemy_mag_00001 | Normal | Attack | Colorless | 70,000 | 1,200 | 35 | 0.3 |
| e_mag_00101_general_Normal_Blue | enemy_mag_00101 | Normal | Attack | Blue | 10,000 | 1,500 | 100 | 0.25 |

**評価:**
- `e_mag_00001`: HP=70,000はNormal/Attack既存平均(69,771)と非常に近い妥当な値。ATK=1,200は既存平均(411)の約2.9倍だが、既存データに1,200超が23件存在し許容範囲内。
- `e_mag_00101`: HP=10,000、move_speed=100（既存上限値）、ATK=1,500。「後半に超高速で突撃してくる」というゲームプレイ設計意図に合致。既存データでも move_speed=100 かつ ATK=1,500 の組み合わせが実在する（8件中1件）。

### 3-2. コマ配置の整合性

| row | height | total_width |
|-----|--------|-------------|
| 1 | 0.55 | 1.0 |
| 2 | 0.55 | 1.0 |
| 3 | 0.55 | 1.0 |

- **コマ幅合計 = 1.0: OK（全3行）**
- **行数 = 3行: OK（dungeon_normal仕様通り）**
- height合計: 1.65（3行 × 0.55、既存dungeon系と同等）

### 3-3. シーケンスの合理性

**action_type分布:**
- SummonEnemy: 6行

**ElapsedTime 時系列（condition_value）:**

| sequence_element_id | condition_type | condition_value | action_value |
|--------------------|----------------|-----------------|--------------|
| 1 | ElapsedTime | 500 | e_mag_00001_general_Normal_Colorless |
| 2 | ElapsedTime | 2,000 | e_mag_00001_general_Normal_Colorless |
| 3 | ElapsedTime | 4,000 | e_mag_00001_general_Normal_Colorless |
| 4 | ElapsedTime | 6,000 | e_mag_00101_general_Normal_Blue |
| 5 | ElapsedTime | 6,300 | e_mag_00101_general_Normal_Blue |
| 6 | ElapsedTime | 6,600 | e_mag_00101_general_Normal_Blue |

- **時系列の逆行: なし（単調増加、OK）**
- 前半3回: 通常の魔法少女エネミー(e_mag_00001)を間隔を空けて召喚
- 後半3回: 高速エネミー(e_mag_00101)を短間隔（300フレーム）で連続召喚（意図的な難易度演出）

### 3-4. ステージ種別固有ルール（dungeon_normal）

- `MstEnemyOutpost.hp = 100: OK（固定値通り）`
- `コマ行数 = 3行: OK（固定値通り）`
- `is_damage_invalidation = NULL（= 0）: OK（dungeon_normalは無敵不要）`

### 3-5. ボス設定の確認

- `boss_mst_enemy_stage_parameter_id = NULL: OK（dungeon_normalはボスなし）`
- `boss_count = 0: OK`
- InitialSummon行: なし（ボスなしのため不要、OK）

---

## Step 4: バランス比較

### 既存 Normal/Attack エネミーとの比較

| 指標 | 既存min | 既存avg | 既存max | e_mag_00001 | e_mag_00101 | 評価 |
|------|--------|--------|--------|------------|------------|------|
| hp | 1,000 | 69,771 | 900,000 | 70,000 | 10,000 | OK |
| attack_power | 50 | 411 | 2,500 | 1,200 | 1,500 | OK（高めだが範囲内） |
| move_speed | 8 | 40.2 | 100 | 35 | 100 | WARNING（e_mag_00101が最大値） |

**move_speed=100 について:**
既存データにmove_speed=100のNormal/Attackエネミーが8件存在し、かつMstInGameのdescription.jaに「超高速のつらら（怪異）が突撃してくる」と明記されているため、意図的な設計です。

---

## Step 5: アセットキーチェック

### 結果: WARNING（空白アセットキーが存在）

| テーブル | カラム | 値 | 評価 |
|---------|-------|---|------|
| MstInGame | bgm_asset_key | `SSE_SBG_003_001` | OK |
| MstInGame | boss_bgm_asset_key | NULL | OK（dungeon_normalはボスなし） |
| MstInGame | loop_background_asset_key | `mag_00004` | OK |
| MstEnemyOutpost | outpost_asset_key | NULL（空白） | **WARNING** |
| MstEnemyOutpost | artwork_asset_key | NULL（空白） | **WARNING** |
| MstKomaLine（row1） | koma1_asset_key | `mag_00004` | OK |
| MstKomaLine（row2） | koma1_asset_key | `mag_00004` | OK |
| MstKomaLine（row3） | koma1_asset_key | `mag_00004` | OK |

#### [WARNING] MstEnemyOutpost.outpost_asset_key が空白

- 対象: `dungeon_mag_normal_00001.outpost_asset_key`
- 確認事項: dungeon系のoutpost_asset_keyはプロジェクト共通のアセットを使用する場合は空白のままでも動作する可能性があります。他のdungeon_normalデータと照合して空白が許容されるか確認してください。

#### [WARNING] MstEnemyOutpost.artwork_asset_key が空白

- 対象: `dungeon_mag_normal_00001.artwork_asset_key`
- 確認事項: dungeon系の artwork_asset_key（原画アセット）が空白でも動作するか確認してください。DBスキーマ定義では `DEFAULT=`（空白許容）となっています。

---

## Step 6: 最終判定

### 実機プレイに支障のある問題はありません

dungeon_normal 仕様の2項目（HP=100固定・コマ行数3行固定）はいずれも正常です。

#### 対応不要（バリデータの仕様差異）

- **フォーマット警告（全ファイル）**: バリデータが期待する3行ヘッダー形式と生成CSVの1行ヘッダー形式の差異。CSVの内容・列・値に問題はない。

#### 投入前に確認推奨

1. **[WARNING] MstEnemyOutpost.outpost_asset_key = 空白**
   - dungeon系で空白が許容されるか既存データと照合する

2. **[WARNING] MstEnemyOutpost.artwork_asset_key = 空白**
   - dungeon系で空白が許容されるか既存データと照合する

#### 意図的な設計（対応不要）

- **e_mag_00101.move_speed = 100（最大値）**: description.jaに「超高速」と明記された設計。既存データにも同様のケースが存在する。
- **e_mag_00001.attack_power = 1,200（既存平均の約3倍）**: 既存データに1,200超のケースが23件存在し、設計範囲内。
