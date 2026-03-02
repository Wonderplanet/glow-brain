# 検証レポート: dungeon_osh_normal_00001

- 検証日時: 2026-03-02
- 対象ディレクトリ: `domain/tasks/dungeon-bulk-masterdata-generation/outputs/osh/normal/generated/`
- ステージ種別: dungeon_normal
- インゲームID: `dungeon_osh_normal_00001`

---

## 総合判定: PASS

全ての実質的な検証項目（ID整合性・ゲームプレイ品質・dungeon_normal固有チェック）に合格しました。

---

## Step 1: フォーマット検証

### 結果: 警告あり（実質問題なし）

全6ファイルで同一パターンの警告が発生しました。

**警告内容**: `validate_csv_format.py` / `validate_template.py` が3行ヘッダー形式（memo行 / TABLE行 / ENABLE行）を期待しているのに対して、生成CSVは1行ヘッダー形式（ENABLE行のみ）を使用しているため。

**判断**: これはバリデーターとCSV生成仕様の差異によるものです。spy参考データ（`domain/tasks/masterdata-entry/masterdata-ingame-creator/20260301_131508_dungeon_spy_normal_block/generated/`）も同じ1行ヘッダー形式を採用しており、このタスクのCSV仕様として意図された形式です。実質的な問題はありません。

| ファイル | 行数 | ヘッダー形式警告 |
|---------|-----|----------------|
| MstAutoPlayerSequence.csv | 6行（データ5行） | あり（仕様差異） |
| MstEnemyOutpost.csv | 2行（データ1行） | あり（仕様差異） |
| MstEnemyStageParameter.csv | 3行（データ2行） | あり（仕様差異） |
| MstInGame.csv | 2行（データ1行） | あり（仕様差異） |
| MstKomaLine.csv | 4行（データ3行） | あり（仕様差異） |
| MstPage.csv | 2行（データ1行） | あり（仕様差異） |

---

## Step 2: ID整合性チェック

### 結果: PASS（全項目合格）

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

---

## Step 3: ゲームプレイ品質チェック

### 3-1. MstEnemyOutpost.hp 確認

| id | hp | is_damage_invalidation |
|----|----|----------------------|
| dungeon_osh_normal_00001 | **100** | NULL |

**結果: PASS** - hp = 100（dungeon_normal 固定値）

---

### 3-2. KomaLine 行数とコマ幅合計確認

| row | total_width |
|-----|-------------|
| 1 | **1.0** |
| 2 | **1.0** |
| 3 | **1.0** |

**結果: PASS** - 3行あり、全行のコマ幅合計 = 1.0

---

### 3-3. ElapsedTime 単調増加確認

違反行数: **0件**

シーケンス詳細（参考）:

| sequence_element_id | condition_type | condition_value | action_value | summon_count |
|--------------------|----------------|-----------------|--------------|--------------|
| 1 | ElapsedTime | 200 | e_glo_00002_general_osh_n_Normal_Colorless | 1 |
| 2 | ElapsedTime | 1000 | e_glo_00001_general_osh_n_Normal_Yellow | 2 |
| 3 | ElapsedTime | 2500 | e_glo_00001_general_osh_n_Normal_Yellow | 2 |
| 4 | ElapsedTime | 4000 | e_glo_00001_general_osh_n_Normal_Yellow | 3 |
| 5 | ElapsedTime | 6000 | e_glo_00002_general_osh_n_Normal_Colorless | 2 |

**結果: PASS** - ElapsedTime 単調増加（200 → 1000 → 2500 → 4000 → 6000）

---

### 3-4. EnemyStageParameter 確認

| id | character_unit_kind | role_type | hp | attack_power | move_speed | well_distance |
|----|--------------------|-----------|----|-------------|-----------|--------------|
| e_glo_00001_general_osh_n_Normal_Yellow | Normal | Attack | 1000 | 100 | 34 | 0.35 |
| e_glo_00002_general_osh_n_Normal_Colorless | Normal | Attack | 1000 | 100 | 47 | 0.35 |

**結果: PASS** - 2体のパラメータが正常に設定されている

---

### 3-5. MstInGame 確認

| id | boss_count | boss_mst_enemy_stage_parameter_id |
|----|-----------|----------------------------------|
| dungeon_osh_normal_00001 | **0** | NULL |

**結果: PASS** - boss_count = 0（dungeon_normal 仕様通り）、ボスパラメータIDなし

---

## dungeon_normal 固有チェック項目まとめ

| チェック項目 | 期待値 | 実際値 | 判定 |
|------------|--------|--------|------|
| MstEnemyOutpost.hp | 100（固定） | 100 | PASS |
| KomaLine 行数 | 3行（固定） | 3行 | PASS |
| コマ幅合計（全行） | 1.0 | 1.0（全3行） | PASS |
| boss_count | 0 | 0 | PASS |
| ElapsedTime 単調増加 | 違反なし | 違反なし | PASS |

---

## ファイル内容サマリー

### MstPage.csv
- `dungeon_osh_normal_00001` 1件

### MstEnemyOutpost.csv
- `dungeon_osh_normal_00001` (hp=100)

### MstKomaLine.csv
- row 1: コマ2枚（幅 0.5 + 0.5 = 1.0）、アセット: osh_00001
- row 2: コマ3枚（幅 0.33 + 0.34 + 0.33 = 1.0）、アセット: osh_00001
- row 3: コマ2枚（幅 0.5 + 0.5 = 1.0）、アセット: osh_00001

### MstEnemyStageParameter.csv
- Yellow（雑魚メイン）: HP=1000, ATK=100, SPD=34
- Colorless（雑魚サブ）: HP=1000, ATK=100, SPD=47

### MstAutoPlayerSequence.csv
- シーケンス5件（ElapsedTime 200〜6000ms）

### MstInGame.csv
- BGM: `SSE_SBG_003_003`
- 背景アセット: `osh_00001`
- boss_count: 0
- description.ja: 「黄属性の敵と無属性の敵が登場するぞ！緑属性のキャラは黄属性の敵に対して有利に戦うことができるぞ！」

---

## 特記事項

- フォーマット検証のヘッダー形式警告は、バリデーターと生成CSVの仕様差異によるものであり、データの品質上の問題ではない。SPY×FAMILY参考データも同じ形式を採用しており、このタスク全体の生成仕様として統一されている。
- 実質的なデータ品質（ID整合性・パラメータ値・dungeon_normal固有ルール）は全て問題なし。
