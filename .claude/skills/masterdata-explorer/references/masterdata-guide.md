# GLOWマスタデータ詳細ガイド

このドキュメントでは、GLOWプロジェクトのマスタデータの構造、形式、運用の概念について説明します。

## マスタデータとは

GLOWプロジェクトでは、ゲームの基礎データ（キャラクター、アイテム、イベントなど）をマスタデータとして管理しています。

### 特徴

- **CSV形式**: すべてのマスタデータはCSVファイルとして管理
- **バージョン管理**: Gitでバージョン管理され、リリースごとに更新
- **データベース投入**: CSVファイルはサーバー起動時にデータベースに投入される
- **型安全**: DBスキーマで型とenum値が厳密に定義されている

### データ量

```bash
# glow-masterdataディレクトリ内
$ ls projects/glow-masterdata/*.csv | wc -l
192

# 主要なマスタデータ
- MstUnit.csv         # キャラクターユニット
- MstSeries.csv       # シリーズ
- MstEvent.csv        # イベント
- MstStage.csv        # ステージ
- MstItem.csv         # アイテム
- MstGacha.csv        # ガチャ
- ... 他186個
```

---

## 3つのデータソースの詳細

### 1. DBスキーマ（master_tables_schema.json）

**パス**: `projects/glow-server/api/database/schema/exports/master_tables_schema.json`

**役割**: テーブル構造の「設計図」

**内容**:
- テーブル定義（カラム名、型、NULL可否）
- enum値の定義（許可される値のリスト）
- インデックス定義
- コメント（日本語の説明）

**確認方法**:
```bash
# テーブル一覧
.claude/skills/masterdata-explorer/scripts/search_schema.sh tables event

# カラム定義確認
.claude/skills/masterdata-explorer/scripts/search_schema.sh columns mst_events

# enum値確認
.claude/skills/masterdata-explorer/scripts/search_schema.sh enum mst_events event_category
```

**使用場面**:
- 新しいマスタデータを作成する前に、どのカラムが必須かを確認
- enum値の許可値を確認
- カラムの型を確認（string, integer, datetimeなど）

---

### 2. CSVテンプレート（sheet_schema/）

**パス**: `projects/glow-masterdata/sheet_schema/`

**役割**: マスタデータ作成時の「ひな形」

**内容**:
- 列の順序が定義されたヘッダー行のみのCSV
- 実データは含まれない（ヘッダーのみ）

**例**:
```csv
id,asset_key,mst_series_id,rarity,max_hp,max_attack_power,...,ENABLE
```

**確認方法**:
```bash
# ヘッダー行を確認
head -1 projects/glow-masterdata/sheet_schema/MstUnit.csv
```

**使用場面**:
- 新しいマスタデータCSVを作成するときのテンプレート
- カラムの順序を確認（CSVは順序が重要）
- 必須カラムの有無を確認

---

### 3. 既存マスタデータCSV（実データ）

**パス**: `projects/glow-masterdata/*.csv`

**役割**: 過去に作成・投入済みのマスタデータ

**内容**:
- 実際にゲームで使用されているデータ
- `ENABLE = 'e'`が有効なレコード
- `__NULL__`文字列でNULL値を表現

**確認方法**:
```bash
# 最初の5行を確認
head -5 projects/glow-masterdata/MstUnit.csv

# 有効なレコード数をカウント
grep -c ",e$" projects/glow-masterdata/MstUnit.csv

# DuckDBで分析
duckdb -init .claude/skills/masterdata-explorer/.duckdbrc
```

**使用場面**:
- 既存データのパターンを参考にする
- 類似データのコピー元として使用
- データの整合性を確認（他のテーブルとのリレーション）

---

## データ形式の詳細

### ENABLE列

すべてのマスタデータテーブルには`ENABLE`列が存在します。

**値**:
- `'e'` - 有効（enabled）
- `''` - 無効（disabled）

**用途**:
- データを削除せずに無効化できる
- 過去のデータを残しつつ、現在使用中のデータのみを抽出

**使用例**:
```sql
-- 有効なレコードのみを取得
SELECT * FROM read_csv('projects/glow-masterdata/MstUnit.csv', AUTO_DETECT=TRUE, nullstr='__NULL__')
WHERE ENABLE = 'e';
```

---

### __NULL__表現

CSVファイルではNULL値を`__NULL__`文字列で表現します。

**理由**:
- CSV形式では空文字列とNULLの区別が難しい
- 明示的に`__NULL__`とすることで意図が明確になる

**DuckDBでの処理**:
```sql
-- nullstr='__NULL__'オプションで自動変換
read_csv('projects/glow-masterdata/MstUnit.csv', AUTO_DETECT=TRUE, nullstr='__NULL__')
```

**使用例**:
```sql
-- NULL値のカラムをカウント
SELECT COUNT(*) - COUNT(mst_unit_ability_id1) as null_count
FROM read_csv('projects/glow-masterdata/MstUnit.csv', AUTO_DETECT=TRUE, nullstr='__NULL__');
```

---

## テーブル命名規則の詳細

### プレフィックスの意味

#### mst_*, opr_* (Master)

**用途**: マスタデータ

**特徴**:
- ゲームの基礎データ
- 頻繁には変更されない
- リリース単位で更新

**例**:
- `mst_units` - キャラクターユニット
- `mst_items` - アイテム
- `mst_series` - シリーズ
- `mst_stages` - ステージ
- `mst_abilities` - アビリティ
- `opr_gachas` - ガチャ
- `opr_campaigns` - キャンペーン

### DBスキーマ名とCSVファイル名の対応

| DBスキーマ名 | CSVファイル名 | 備考 |
|-------------|--------------|------|
| `mst_units` | `MstUnit.csv` | 複数形 → 単数形 |
| `mst_events` | `MstEvent.csv` | |
| `mst_series` | `MstSeries.csv` | 不変名詞（複数形同形） |
| `mst_abilities_i18n` | `MstAbilityI18n.csv` | アンダースコア → PascalCase |
| `opr_gachas` | `OprGacha.csv` | |

---

## よくある調査パターン

### パターン1: 新しいマスタデータを作成する前の調査

1. **DBスキーマでテーブル構造を確認**
   ```bash
   .claude/skills/masterdata-explorer/scripts/search_schema.sh columns mst_units
   ```

2. **enum値の確認**
   ```bash
   .claude/skills/masterdata-explorer/scripts/search_schema.sh enum mst_units rarity
   ```

3. **既存データを参考にする**
   ```bash
   head -10 projects/glow-masterdata/MstUnit.csv
   ```

4. **CSVテンプレートを確認**
   ```bash
   head -1 projects/glow-masterdata/sheet_schema/MstUnit.csv
   ```

### パターン2: 既存データの整合性確認

1. **外部キー的な関連を確認**
   ```sql
   -- ユニットが参照しているシリーズが存在するか
   SELECT u.id, u.mst_series_id
   FROM read_csv('projects/glow-masterdata/MstUnit.csv', AUTO_DETECT=TRUE, nullstr='__NULL__') u
   LEFT JOIN read_csv('projects/glow-masterdata/MstSeries.csv', AUTO_DETECT=TRUE, nullstr='__NULL__') s
     ON u.mst_series_id = s.id
   WHERE u.ENABLE = 'e' AND s.id IS NULL;
   ```

2. **重複IDの確認**
   ```sql
   SELECT id, COUNT(*) as count
   FROM read_csv('projects/glow-masterdata/MstUnit.csv', AUTO_DETECT=TRUE, nullstr='__NULL__')
   GROUP BY id
   HAVING count > 1;
   ```

### パターン3: データ分布の確認

1. **レア度別の分布**
   ```sql
   SELECT rarity, COUNT(*) as count
   FROM read_csv('projects/glow-masterdata/MstUnit.csv', AUTO_DETECT=TRUE, nullstr='__NULL__')
   WHERE ENABLE = 'e'
   GROUP BY rarity
   ORDER BY count DESC;
   ```

2. **シリーズ別のキャラクター数**
   ```sql
   SELECT s.asset_key, COUNT(u.id) as unit_count
   FROM read_csv('projects/glow-masterdata/MstSeries.csv', AUTO_DETECT=TRUE, nullstr='__NULL__') s
   LEFT JOIN read_csv('projects/glow-masterdata/MstUnit.csv', AUTO_DETECT=TRUE, nullstr='__NULL__') u
     ON s.id = u.mst_series_id AND u.ENABLE = 'e'
   WHERE s.ENABLE = 'e'
   GROUP BY s.asset_key
   ORDER BY unit_count DESC;
   ```

### パターン4: 期間限定データの確認

1. **現在開催中のイベント**
   ```sql
   SELECT id, asset_key, start_at, end_at
   FROM read_csv('projects/glow-masterdata/MstEvent.csv', AUTO_DETECT=TRUE, nullstr='__NULL__')
   WHERE ENABLE = 'e'
     AND start_at <= CURRENT_TIMESTAMP
     AND end_at >= CURRENT_TIMESTAMP;
   ```

2. **今後開催予定のイベント**
   ```sql
   SELECT id, asset_key, start_at, end_at
   FROM read_csv('projects/glow-masterdata/MstEvent.csv', AUTO_DETECT=TRUE, nullstr='__NULL__')
   WHERE ENABLE = 'e'
     AND start_at > CURRENT_TIMESTAMP
   ORDER BY start_at;
   ```

---

## このスキルは参照専用

**重要**: masterdata-explorerスキルは**参照・調査専用**です。

**できること**:
- ✅ マスタデータの内容を確認
- ✅ データ構造を調査
- ✅ 整合性をチェック
- ✅ 統計データを取得

**できないこと**:
- ❌ マスタデータCSVの作成
- ❌ マスタデータCSVの編集
- ❌ データベースへの投入
- ❌ バリデーション実行

**マスタデータを作成したい場合**:
- `masterdata-csv-validator`スキル（別スキル）を使用してください
- これはバリデーション機能を含む作成・編集用のスキルです

---

## 関連ドキュメント

- **[SKILL.md](../SKILL.md)** - このスキルの基本的な使い方
- **[schema-reference.md](schema-reference.md)** - jqパターンとスキーマ構造の詳細
- **[duckdb-query-examples.md](duckdb-query-examples.md)** - DuckDBクエリパターン集（20+例）
