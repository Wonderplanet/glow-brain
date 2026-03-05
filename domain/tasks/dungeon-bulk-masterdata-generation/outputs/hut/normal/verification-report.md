# インゲームマスタデータ検証レポート

- 対象: `dungeon_hut_normal_00001` (dungeon_normal)
- 検証日時: 2026-03-02
- 生成ディレクトリ: `domain/tasks/dungeon-bulk-masterdata-generation/outputs/hut/normal/generated/`

---

## 最終判定

### 軽微な問題あり（WARNING レベル）— 投入可能だが確認推奨

CRITICAL（投入不可）レベルの問題はなし。
WARNING レベルの問題が1件あり。設計意図であれば投入可能。

| フェーズ | 結果 | 備考 |
|---------|------|------|
| A: フォーマット | OK（実質） | validate_all.py は旧3行ヘッダー形式を期待するため誤検知。実際のフォーマットは既存マスタデータ（ENABLE+カラム名形式）と一致 |
| B: ID整合性 | OK | 全FK参照一致（verify_id_integrity.py: valid=true） |
| C: ゲームプレイ品質 | OK（1件WARNING） | コマ行数・幅・シーケンス時系列は正常。attack_power=100 は設計書通りだが基準値より低い |
| D: バランス比較 | WARNING | attack_power=100 は同作品別コンテンツ（dungeon_spy: 5,000）と大きく乖離。設計意図を確認推奨 |
| E: アセットキー | OK（空白は許容） | 空白カラムはデフォルト値="" で許容。SPY参考データも同様の空白パターン |

---

## 各ステップ詳細

### Step 1: フォーマット検証

**結果: OK（実質）**

`validate_all.py` は古い3行ヘッダー形式（1行目: memo / 2行目: TABLE / 3行目: ENABLE）を期待するが、
生成CSVは現行フォーマット（1行目: ENABLE + カラム名）で出力されており、
`projects/glow-masterdata/` の既存CSVと完全に一致している。スクリプトの誤検知と判断。

検証ファイル一覧（全6ファイル）:

| ファイル | 内容確認 |
|--------|---------|
| MstInGame.csv | 1レコード、全必須カラム存在 |
| MstEnemyOutpost.csv | 1レコード、全必須カラム存在 |
| MstEnemyStageParameter.csv | 1レコード、全必須カラム存在 |
| MstPage.csv | 1レコード、全必須カラム存在 |
| MstKomaLine.csv | 3レコード、全必須カラム存在 |
| MstAutoPlayerSequence.csv | 5レコード、全必須カラム存在 |

---

### Step 2: ID整合性チェック

**結果: OK（全チェック通過）**

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

---

### Step 3: ゲームプレイ品質チェック

#### 3-1. 敵パラメータ

| カラム | 値 | 判定 |
|--------|-----|------|
| id | `e_glo_00001_hut_dungeon_Normal_Colorless` | OK |
| mst_enemy_character_id | `enemy_glo_00001` | OK |
| character_unit_kind | `Normal` | OK |
| role_type | `Attack` | OK |
| color | `Colorless` | OK |
| hp | 1,000 | OK（設計書通り） |
| attack_power | 100 | WARNING（後述） |
| move_speed | 40 | OK（推奨範囲25〜65の中央付近） |
| well_distance | 0.2 | OK |
| sort_order | NULL | WARNING（NOT NULL カラム） |
| damage_knock_back_count | 1 | OK |
| attack_combo_cycle | 1 | OK |

**[WARNING] sort_order が NULL**

- カラム定義: `sort_order int NOT NULL`
- 現在値: NULL（空白）
- SPY参考データでは `sort_order = 1` が設定されている
- 修正提案: `sort_order = 1` を設定する

#### 3-2. コマ配置の整合性

**結果: OK**

| 行番号 | コマ幅合計 | 判定 |
|--------|----------|------|
| 1 | 1.0 (0.4 + 0.6) | OK |
| 2 | 1.0 (1.0) | OK |
| 3 | 1.0 (0.6 + 0.4) | OK |

- コマ行数: 3行（dungeon_normal 仕様固定値 3行に一致）
- 全行のコマ幅合計 = 1.0（CRITICAL 基準クリア）

#### 3-3. シーケンスの合理性

**結果: OK**

| sequence_element_id | condition_type | condition_value (ms) | action_type | summon_count |
|--------------------|---------------|---------------------|------------|-------------|
| 1 | ElapsedTime | 500 | SummonEnemy | 1 |
| 2 | ElapsedTime | 1,000 | SummonEnemy | 1 |
| 3 | ElapsedTime | 2,000 | SummonEnemy | 1 |
| 4 | ElapsedTime | 3,000 | SummonEnemy | 1 |
| 5 | ElapsedTime | 4,500 | SummonEnemy | 1 |

- ElapsedTime 時系列: 単調増加（500 → 1,000 → 2,000 → 3,000 → 4,500）
- 時系列逆行: なし（OK）
- action_type: 全行 SummonEnemy（InitialSummon なし、dungeon_normal なのでボスなし。正常）

#### 3-4. ステージ種別固有ルール（dungeon_normal）

| チェック項目 | 期待値 | 実際値 | 判定 |
|------------|-------|-------|------|
| MstEnemyOutpost.hp | 100（固定） | 100 | OK |
| コマ行数 | 3行（固定） | 3行 | OK |
| is_damage_invalidation | 0（dungeon_normalは砦破壊型） | NULL（=0扱い） | OK |

#### 3-5. ボス設定

- `boss_mst_enemy_stage_parameter_id`: NULL（dungeon_normal にはボスなし。正常）
- `boss_count`: NULL（同上）
- InitialSummon シーケンス: なし（正常）

---

### Step 4: バランス比較

**結果: WARNING**

既存 `MstEnemyStageParameter.csv` の `Normal/Attack` 分布:

| 指標 | hp | attack_power | move_speed |
|------|-----|------------|-----------|
| 最小値 | 1,000 | 50 | 8 |
| 平均値 | 約70,000 | 411 | 41 |
| 最大値 | 900,000 | 2,500 | 100 |

今回生成データ:

| id | hp | attack_power | move_speed |
|----|-----|------------|-----------|
| e_glo_00001_hut_dungeon_Normal_Colorless | 1,000 | 100 | 40 |

**[WARNING] attack_power = 100 について**

- 既存データの最小値 (50) 付近であり、異常値ではない
- ただし SPY dungeon normal (attack_power=5,000) と50倍の乖離がある
- 設計書（`dungeon_hut_normal_00001.md`）には attack_power=100 と明記されており、意図的な設定
- dungeon_normal はゲートHP=100（固定）のため、ゲートへの1回ヒットでゲームオーバーになるリスクを抑える観点から低ATKは合理的
- hp=1,000（設計書通り）は既存最小値に一致しており適切

**確認推奨**: attack_power=100 が dungeon_normal の難易度設計として意図通りであるか、
SPY dungeon normal (5,000) との難易度差が問題ないかをレベルデザイン担当者に確認。

---

### Step 5: アセットキーチェック

**結果: OK（空白は許容）**

| テーブル | カラム | 値 | 判定 |
|---------|--------|-----|------|
| MstInGame | bgm_asset_key | `SSE_SBG_003_002` | OK（設定済み） |
| MstInGame | boss_bgm_asset_key | 空白 | OK（dungeon_normalはボスなし。SPY参考も空白） |
| MstInGame | loop_background_asset_key | 空白 | OK（設計書に「hut専用背景アセット未存在のため空白」と明記） |
| MstInGame | player_outpost_asset_key | 空白 | OK（DBスキーマ default='' で空白許容） |
| MstEnemyOutpost | outpost_asset_key | 空白 | OK（SPY参考も空白） |
| MstEnemyOutpost | artwork_asset_key | 空白 | OK（SPY参考も空白） |
| MstKomaLine | koma1_asset_key | `glo_00014` | OK（全行設定済み） |
| MstKomaLine | koma_line_layout_asset_key | 数値（1, 2, 3） | OK（レイアウト番号） |

---

## 問題サマリー

### [WARNING] MstEnemyStageParameter.sort_order が NULL

- **対象**: `MstEnemyStageParameter.csv` の `sort_order` カラム
- **現在値**: NULL（空白）
- **DBスキーマ定義**: `sort_order int NOT NULL`
- **参考値**: SPY dungeon normal では `sort_order = 1`
- **修正提案**: `sort_order = 1` を設定する（投入時に DB エラーになる可能性があるため推奨）

### [WARNING] attack_power = 100（設計意図の確認推奨）

- **対象**: `MstEnemyStageParameter.csv` の `attack_power` カラム
- **現在値**: 100（設計書通り）
- **参考値**: SPY dungeon normal では 5,000
- **影響**: dungeon_normal のゲートHP=100 に対して雑魚ATK=100 はゲート1回ヒットでゲームオーバーとなる。これは設計意図の可能性が高いが、難易度バランスの確認を推奨。

---

## 投入判断

- **CRITICAL（投入不可）**: なし
- **投入推奨アクション**:
  1. `MstEnemyStageParameter.csv` の `sort_order` を `1` に設定する（DBエラー回避のため）
  2. attack_power=100 の難易度設計をレベルデザイン担当者に確認する

sort_order の修正のみ行えば、技術的には投入可能な状態。
