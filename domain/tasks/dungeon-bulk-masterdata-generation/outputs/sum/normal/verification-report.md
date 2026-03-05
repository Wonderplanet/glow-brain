# 検証レポート: dungeon_sum_normal_00001

- **検証日時**: 2026-03-02
- **対象ディレクトリ**: `domain/tasks/dungeon-bulk-masterdata-generation/outputs/sum/normal/generated/`
- **ステージ種別**: dungeon_normal
- **インゲームID**: `dungeon_sum_normal_00001`

---

## 総合判定: WARNING（要修正2件あり）

| チェック項目 | 結果 | 詳細 |
|------------|------|------|
| ID整合性 | PASS | 全FK参照OK |
| EnemyOutpost HP | PASS | hp=100（固定値OK） |
| KomaLine行数 | PASS | 3行（固定値OK） |
| コマ幅合計 | PASS | 全行1.0 |
| ElapsedTime昇順 | PASS | 逆順なし |
| boss_count=0 | **FAIL** | 空文字列（要修正） |
| mst_auto_player_sequence_id | **FAIL** | 空文字列（要修正） |
| CSVカラム定義 | PASS | テンプレートと一致 |
| CSVヘッダー形式 | INFO | memo/TABLE行なし（このフォーマットは他の生成済みCSVと同様） |

---

## Step 1: フォーマット検証

### ヘッダー形式

全CSVファイルにテンプレート形式（`memo` / `TABLE,...` / `ENABLE,...`）のヘッダー3行が存在せず、
`ENABLE,...` から始まる1行ヘッダー形式になっています。

これはvalidate_template.pyにおいて「ヘッダー形式不備」と判定されましたが、
**カラム定義そのものはテンプレートと完全一致**しており、他の作品の生成済みCSV（SPY等）と同じフォーマットです。

### カラム照合結果

| ファイル | カラム一致 | 備考 |
|---------|-----------|------|
| MstAutoPlayerSequence.csv | PASS | テンプレートと完全一致 |
| MstEnemyOutpost.csv | PASS | テンプレートと完全一致 |
| MstEnemyStageParameter.csv | PASS | テンプレートと完全一致 |
| MstInGame.csv | PASS | テンプレートと完全一致 |
| MstKomaLine.csv | PASS | テンプレートと完全一致 |
| MstPage.csv | PASS | テンプレートと完全一致 |

---

## Step 2: ID整合性チェック

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
  "issues": [],
  "summary": {
    "total_issues": 0,
    "critical_issues": 0,
    "warnings": 0
  }
}
```

**結果: PASS** — 全FK参照OK、ID命名・整合性に問題なし。

---

## Step 3: ゲームプレイ品質チェック

### MstEnemyOutpost HP

```
┌──────────────────────────┬───────┐
│ id                       │ hp    │
├──────────────────────────┼───────┤
│ dungeon_sum_normal_00001 │  100  │
└──────────────────────────┴───────┘
```

**PASS** — hp=100（dungeon_normal固定値）

### KomaLine コマ幅合計

```
┌──────┬─────────────┐
│ row  │ total_width │
├──────┼─────────────┤
│    1 │         1.0 │
│    2 │         1.0 │
│    3 │         1.0 │
└──────┴─────────────┘
```

**PASS** — 3行すべてtotal_width=1.0、行数=3（dungeon_normal固定値）

### ElapsedTime 昇順チェック

逆順レコード: 0件

**PASS** — 500→1500→2500→4000→5500と昇順に設定されている

### MstEnemyStageParameter

```
┌──────────────────────────────────────┬─────────────────────┬───────────┬───────┬──────────────┬────────────┐
│ id                                   │ character_unit_kind │ role_type │ hp    │ attack_power │ move_speed │
├──────────────────────────────────────┼─────────────────────┼───────────┼───────┼──────────────┼────────────┤
│ e_sum_00001_general_Normal_Colorless │ Normal              │ Defense   │ 15000 │ 200          │ 40         │
│ e_sum_00001_general_Normal_Yellow    │ Normal              │ Defense   │ 26000 │ 300          │ 40         │
└──────────────────────────────────────┴─────────────────────┴───────────┴───────┴──────────────┴────────────┘
```

**PASS** — 無属性・黄属性の2種類、HP/攻撃/速度の値は適切な範囲内

### MstInGame boss_count

```
┌──────────────────────────┬────────────┐
│ id                       │ boss_count │
├──────────────────────────┼────────────┤
│ dungeon_sum_normal_00001 │ NULL       │
└──────────────────────────┴────────────┘
```

**FAIL** — boss_countが空文字列（CSVでは `''`）になっている。dungeon_normalでは `0` が正しい値。

---

## 問題点と修正事項

### 問題1（CRITICAL）: MstInGame.boss_count が空文字列

- **対象ファイル**: `MstInGame.csv`
- **対象行**: row 2（データ行）、`boss_count` カラム
- **現在値**: `''`（空文字列）
- **期待値**: `0`
- **影響**: ゲームプレイ時にボス出現数が未定義になる可能性あり
- **参考**: SPY参考データ（`20260301_131508_dungeon_spy_normal_block/generated/MstInGame.csv`）では `boss_count = '0'`

### 問題2（CRITICAL）: MstInGame.mst_auto_player_sequence_id が空文字列

- **対象ファイル**: `MstInGame.csv`
- **対象行**: row 2（データ行）、`mst_auto_player_sequence_id` カラム
- **現在値**: `''`（空文字列）
- **期待値**: `dungeon_sum_normal_00001`
- **影響**: シーケンス参照が欠落し、敵出現スクリプトが動作しない可能性あり
- **参考**: SPY参考データでは `mst_auto_player_sequence_id = 'dungeon_spy_normal_00001'`

---

## 修正手順

`MstInGame.csv` の2行目（データ行）を以下のように修正してください：

**修正前:**
```
e,dungeon_sum_normal_00001,,dungeon_sum_normal_00001,SSE_SBG_003_003,...
```

**修正後:**
```
e,dungeon_sum_normal_00001,dungeon_sum_normal_00001,dungeon_sum_normal_00001,SSE_SBG_003_003,...
```

また `boss_count` フィールド（13列目）を空文字から `0` に変更してください。

---

## 正常確認項目サマリー

| 項目 | 値 | 判定 |
|------|-----|------|
| MstEnemyOutpost.hp | 100 | PASS |
| KomaLine行数 | 3 | PASS |
| 全行コマ幅合計 | 1.0 | PASS |
| ElapsedTime昇順 | 500→1500→2500→4000→5500 | PASS |
| ID整合性（全FK） | 問題なし | PASS |
| MstInGame.boss_count | 空文字（要修正→0） | FAIL |
| MstInGame.mst_auto_player_sequence_id | 空文字（要修正） | FAIL |
