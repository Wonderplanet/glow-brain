# スキーマ調査ガイド

マスタデータのスキーマ調査方法と重要な注意点。

## Sheet Schema vs DB Tables の違い

### Sheet Schema

- **場所**: `projects/glow-masterdata/sheet_schema/`
- **形式**: CSVファイル
- **役割**: プランナーがデータ入力に使うCSVのヘッダ定義
- **例**: `MstAdventBattle.csv` には `id`, `name.ja`, `name.en`, `start_at` などの列

### DB Tables

- **場所**: `projects/glow-server/api/database/schema/exports/master_tables_schema.json`
- **形式**: JSON（テーブル定義）
- **役割**: サーバー側で実際にデータが格納されるテーブル構造
- **例**: `mst_advent_battles` と `mst_advent_battles_i18n` の2つのテーブル

### 重要な違い

**1:1対応ではない**

1つのsheet schemaが複数のDBテーブルに分割されることがある：

```
MstAdventBattle.csv
  ├─> mst_advent_battles (基本情報)
  └─> mst_advent_battles_i18n (多言語情報: name.ja, name.en など)
```

## コードベース調査の優先順位

### 1. sheet_schema CSVファイル（最優先）

**目的**: プランナーが実際に扱うシート定義を確認

```bash
ls projects/glow-masterdata/sheet_schema/ | grep -i "advent"
```

**確認項目**:
- CSVヘッダー（列名）
- 列の順序
- i18nサフィックス（`.ja`, `.en`）の有無

### 2. master_tables_schema.json

**目的**: 実際のDB構造を確認（型、NULL制約など）

```bash
cat projects/glow-server/api/database/schema/exports/master_tables_schema.json | jq '.tables[] | select(.name | contains("advent"))'
```

**確認項目**:
- フィールド型（`string`, `int`, `enum`, `datetime` など）
- NULL許容（`nullable: true/false`）
- デフォルト値
- ENUM型の選択肢（`enum_values`）
- 主キー、外部キー

### 3. Server Code

**目的**: ビジネスロジック、外部キー関係、バリデーションルールを確認

**調査対象**:
- **モデル**: `projects/glow-server/api/app/Models/Master/MstAdventBattle.php`
  - リレーション定義（`hasMany`, `belongsTo`）
  - アクセサ・ミューテータ
- **UseCase**: `projects/glow-server/api/app/UseCases/AdventBattle/`
  - ビジネスロジック
  - バリデーションルール
- **Service**: 報酬計算、スコア計算などのロジック

### 4. Client Code

**目的**: UI表示、使用パターンを確認

**調査対象**:
- **Presenter**: `projects/glow-client/Assets/GLOW/Scripts/Presentation/Presenters/AdventBattle/`
- **ViewModel**: データの表示形式
- **UseCase**: クライアント側のビジネスロジック

## 調査時の確認事項

### 1. シートとテーブルの対応関係

```
MstAdventBattle.csv
  ├─> mst_advent_battles
  └─> mst_advent_battles_i18n

MstAdventBattleRank.csv
  └─> mst_advent_battle_ranks

MstAdventBattleRewardGroup.csv
  └─> mst_advent_battle_reward_groups
```

### 2. 列の詳細情報

| 確認項目 | 調査方法 |
|---------|---------|
| **型** | `master_tables_schema.json` の `type` フィールド |
| **NULL許容** | `master_tables_schema.json` の `nullable` フィールド |
| **デフォルト値** | `master_tables_schema.json` の `default` フィールド |
| **ENUM選択肢** | `master_tables_schema.json` の `enum_values` フィールド |

### 3. ENUM型の選択肢

```json
{
  "name": "advent_battle_type",
  "type": "enum",
  "enum_values": ["ScoreChallenge", "Raid"],
  "nullable": false
}
```

ドキュメントには以下のように記載：

```markdown
| advent_battle_type | 列挙型 |  | バトルタイプ | `ScoreChallenge` または `Raid` |
```

### 4. 外部キー関係

**master_tables_schema.json** から外部キーを確認：

```json
{
  "name": "mst_advent_battle_id",
  "type": "string",
  "nullable": false,
  "comment": "降臨バトルID（外部キー: mst_advent_battles.id）"
}
```

**サーバーコード（モデル）** からリレーションを確認：

```php
// MstAdventBattleRank.php
public function adventBattle()
{
    return $this->belongsTo(MstAdventBattle::class, 'mst_advent_battle_id');
}
```

### 5. 実際の使用例

既存のマスタデータCSVファイルを確認：

```bash
# 既存のマスタデータ例を確認（存在する場合）
cat projects/glow-masterdata/master/MstAdventBattle.csv | head -5
```

## 調査コマンド例

### sheet_schemaの検索

```bash
# コンテンツ名に関連するCSVファイルを検索
find projects/glow-masterdata/sheet_schema/ -iname "*advent*"
```

### master_tables_schema.jsonの検索

```bash
# テーブル名に関連する定義を抽出
cat projects/glow-server/api/database/schema/exports/master_tables_schema.json \
  | jq '.tables[] | select(.name | contains("advent_battle"))'
```

### サーバーコードの検索

```bash
# モデルファイルを検索
find projects/glow-server/api/app/Models/Master/ -iname "*AdventBattle*"

# UseCase を検索
find projects/glow-server/api/app/UseCases/ -iname "*AdventBattle*"
```

### クライアントコードの検索

```bash
# Presenterを検索
find projects/glow-client/Assets/GLOW/Scripts/Presentation/Presenters/ -iname "*AdventBattle*"
```

## よくある落とし穴

### 1. i18nテーブルを独立したCSVだと誤解する

**誤解**: `mst_advent_battles_i18n` というテーブルがあるから、`MstAdventBattleI18n.csv` というCSVが必要

**正解**: `MstAdventBattle.csv` に `name.ja`, `name.en` サフィックスで記載すれば、自動的に `_i18n` テーブルに分割される

### 2. ENABLE/release_keyをプランナーに説明する

**誤解**: ENABLE, release_key はDBにあるから、ドキュメントに含める必要がある

**正解**: これらはシステム管理用フィールドであり、プランナーが設定する必要はない。ドキュメントから除外する

### 3. 外部キーの存在確認を忘れる

**誤解**: `mst_event_id` というフィールドがあれば、そのまま設定例に記載

**正解**: `MstEvent` テーブルに該当するIDが存在するか確認し、実際に存在するIDを設定例に記載

### 4. スコープが広すぎる調査

**誤解**: 「降臨バトル」のドキュメントなのに、全てのイベント関連テーブルを調査してしまう

**正解**: コンテンツに直接関連するテーブルのみを調査し、間接的な関連（例: MstEvent）は外部参照として簡潔に記載
