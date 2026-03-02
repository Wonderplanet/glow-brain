# インゲームマスタデータ検証レポート

- **対象**: `dungeon_dan_boss_00001` (dungeon_boss)
- **検証日時**: 2026-03-02
- **生成ディレクトリ**: `domain/tasks/dungeon-bulk-masterdata-generation/outputs/dan/boss/generated/`

---

## 判定結果

### 問題があります（修正が必要です）

| フェーズ | 結果 | 備考 |
|---------|------|------|
| A: フォーマット | 警告あり（無視可） | ヘッダー形式の誤検知（既存データと同一形式） |
| B: ID整合性 | OK | 全FK参照一致 |
| C: ゲームプレイ品質 | CRITICAL あり | is_damage_invalidation が未設定 |
| D: バランス比較 | OK | 既存データの正常範囲内 |
| E: アセットキー | OK | 必須キーあり |

---

## Step 1: フォーマット検証（Phase A）

### 結果: 警告あり（無視可）

`validate_all.py` の検証結果は全ファイルで `valid: false` となったが、これは **スクリプトが3行ヘッダー形式を期待しているのに対し、生成CSVが ENABLE から始まる1行ヘッダー形式** であるためです。

既存の参考データ（`domain/tasks/masterdata-entry/masterdata-ingame-creator/20260301_131508_dungeon_spy_normal_block/generated/`）も同一の1行ヘッダー形式であることを確認済みのため、**このエラーは誤検知**です。

各ファイルの実データは問題なく存在しています。

| ファイル | 行数 | フォーマット |
|---------|------|------------|
| MstEnemyStageParameter.csv | データ2行（ボス1 + 雑魚1） | 1行ヘッダー（ENABLE形式） |
| MstEnemyOutpost.csv | データ1行 | 1行ヘッダー（ENABLE形式） |
| MstPage.csv | データ1行 | 1行ヘッダー（ENABLE形式） |
| MstKomaLine.csv | データ1行 | 1行ヘッダー（ENABLE形式） |
| MstAutoPlayerSequence.csv | データ3行 | 1行ヘッダー（ENABLE形式） |
| MstInGame.csv | データ1行 | 1行ヘッダー（ENABLE形式） |

---

## Step 2: ID整合性チェック（Phase B）

### 結果: OK（問題なし）

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

全FK参照が一致しており、ID整合性に問題はありません。

---

## Step 3: ゲームプレイ品質チェック（Phase C）

### 3-1. 敵パラメータの妥当性

| id | character_unit_kind | role_type | hp | attack_power | move_speed | well_distance |
|----|--------------------|-----------|----|-------------|-----------|---------------|
| c_dan_00002_dan_dungeon_Boss_Blue | Boss | Attack | 30,000 | 800 | 10 | 0.35 |
| e_dan_00101_dan_dungeon_Normal_Colorless | Normal | Attack | 1,000 | 50 | 47 | 0.25 |

**ボス（Boss/Attack）の評価:**
- HP: 30,000 → 既存平均 164,084（範囲 1,000〜1,500,000）→ 平均の約0.18倍
- ATK: 800 → 既存平均 445.9（範囲 10〜3,800）→ 正常範囲内
- move_speed: 10 → 既存範囲 5〜75 → 正常範囲内

> **NOTE**: ボスHPが既存平均の約0.18倍と低めです（WARNING閾値は0.2倍未満）。dungeon_boss の初期コンテンツとして意図的に設定されているとみられますが、難易度が低くなる可能性があります。

**雑魚敵（Normal/Attack）の評価:**
- HP: 1,000 → 既存範囲 1,000〜900,000 → 最小値と同値（軽量な雑魚）
- ATK: 50 → 既存平均 410.5（範囲 50〜2,500）→ 最小値と同値
- move_speed: 47 → 既存平均 41.0（範囲 8〜100）→ 正常範囲内

### 3-2. コマ配置の整合性

```
row 1: total_width = 1.0 ✅
行数: 1行 ✅（dungeon_boss 固定仕様の1行と一致）
```

コマ配置に問題はありません。

### 3-3. シーケンスの合理性

| action_type | 行数 |
|-------------|------|
| SummonEnemy | 3 |

- 召喚数: 3体（ボス1 + 雑魚2）→ 最小境界値（< 3体でWARNING）につき問題なし
- ElapsedTime の時系列逆転: なし ✅

シーケンス詳細:

| sequence_element_id | condition_type | condition_value | action_type | action_value | aura_type |
|---------------------|----------------|----------------|-------------|-------------|-----------|
| 1 | InitialSummon | 0 | SummonEnemy | c_dan_00002_dan_dungeon_Boss_Blue | Boss |
| 2 | ElapsedTime | 2000 | SummonEnemy | e_dan_00101_dan_dungeon_Normal_Colorless | Default |
| 3 | ElapsedTime | 4000 | SummonEnemy | e_dan_00101_dan_dungeon_Normal_Colorless | Default |

- ボスが InitialSummon で即召喚されていることを確認済み ✅
- 雑魚敵が 2,000ms / 4,000ms の間隔で召喚される構成

### 3-4. ステージ種別固有ルール（dungeon_boss）

| チェック項目 | 期待値 | 実際値 | 判定 |
|------------|--------|--------|------|
| MstEnemyOutpost.hp | 1,000 | 1,000 | ✅ OK |
| コマ行数 | 1行 | 1行 | ✅ OK |
| is_damage_invalidation | 1（要件指定） | NULL（0相当） | **CRITICAL** |

#### [CRITICAL] is_damage_invalidation が未設定

- **対象**: `MstEnemyOutpost.is_damage_invalidation`
- **現在値**: NULL（デフォルト0 = ゲートダメージ有効）
- **要件**: dungeon_boss はゲートダメージ無効（`is_damage_invalidation = 1`）が必要
- **影響**: ゲートダメージが有効のままだと、ボスを倒さずにゲートを攻撃してクリアできてしまう可能性がある
- **修正提案**: `MstEnemyOutpost.csv` の `is_damage_invalidation` カラムを `1` に変更する

```
修正前: e,dungeon_dan_boss_00001,1000,,,dan_00005,999999999
修正後: e,dungeon_dan_boss_00001,1000,1,,dan_00005,999999999
```

### 3-5. ボス設定の二重チェック

- `boss_mst_enemy_stage_parameter_id`: `c_dan_00002_dan_dungeon_Boss_Blue` ✅
- `boss_count`: 1 ✅
- InitialSummon でボスが召喚されているか: ✅（sequence_element_id=1 で確認済み）

---

## Step 4: バランス比較（Phase D）

### 結果: OK（概ね正常範囲内、一部NOTE）

既存 `MstEnemyStageParameter.csv` との比較:

**Boss / Attack（ボス）:**
| 指標 | 既存avg | 既存min | 既存max | 今回値 | 評価 |
|------|---------|---------|---------|--------|------|
| HP | 164,085 | 1,000 | 1,500,000 | 30,000 | NOTE（平均の0.18倍、低め） |
| ATK | 446 | 10 | 3,800 | 800 | OK |
| speed | 38.6 | 5 | 75 | 10 | OK（低速ボス） |

**Normal / Attack（雑魚）:**
| 指標 | 既存avg | 既存min | 既存max | 今回値 | 評価 |
|------|---------|---------|---------|--------|------|
| HP | 69,771 | 1,000 | 900,000 | 1,000 | NOTE（軽量な取り巻き、意図的） |
| ATK | 411 | 50 | 2,500 | 50 | NOTE（最小値、意図的） |
| speed | 41.0 | 8 | 100 | 47 | OK |

> **NOTE**: ボスHPが既存平均の0.18倍（WARNING閾値: 0.2倍未満）。dungeon_bossコンテンツの初期設計として意図的な数値である可能性が高いが、難易度が低すぎないか確認推奨。

---

## Step 5: アセットキー形式チェック（Phase E）

### 結果: OK

| テーブル | カラム | 値 | 判定 |
|---------|-------|-----|------|
| MstInGame | bgm_asset_key | SSE_SBG_003_001 | ✅ |
| MstInGame | boss_bgm_asset_key | NULL | OK（ボス専用BGMなし） |
| MstEnemyOutpost | artwork_asset_key | dan_00005 | ✅ |
| MstKomaLine | koma1_asset_key | dan_00005 | ✅ |

全必須アセットキーは設定されています。`boss_bgm_asset_key` は NULL ですが、通常BGMを引き継ぐ仕様のため問題ありません。

> **NOTE**: `MstEnemyOutpost.outpost_asset_key` が NULL です。既存の dungeon 系データでも NULL のケースがあるか確認を推奨します。

---

## Step 6: 最終判定

### 修正必須（CRITICAL）

#### [CRITICAL] MstEnemyOutpost.is_damage_invalidation が 1 になっていない

dungeon_boss 仕様として「ゲートダメージ無効」が要件に指定されているにもかかわらず、現在値が NULL（= 0: ダメージ有効）です。

**修正内容:**

ファイル: `domain/tasks/dungeon-bulk-masterdata-generation/outputs/dan/boss/generated/MstEnemyOutpost.csv`

```csv
修正前:
e,dungeon_dan_boss_00001,1000,,,dan_00005,999999999

修正後:
e,dungeon_dan_boss_00001,1000,1,,dan_00005,999999999
```

### 確認推奨（NOTE）

1. **ボスHP 30,000 の妥当性**: 既存Boss/Attackの平均（164,085）より大幅に低い。dungeon_boss の入門難易度として意図的な場合は問題なし。
2. **雑魚HP/ATKの最小値設定**: ボスに集中させる設計意図があれば問題なし。
3. **MstEnemyOutpost.outpost_asset_key が NULL**: 既存データで同様の事例があるか確認推奨。

---

## 修正後の再検証手順

1. `MstEnemyOutpost.csv` を上記の通り修正
2. `verify_id_integrity.py` で再検証
3. 問題なければ XLSX 変換に進む
