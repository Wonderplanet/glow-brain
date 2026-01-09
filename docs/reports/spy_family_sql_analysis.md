# SPY×FAMILY マスタデータ SQL分析レポート

## 📌 概要

このレポートは、DuckDBを使用して複数のマスタデータテーブルをJOINし、SPY×FAMILYのキャラクターとイベントの詳細分析を行った結果をまとめたものです。

---

## 🔍 使用したクエリと分析結果

### Query 1: シリーズ統計（キャラクター数とレアリティ・役割分布）

**実行したSQL:**
```sql
SELECT 
  s.id as series_id,
  s.asset_key as series_name,
  COUNT(u.id) as total_characters,
  COUNT(CASE WHEN u.rarity = 'UR' THEN 1 END) as ur_count,
  COUNT(CASE WHEN u.rarity = 'SR' THEN 1 END) as sr_count,
  COUNT(CASE WHEN u.role_type = 'Attack' THEN 1 END) as attack_count,
  COUNT(CASE WHEN u.role_type = 'Defense' THEN 1 END) as defense_count,
  COUNT(CASE WHEN u.role_type = 'Special' THEN 1 END) as special_count
FROM read_csv('projects/glow-masterdata/MstSeries.csv', AUTO_DETECT=TRUE, nullstr='__NULL__') s
LEFT JOIN read_csv('projects/glow-masterdata/MstUnit.csv', AUTO_DETECT=TRUE, nullstr='__NULL__') u
  ON s.id = u.mst_series_id AND u.ENABLE = 'e'
WHERE s.ENABLE = 'e' AND s.id = 'spy'
GROUP BY s.id, s.asset_key;
```

**結果:**

| series_id | series_name | total_characters | ur_count | sr_count | attack_count | defense_count | special_count |
|-----------|-------------|-----------------:|---------:|---------:|-------------:|--------------:|--------------:|
| spy       | spy         | 6                | 4        | 2        | 2            | 2             | 2             |

**分析:**
- SPY×FAMILYシリーズには全6キャラクターが登録されている
- URとSRの比率は 4:2（67%:33%）
- アタック、ディフェンス、特殊サポートが各2キャラずつとバランスが良い

---

### Query 2: イベント詳細（シリーズ情報との結合）

**実行したSQL:**
```sql
SELECT 
  e.id as event_id,
  e.start_at,
  e.end_at,
  DATEDIFF('day', CAST(e.start_at AS TIMESTAMP), CAST(e.end_at AS TIMESTAMP)) as duration_days,
  s.asset_key as series_name,
  e.is_displayed_series_logo,
  e.is_displayed_jump_plus
FROM read_csv('projects/glow-masterdata/MstEvent.csv', AUTO_DETECT=TRUE, nullstr='__NULL__') e
JOIN read_csv('projects/glow-masterdata/MstSeries.csv', AUTO_DETECT=TRUE, nullstr='__NULL__') s
  ON e.mst_series_id = s.id
WHERE e.ENABLE = 'e' AND s.id = 'spy';
```

**結果:**

|    event_id     |      start_at       |       end_at        | duration_days | series_name | is_displayed_series_logo | is_displayed_jump_plus |
|-----------------|---------------------|---------------------|--------------:|-------------|-------------------------:|-----------------------:|
| event_spy_00001 | 2025-10-06 15:00:00 | 2025-11-06 14:59:59 | 31            | spy         | 1                        | 1                      |

**分析:**
- イベント期間は31日間（約1ヶ月）
- シリーズロゴとジャンプ+の表示が両方有効
- 期間が長めに設定されており、じっくり育成できる設計

---

### Query 3: キャラクターのイベントボーナス対象状況

**実行したSQL:**
```sql
SELECT 
  u.id as character_id,
  u.rarity,
  u.role_type,
  u.color as attribute,
  CASE WHEN b.id IS NOT NULL THEN 'ボーナス対象' ELSE '通常' END as bonus_status,
  b.bonus_percentage
FROM read_csv('projects/glow-masterdata/MstUnit.csv', AUTO_DETECT=TRUE, nullstr='__NULL__') u
LEFT JOIN read_csv('projects/glow-masterdata/MstEventBonusUnit.csv', AUTO_DETECT=TRUE, nullstr='__NULL__') b
  ON u.id = b.mst_unit_id AND b.ENABLE = 'e'
WHERE u.ENABLE = 'e' AND u.mst_series_id = 'spy'
ORDER BY 
  CASE WHEN b.id IS NOT NULL THEN 0 ELSE 1 END,
  u.rarity DESC,
  u.id;
```

**結果:**

|  character_id   | rarity | role_type | attribute | bonus_status | bonus_percentage |
|-----------------|--------|-----------|-----------|--------------|-----------------:|
| chara_spy_00101 | UR     | Attack    | Yellow    | ボーナス対象 | 30               |
| chara_spy_00201 | UR     | Attack    | Red       | ボーナス対象 | 30               |
| chara_spy_00501 | UR     | Defense   | Blue      | ボーナス対象 | 30               |
| chara_spy_00401 | SR     | Defense   | Green     | ボーナス対象 | 30               |
| chara_spy_00001 | UR     | Special   | Colorless | 通常         |                  |
| chara_spy_00301 | SR     | Special   | Colorless | 通常         |                  |

**分析:**
- 4キャラクター（ロイド、ヨル、ユーリ、フランキー）がボーナス対象
- ボーナス倍率は一律30%
- アーニャとダミアンはSpecialタイプのためボーナス対象外
- 戦闘キャラ（Attack/Defense）がボーナス対象になっている

---

### Query 4: イベントミッションとキャラクター詳細

**実行したSQL:**
```sql
SELECT 
  m.id as mission_id,
  m.criterion_type,
  m.criterion_value as target_character,
  m.criterion_count as required_count,
  u.rarity,
  u.role_type,
  m.sort_order
FROM read_csv('projects/glow-masterdata/MstMissionEvent.csv', AUTO_DETECT=TRUE, nullstr='__NULL__') m
LEFT JOIN read_csv('projects/glow-masterdata/MstUnit.csv', AUTO_DETECT=TRUE, nullstr='__NULL__') u
  ON m.criterion_value = u.id
WHERE m.ENABLE = 'e' AND m.mst_event_id = 'event_spy_00001'
ORDER BY m.sort_order;
```

**結果:**

|     mission_id     |      criterion_type      |       target_character       | required_count | rarity | role_type | sort_order |
|--------------------|--------------------------|------------------------------|---------------:|--------|-----------|-----------:|
| event_spy_00001_1  | SpecificUnitGradeUpCount | chara_spy_00401              | 2              | SR     | Defense   | 1          |
| event_spy_00001_2  | SpecificUnitGradeUpCount | chara_spy_00401              | 3              | SR     | Defense   | 2          |
| event_spy_00001_3  | SpecificUnitGradeUpCount | chara_spy_00401              | 4              | SR     | Defense   | 3          |
| event_spy_00001_4  | SpecificUnitGradeUpCount | chara_spy_00401              | 5              | SR     | Defense   | 4          |
| event_spy_00001_5  | SpecificUnitLevel        | chara_spy_00401              | 20             | SR     | Defense   | 5          |
| event_spy_00001_6  | SpecificUnitLevel        | chara_spy_00401              | 30             | SR     | Defense   | 6          |
| event_spy_00001_7  | SpecificUnitLevel        | chara_spy_00401              | 40             | SR     | Defense   | 7          |
| event_spy_00001_8  | SpecificUnitGradeUpCount | chara_spy_00301              | 2              | SR     | Special   | 8          |
| event_spy_00001_9  | SpecificUnitGradeUpCount | chara_spy_00301              | 3              | SR     | Special   | 9          |
| event_spy_00001_10 | SpecificUnitGradeUpCount | chara_spy_00301              | 4              | SR     | Special   | 10         |
| event_spy_00001_11 | SpecificUnitGradeUpCount | chara_spy_00301              | 5              | SR     | Special   | 11         |
| event_spy_00001_12 | SpecificUnitLevel        | chara_spy_00301              | 20             | SR     | Special   | 12         |
| event_spy_00001_13 | SpecificUnitLevel        | chara_spy_00301              | 30             | SR     | Special   | 13         |
| event_spy_00001_14 | SpecificUnitLevel        | chara_spy_00301              | 40             | SR     | Special   | 14         |
| event_spy_00001_15 | SpecificQuestClear       | quest_event_spy1_charaget01  | 1              |        |           | 15         |
| event_spy_00001_16 | SpecificQuestClear       | quest_event_spy1_charaget02  | 1              |        |           | 16         |
| event_spy_00001_17 | SpecificQuestClear       | quest_event_spy1_challenge01 | 1              |        |           | 17         |
| event_spy_00001_18 | SpecificQuestClear       | quest_event_spy1_savage      | 1              |        |           | 18         |
| event_spy_00001_19 | DefeatEnemyCount         |                              | 10             |        |           | 19         |
| event_spy_00001_20 | DefeatEnemyCount         |                              | 20             |        |           | 20         |
| event_spy_00001_21 | DefeatEnemyCount         |                              | 30             |        |           | 21         |
| event_spy_00001_22 | DefeatEnemyCount         |                              | 40             |        |           | 22         |
| event_spy_00001_23 | DefeatEnemyCount         |                              | 50             |        |           | 23         |
| event_spy_00001_24 | DefeatEnemyCount         |                              | 100            |        |           | 24         |

**分析:**
- **重要な発見**: イベントミッションは24個あった（初期レポートでは5個のみ記載）
- フランキー（chara_spy_00401）のミッション: 7個（グレードアップ4段階 + レベル3段階）
- ダミアン（chara_spy_00301）のミッション: 7個（グレードアップ4段階 + レベル3段階）
- クエストクリアミッション: 4個
- 敵撃破数ミッション: 6個（10/20/30/40/50/100体）
- SRキャラ2体を段階的に育成する設計

---

### Query 5: キャラクター戦闘力ランキング

**実行したSQL:**
```sql
SELECT 
  u.id,
  u.rarity,
  u.role_type,
  u.max_attack_power,
  u.max_hp,
  RANK() OVER (ORDER BY u.max_attack_power DESC) as attack_rank,
  RANK() OVER (ORDER BY u.max_hp DESC) as hp_rank,
  CASE WHEN b.id IS NOT NULL THEN '★ボーナス' ELSE '' END as bonus_mark
FROM read_csv('projects/glow-masterdata/MstUnit.csv', AUTO_DETECT=TRUE, nullstr='__NULL__') u
LEFT JOIN read_csv('projects/glow-masterdata/MstEventBonusUnit.csv', AUTO_DETECT=TRUE, nullstr='__NULL__') b
  ON u.id = b.mst_unit_id AND b.ENABLE = 'e'
WHERE u.ENABLE = 'e' AND u.mst_series_id = 'spy' AND u.role_type != 'Special'
ORDER BY u.max_attack_power DESC;
```

**結果:**

|       id        | rarity | role_type | max_attack_power | max_hp | attack_rank | hp_rank | bonus_mark |
|-----------------|--------|-----------|-----------------:|-------:|------------:|--------:|------------|
| chara_spy_00101 | UR     | Attack    | 96000            | 19700  | 1           | 3       | ★ボーナス  |
| chara_spy_00201 | UR     | Attack    | 31700            | 15600  | 2           | 4       | ★ボーナス  |
| chara_spy_00401 | SR     | Defense   | 9400             | 28200  | 3           | 2       | ★ボーナス  |
| chara_spy_00501 | UR     | Defense   | 4200             | 35600  | 4           | 1       | ★ボーナス  |

**分析:**
- **攻撃力1位**: ロイド（96,000）- 圧倒的な火力
- **攻撃力2位**: ヨル（31,700）- ロイドの約1/3
- **HP1位**: ユーリ（35,600）- 最も耐久力が高い
- **HP2位**: フランキー（28,200）
- ロイドは攻撃特化、ユーリは防御特化の設計
- 全てのバトルキャラがボーナス対象になっている

---

## 📊 重要な発見のまとめ

### 1. ミッション数の訂正
- **初期レポート**: 5ミッション
- **実際**: 24ミッション
  - フランキー育成: 7ミッション
  - ダミアン育成: 7ミッション
  - クエストクリア: 4ミッション
  - 敵撃破数: 6ミッション

### 2. キャラクター戦闘力の特徴
- ロイドの攻撃力（96,000）は他を圧倒
- ユーリのHP（35,600）が最高で防御特化
- SRキャラ（フランキー、ダミアン）もイベントで育成可能

### 3. イベント設計の考察
- SR2体（フランキー、ダミアン）の育成を促進
- 31日間の長期イベントでじっくり育成できる
- ボーナスキャラは戦闘キャラ（Attack/Defense）のみ

---

## 🔧 DuckDBクエリの使い方

### 前提条件
```bash
# DuckDBのインストール（未インストールの場合）
brew install duckdb  # macOS
# または
wget https://github.com/duckdb/duckdb/releases/download/v1.1.3/duckdb_cli-linux-amd64.zip
unzip duckdb_cli-linux-amd64.zip
sudo mv duckdb /usr/local/bin/
```

### クエリ実行方法

```bash
# glow-brainルートディレクトリに移動
cd /path/to/glow-brain

# 対話モードで起動
duckdb -init .claude/skills/masterdata-explorer/.duckdbrc

# またはワンライナーで実行
duckdb -init .claude/skills/masterdata-explorer/.duckdbrc -markdown -c "SELECT * FROM read_csv('projects/glow-masterdata/MstUnit.csv', AUTO_DETECT=TRUE, nullstr='__NULL__') WHERE ENABLE = 'e' AND mst_series_id = 'spy';"
```

### カラム名の事前確認（重要）

DuckDBクエリを書く前に、必ずカラム名を確認してください：

```bash
# テーブルのカラム一覧を確認
.claude/skills/masterdata-explorer/scripts/search_schema.sh columns mst_units
.claude/skills/masterdata-explorer/scripts/search_schema.sh columns mst_events
.claude/skills/masterdata-explorer/scripts/search_schema.sh columns mst_mission_events
```

---

## 📚 参考資料

- **スキル**: `masterdata-explorer`
- **クエリ例集**: `.claude/skills/masterdata-explorer/references/duckdb-query-examples.md`
- **スキーマリファレンス**: `.claude/skills/masterdata-explorer/references/schema-reference.md`
- **DuckDB公式ドキュメント**: https://duckdb.org/docs/

---

**レポート作成日**: 2026-01-09  
**使用ツール**: DuckDB 1.1.3, masterdata-explorer skill  
**データソース**: glow-masterdata CSVファイル群
