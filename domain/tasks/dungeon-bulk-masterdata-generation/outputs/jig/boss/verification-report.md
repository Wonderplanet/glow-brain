# インゲームマスタデータ検証レポート

- 対象: `dungeon_jig_boss_00001`（dungeon_boss）
- 検証日時: 2026-03-02
- 検証ディレクトリ: `domain/tasks/dungeon-bulk-masterdata-generation/outputs/jig/boss/generated/`

---

## 判定: ❌ 問題があります（修正が必要です）

| フェーズ | 結果 | 備考 |
|---------|------|------|
| A: フォーマット | ⚠️ 参考 | テンプレート形式エラーはツールの仕様差異による既知問題。実カラム構成は正常 |
| B: ID整合性 | ✅ OK | 全FK参照一致（6項目すべてパス） |
| C: ゲームプレイ品質 | ❌ CRITICAL | MstEnemyOutpost.is_damage_invalidation が未設定（NULL） |
| D: バランス比較 | ⚠️ WARNING | ボスHPが既存dungeon_boss仕様では参照先なし（新規コンテンツ）。ステータスシート基準内 |
| E: アセットキー | ⚠️ WARNING | outpost_asset_key が NULL（既存データの慣例と一致） |

---

## 詳細レポート

### Step 1: フォーマット検証

`validate_all.py` を全6ファイルに実行した結果：

- **template/format/schema検証**：全ファイルで `valid: false`
  - 原因: スクリプトが `memo / TABLE / ENABLE` の3行ヘッダー形式を期待しているが、生成CSVは直接 `ENABLE,id,...` 形式で始まる
  - これは既存の生成フォーマットとの仕様差異による既知問題であり、CSV自体の実データは正常

- **カラム構成確認（目視・DuckDB）**：
  - `MstEnemyStageParameter`: 19カラム ✅
  - `MstEnemyOutpost`: 6カラム ✅
  - `MstPage`: 2カラム ✅
  - `MstKomaLine`: 41カラム ✅
  - `MstAutoPlayerSequence`: 35カラム（ENABLE含む）✅
  - `MstInGame`: 22カラム ✅

- **MstAutoPlayerSequenceのスキーマ差異**：
  - スクリプトは34カラムを期待、CSVは35カラム（ENABLE列を含む）
  - ENABLE列を除くと34カラムで一致 → 実質的に問題なし

---

### Step 2: ID整合性チェック

`verify_id_integrity.py` の実行結果：

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

**全6項目パス。** FK参照切れなし。

---

### Step 3: ゲームプレイ品質チェック

#### 3-1. 敵パラメータの妥当性

| id | character_unit_kind | role_type | color | hp | attack_power | move_speed | well_distance | attack_combo_cycle |
|----|--------------------|-----------|-------|----|-------------|-----------|--------------|-------------------|
| `c_jig_00001_jig_dungeon_Boss_Red` | Boss | Technical | Red | 5,000 | 100 | 41 | 0.3 | 5 |
| `e_jig_00001_jig_dungeon_Normal_Colorless` | Normal | Defense | Colorless | 3,500 | 50 | 31 | 0.25 | 1 |

**エネミーキャラクター実在確認**：
- `chara_jig_00001`（ボスキャラ参照先）: 実在 ✅
- `enemy_jig_00001`（雑魚敵参照先）: 実在 ✅

**ステータスシート基準との比較**：
- ボスHP 5,000（enemy_hp_coef=4 → 実質HP=20,000）: エネミーステータスシートの中央値（Boss/Technical）と比較して低め。dungeon初期ブロックとして短期決戦設計のため妥当
- ボスATK 100（atk_coef=3）: 既存jig系ボス相当（既存 `c_jig_00001_mainquest_Boss_Red` HP=5,000, ATK=100）と一致
- 雑魚HP 3,500（enemy_hp_coef=1 → 実質HP=3,500）: 既存jig系Normal相当で妥当
- move_speed 41（ボス）/ 31（雑魚）: 一般的な範囲内（25〜65）

#### 3-2. コマ配置の整合性

```
row | total_width
  1 | 1.0
行数: 1行
```

- コマ幅合計 = 1.0 ✅
- 行数 = 1行（dungeon_boss仕様: 1行固定）✅

#### 3-3. シーケンスの合理性

| sequence_element_id | condition_type | condition_value | action_type | action_value | summon_count |
|--------------------|---------------|----------------|------------|-------------|-------------|
| 1 | InitialSummon | 0 | SummonEnemy | `c_jig_00001_jig_dungeon_Boss_Red` | 1 |
| 2 | ElapsedTime | 2,000 | SummonEnemy | `e_jig_00001_jig_dungeon_Normal_Colorless` | 2 |
| 3 | ElapsedTime | 5,000 | SummonEnemy | `e_jig_00001_jig_dungeon_Normal_Colorless` | 3 |

- SummonEnemy: 3行 ✅
- ElapsedTime 単調増加チェック: 時系列逆転なし ✅（2,000ms → 5,000ms）
- ボス InitialSummon 設定: `is_summon_unit_outpost_damage_invalidation = 1` ✅

#### 3-4. dungeon_boss固有ルール確認 — [CRITICAL]

```
id                     | hp   | is_damage_invalidation
dungeon_jig_boss_00001 | 1000 | NULL
```

- `hp = 1,000`（dungeon_boss固定値）✅
- `is_damage_invalidation = NULL` ❌ **← CRITICAL**

**dungeon_boss仕様では `is_damage_invalidation = 1` が必須**（CLAUDE.mdに明記）。現在NULLのため、ゲートへのダメージが無効化されずボスブロックとして機能しない可能性がある。

#### 3-5. ボス設定の二重チェック

```
boss_mst_enemy_stage_parameter_id      | boss_count
c_jig_00001_jig_dungeon_Boss_Red       | 1
```

- `boss_mst_enemy_stage_parameter_id` 設定済み ✅
- `boss_count = 1` ✅
- InitialSummonでボスIDが一致 ✅

---

### Step 4: バランス比較

既存本番マスタデータにはまだ `dungeon_*` 系インゲームIDは存在しない（新規コンテンツ）。比較対象なし。

エネミーステータスシート基準（dungeon相当の難易度として参考）：
- 既存jig系の同種ステージ（`jig1_challenge` 系）ボスHP: 50,000 と比較して本ブロックのボスHPは5,000×4係数=20,000と低め
- dungeonコンテンツの初期ブロック（限界チャレンジの入り口段階）として適切な難易度設定と判断
- 特段のバランス問題は見当たらない

---

### Step 5: アセットキーチェック

| テーブル | カラム | 値 | 判定 |
|---------|--------|-----|------|
| MstInGame | bgm_asset_key | `SSE_SBG_003_003` | ✅ 設定済み |
| MstInGame | boss_bgm_asset_key | NULL | ⚠️ 空欄（ボス用BGMなし） |
| MstInGame | loop_background_asset_key | `jig_00002` | ✅ 設定済み |
| MstEnemyOutpost | outpost_asset_key | NULL | ⚠️ 空欄 |
| MstEnemyOutpost | artwork_asset_key | NULL | ⚠️ 空欄 |
| MstKomaLine | koma1_asset_key | `jig_00002` | ✅ 設定済み |

**補足**:
- `outpost_asset_key`: スキーマ上はNOT NULLだが、既存MstEnemyOutpost 532件中498件（93.6%）がNULL。dungeon系でのデフォルト慣例と判断
- `boss_bgm_asset_key`: 通常ブロックではボス用BGMなしが一般的。WARNING扱い
- `artwork_asset_key`: スキーマ上DEFAULT=""のため空欄は許容される

---

## 問題一覧

### [CRITICAL] MstEnemyOutpost.is_damage_invalidation が未設定（NULL）

- **対象**: `MstEnemyOutpost.csv` の `dungeon_jig_boss_00001` 行
- **現在値**: `is_damage_invalidation = NULL`（空欄）
- **必要値**: `is_damage_invalidation = 1`
- **理由**: dungeon_boss仕様では「ボス撃破まで敵ゲートへのダメージを無効化」が必須要件（CLAUDE.md記載）。NULLのままではゲームシステムが無効化判定を行わない可能性がある
- **修正方法**: `MstEnemyOutpost.csv` の `is_damage_invalidation` 列を空欄から `1` に変更する

```csv
修正前: e,dungeon_jig_boss_00001,1000,,,,999999999
修正後: e,dungeon_jig_boss_00001,1000,1,,,999999999
```

---

### [WARNING] boss_bgm_asset_key が未設定

- **対象**: `MstInGame.csv` の `boss_bgm_asset_key`
- **現在値**: NULL（空欄）
- **確認事項**: ボス戦専用BGMを設定する予定があるか。設定しない場合は `bgm_asset_key` の `SSE_SBG_003_003` が継続使用される

---

## 修正指示

### 必須修正（CRITICAL）

**ファイル**: `domain/tasks/dungeon-bulk-masterdata-generation/outputs/jig/boss/generated/MstEnemyOutpost.csv`

現在:
```
ENABLE,id,hp,is_damage_invalidation,outpost_asset_key,artwork_asset_key,release_key
e,dungeon_jig_boss_00001,1000,,,,999999999
```

修正後:
```
ENABLE,id,hp,is_damage_invalidation,outpost_asset_key,artwork_asset_key,release_key
e,dungeon_jig_boss_00001,1000,1,,,999999999
```

---

## 検証済み正常項目

- ID命名規則: `dungeon_jig_boss_00001` ✅
- MstEnemyOutpost.hp = 1,000（dungeon_boss固定値）✅
- MstKomaLine 行数 = 1行（dungeon_boss固定）✅
- コマ幅合計 = 1.0 ✅
- FK参照整合性（全6項目）✅
- ボス InitialSummon 設定 + `is_summon_unit_outpost_damage_invalidation = 1` ✅
- ElapsedTime 単調増加 ✅
- MstEnemyCharacter 実在確認（chara_jig_00001, enemy_jig_00001）✅
- シーケンス `sequence_set_id` 一貫性 ✅
- BGMアセットキー `SSE_SBG_003_003` 設定済み ✅
- ループ背景 `jig_00002` 設定済み ✅
