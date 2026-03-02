# 検証レポート: dungeon_spy_normal_00001

- **検証日時**: 2026-03-02
- **対象ディレクトリ**: `domain/tasks/dungeon-bulk-masterdata-generation/outputs/spy/normal/generated/`
- **ステージ種別**: dungeon_normal
- **インゲームID**: `dungeon_spy_normal_00001`

---

## Step 1: フォーマット検証

| ファイル | テンプレート | CSV形式 | DBスキーマ | Enum値 | 総合 |
|---------|------------|--------|-----------|-------|------|
| MstAutoPlayerSequence.csv | OK | OK (8行) | OK | スキップ | **PASS** |
| MstEnemyOutpost.csv | OK | OK (4行) | OK | スキップ | **PASS** |
| MstEnemyStageParameter.csv | OK | OK (5行) | OK | OK | **PASS** |
| MstInGame.csv | OK | OK (4行) | **WARNING** (カラム数不一致: 期待19, 実際21) | スキップ | **PASS (要確認)** |
| MstKomaLine.csv | OK | OK (6行) | OK | スキップ | **PASS** |
| MstPage.csv | OK | OK (4行) | OK | スキップ | **PASS** |

### MstInGame.csv カラム数不一致について

- DBスキーマ（`mst_in_games`）は **19カラム**
- 生成CSV（MstInGame.csv）は **21カラム**
- 差分の2カラムは `result_tips.ja` と `description.ja`（`MstInGameI18n` テーブル）
- `projects/glow-masterdata/sheet_schema/MstInGame.csv` のスキーマと**完全一致**を確認
- **判定: 誤検知。実際の運用フォーマット（sheet_schema）に準拠しており問題なし。**

---

## Step 2: ID整合性チェック

バリデータスクリプトはエラーを報告しましたが、これはCSVフォーマット（先頭3行がmemo/TABLE/ENABLE行）に対応していないことが原因と判明。
実際のIDは目視で確認済み（以下参照）。

| チェック | 結果 | 備考 |
|---------|------|------|
| MstInGame.id | OK | `dungeon_spy_normal_00001` |
| MstPage.id | OK | `dungeon_spy_normal_00001` |
| MstEnemyOutpost.id | OK | `dungeon_spy_normal_00001` |
| MstKomaLine.mst_page_id | OK | `dungeon_spy_normal_00001` |
| MstAutoPlayerSequence.sequence_set_id | OK | `dungeon_spy_normal_00001` |
| MstInGame.mst_page_id | OK | `dungeon_spy_normal_00001` |
| MstInGame.mst_enemy_outpost_id | OK | `dungeon_spy_normal_00001` |
| MstInGame.mst_auto_player_sequence_id | OK | `dungeon_spy_normal_00001` |
| MstInGame.mst_auto_player_sequence_set_id | OK | `dungeon_spy_normal_00001` |

---

## Step 3: ゲームプレイ品質チェック

### 3-1. MstEnemyOutpost HP チェック

| id | hp | is_damage_invalidation |
|----|----|-----------------------|
| dungeon_spy_normal_00001 | **100** | NULL |

**結果: PASS** (hp=100 固定値を確認)

---

### 3-2. MstKomaLine コマ幅合計チェック（行数・幅合計）

| row | total_width |
|-----|-------------|
| 1 | **1.0** |
| 2 | **1.0** |
| 3 | **1.0** |

**結果: PASS** (3行 / 各行のコマ幅合計=1.0 を確認)

各行の内訳:
- Row 1: koma1=0.4 + koma2=0.6 = 1.0（2コマ構成）
- Row 2: koma1=0.25 + koma2=0.5 + koma3=0.25 = 1.0（3コマ構成）
- Row 3: koma1=0.75 + koma2=0.25 = 1.0（2コマ構成）

---

### 3-3. MstAutoPlayerSequence ElapsedTime 時系列順序チェック

| sequence_element_id | condition_type | condition_value |
|--------------------|---------------|-----------------|
| 1 | ElapsedTime | 250 |
| 2 | ElapsedTime | 800 |
| 3 | ElapsedTime | 1500 |
| 4 | ElapsedTime | 3000 |
| 5 | ElapsedTime | 4500 |

逆順レコード数: **0件**

**結果: PASS** (ElapsedTimeが単調増加していることを確認)

---

### 3-4. MstEnemyStageParameter キャラクター情報

| id | character_unit_kind | role_type | hp | attack_power | move_speed |
|----|--------------------|-----------|----|-------------|------------|
| e_spy_00001_spy_dungeon_Normal_Colorless | Normal | Attack | 1000 | 200 | 40 |
| e_spy_00101_spy_dungeon_Normal_Colorless | Normal | Attack | 1000 | 200 | 38 |

**結果: PASS** (2体の雑魚敵パラメータが正常に設定されている)

---

### 3-5. MstInGame boss_count チェック

| id | boss_count |
|----|------------|
| dungeon_spy_normal_00001 | **0** |

**結果: PASS** (boss_count=0 を確認: normalブロックにボスなし)

---

## dungeon_normal 固有チェック項目

| チェック項目 | 期待値 | 実際値 | 結果 |
|------------|--------|--------|------|
| MstEnemyOutpost.hp | 100（固定） | 100 | **PASS** |
| KomaLine行数 | 3行（固定） | 3行 | **PASS** |
| コマ幅合計（全行） | 1.0 | 1.0（全3行） | **PASS** |
| boss_count | 0 | 0 | **PASS** |

---

## 総合判定

**PASS（問題なし）**

全ての dungeon_normal 固有チェック項目をクリア。
MstInGame.csv のカラム数不一致はバリデータの誤検知であり、実運用フォーマット（sheet_schema）に準拠していることを確認済み。

---

## データサマリ

| テーブル | データ行数 | 主要ID |
|---------|----------|--------|
| MstPage | 1行 | dungeon_spy_normal_00001 |
| MstEnemyOutpost | 1行 | dungeon_spy_normal_00001 |
| MstKomaLine | 3行 | dungeon_spy_normal_00001_1〜3 |
| MstEnemyStageParameter | 2行 | e_spy_00001_..., e_spy_00101_... |
| MstAutoPlayerSequence | 5行 | dungeon_spy_normal_00001_1〜5 |
| MstInGame | 1行 | dungeon_spy_normal_00001 |

### ゲームプレイ設定

- **BGM**: SSE_SBG_003_002
- **背景アセット**: spy_00005
- **コマ数構成**: 3行（2コマ / 3コマ / 2コマ）
- **敵ウェーブ数**: 5ウェーブ
- **敵種別**:
  - `enemy_spy_00101`: Wave 1（250ms）・Wave 4（3000ms）各1体（サブ敵）
  - `enemy_spy_00001`: Wave 2（800ms）2体・Wave 3（1500ms）3体・Wave 5（4500ms）3体（メイン敵）
