# インゲームマスタデータ 検証レポート

- **対象**: `dungeon_yuw_normal_00001`
- **ステージ種別**: dungeon_normal
- **検証日時**: 2026-03-02
- **検証ディレクトリ**: `domain/tasks/dungeon-bulk-masterdata-generation/outputs/yuw/normal/generated/`

---

## Step 1: フォーマット検証

| ファイル | テンプレート | CSV形式 | DBスキーマ | Enum値 | 総合 |
|---------|------------|--------|-----------|--------|------|
| MstAutoPlayerSequence.csv | OK | OK (8行) | OK | OK | **PASS** |
| MstEnemyOutpost.csv | OK | OK (4行) | OK | OK | **PASS** |
| MstEnemyStageParameter.csv | OK | OK (5行) | OK | OK | **PASS** |
| MstInGame.csv | OK | OK (4行) | WARNING | OK | **WARNING** |
| MstKomaLine.csv | OK | OK (6行) | OK | OK | **PASS** |
| MstPage.csv | OK | OK (4行) | OK | OK | **PASS** |

### MstInGame.csv DBスキーマ検証の補足

- validate_all.py の報告: `カラム数が一致しません（期待: 19, 実際: 21）`
- 原因: DBスキーマ（`mst_in_games`）は19カラムだが、CSVには `result_tips.ja` と `description.ja` の2カラムが追加されている
- 判定: **問題なし（意図的な拡張）**
  - これらは `MstInGameI18n` の拡張カラムであり、sheet_schema（`projects/glow-masterdata/sheet_schema/MstInGame.csv`）の3行目（ENABLE行）に正式に定義されている
  - yuwのCSVはsheet_schema準拠のフォーマットに従っており、SPY参考実装との差分は単にi18nカラムの有無のみ

---

## Step 2: ID整合性チェック

`verify_id_integrity.py` の実行結果は **スクリプト自体のSQLエラー** により全チェックが失敗扱いとなった（CSVのメタ行構造（memo/TABLE/ENABLE行）に対応していないSQLクエリによるBinder Error）。

手動検証の結果は以下の通り:

| チェック項目 | 結果 | 詳細 |
|------------|------|------|
| MstInGame.id | OK | `dungeon_yuw_normal_00001` |
| MstAutoPlayerSequence.sequence_set_id | OK | `dungeon_yuw_normal_00001` (全5行) |
| MstPage.id | OK | `dungeon_yuw_normal_00001` |
| MstEnemyOutpost.id | OK | `dungeon_yuw_normal_00001` |
| MstAutoPlayerSequence.action_value FK | OK | `c_yuw_00001_dungeon_Normal_Yellow`, `c_yuw_00101_dungeon_Normal_Green` が MstEnemyStageParameter に存在 |
| MstKomaLine.mst_page_id | OK | `dungeon_yuw_normal_00001` (全3行) |

**ID一貫性のまとめ**: MstInGame.id = MstAutoPlayerSequence.sequence_set_id = MstPage.id = MstEnemyOutpost.id = `dungeon_yuw_normal_00001` で統一されており正常。

---

## Step 3: ゲームプレイ品質チェック

### MstEnemyOutpost: HP固定値チェック

| id | hp | is_damage_invalidation |
|----|----|------------------------|
| dungeon_yuw_normal_00001 | 100 | （空欄） |

- dungeon_normal 固有要件 `hp = 100`: **PASS**

### MstKomaLine: コマ行数・コマ幅合計チェック

| row | koma1_width | koma2_width | koma3_width | koma4_width | total_width |
|-----|------------|------------|------------|------------|-------------|
| 1 | 0.6 | 0.4 | 0.0 | 0.0 | 1.0 |
| 2 | 0.75 | 0.25 | 0.0 | 0.0 | 1.0 |
| 3 | 1.0 | 0.0 | 0.0 | 0.0 | 1.0 |

- dungeon_normal 固有要件 `KomaLine行数 = 3行`: **PASS**
- コマ幅合計 = 1.0: **全行 PASS**

### MstAutoPlayerSequence: ElapsedTime 昇順チェック

| sequence_element_id | condition_value | 順序 |
|--------------------|-----------------|----|
| 1 | 5.0 | OK |
| 2 | 20.0 | OK |
| 3 | 40.0 | OK |
| 4 | 60.0 | OK |
| 5 | 80.0 | OK |

- ElapsedTime の昇順単調増加: **PASS**

### MstEnemyStageParameter: 敵パラメータ

| id | character_unit_kind | role_type | hp | attack_power | move_speed |
|----|--------------------|-----------|----|--------------|------------|
| c_yuw_00001_dungeon_Normal_Yellow | Normal | Attack | 10000 | 320 | 34 |
| c_yuw_00101_dungeon_Normal_Green | Normal | Technical | 12000 | 280 | 29 |

- 2種類の雑魚敵（Yellow/Green）が設定されており妥当
- character_unit_kind = Normal: **PASS**

### MstInGame: boss_count チェック

| id | boss_count |
|----|-----------|
| dungeon_yuw_normal_00001 | （空欄） |

- dungeon_normal 固有要件 `boss_count = 0`: **WARNING - 要確認**
  - 現在 `boss_count` が空欄になっている
  - SPY×FAMILY参考実装（`dungeon_spy_normal_00001`）では `boss_count = 0` が明示的に設定されている
  - 既存マスタデータでは `boss_count` は `''`（空欄）か `'1'` のいずれかであり、空欄もノーマルステージで使われているケースがある
  - ただし、dungeon_normalでの明示的な `0` 設定が推奨される

---

## Step 4: dungeon_normal 固有チェックサマリー

| チェック項目 | 要件値 | 実際の値 | 判定 |
|------------|-------|---------|------|
| MstEnemyOutpost.hp | 100 | 100 | **PASS** |
| KomaLine行数 | 3行 | 3行 | **PASS** |
| コマ幅合計（全行） | 1.0 | 1.0, 1.0, 1.0 | **PASS** |
| boss_count | 0 | （空欄） | **WARNING** |

---

## 追加確認事項

### MstInGame.mst_auto_player_sequence_id が空欄

- 現在の値: 空欄
- SPY参考実装の値: `dungeon_spy_normal_00001`（sequence_set_idと同一）
- 既存マスタデータの調査結果: PVP系（24件）のみ空欄。それ以外は全件 `mst_auto_player_sequence_id == mst_auto_player_sequence_set_id`
- 判定: **WARNING - 要修正推奨**
  - `mst_auto_player_sequence_id` に `dungeon_yuw_normal_00001` を設定することを推奨

---

## 総合判定

| 区分 | 結果 |
|------|------|
| フォーマット検証 | PASS（MstInGame.csvのi18n拡張は意図的） |
| ID整合性 | PASS |
| ゲームプレイ品質 | PASS（boss_countの空欄に要注意） |
| dungeon_normal 固有ルール | **PARTIAL PASS** |

### 要修正事項

1. **WARNING（修正推奨）**: `MstInGame.boss_count` が空欄
   - 現在: 空欄
   - 推奨値: `0`
   - 根拠: SPY参考実装（dungeon_spy_normal_00001）で `0` が設定されている

2. **WARNING（修正推奨）**: `MstInGame.mst_auto_player_sequence_id` が空欄
   - 現在: 空欄
   - 推奨値: `dungeon_yuw_normal_00001`
   - 根拠: 既存データではPVP以外の全ステージで `mst_auto_player_sequence_id == mst_auto_player_sequence_set_id`

### 問題なし（PASS）として確定した事項

- 全CSVのフォーマット（列順・型）
- 全テーブル間のID一貫性（dungeon_yuw_normal_00001で統一）
- MstEnemyOutpost.hp = 100（固定）
- KomaLine 3行構成
- 全コマ幅合計 = 1.0
- ElapsedTimeの昇順配置（5, 20, 40, 60, 80秒）
- MstAutoPlayerSequence.action_value の FK整合性
- MstInGameI18n拡張カラム（result_tips.ja, description.ja）のテキスト設定

---

## 推奨アクション

MstInGame.csvの以下2カラムを修正してから XLSX 出力に進むことを推奨します。

```csv
boss_count: '' → '0' に修正
mst_auto_player_sequence_id: '' → 'dungeon_yuw_normal_00001' に修正
```
