# ドキュメント記述ルール

非エンジニア（プランナー）向けマスタデータ設定方法ドキュメントの記述ルール。

## 型の表記

DBスキーマの型を非エンジニア向けに変換：

| DBスキーマ型 | ドキュメント表記 |
|------------|---------------|
| `string`, `varchar`, `text` | **文字列** |
| `int`, `integer`, `bigint` | **整数** |
| `decimal`, `float`, `double` | **少数** |
| `datetime`, `timestamp` | **日時** |
| `enum` | **列挙型**（選択肢を併記） |
| `boolean` | **真偽値** |

### ENUM型の記載例

```markdown
| 列名 | 型 | NULL許容 | 説明 | 設定例 |
|------|-----|---------|------|--------|
| **advent_battle_type** | 列挙型 |  | バトルタイプ | `ScoreChallenge` または `Raid` |
| **reward_category** | 列挙型 |  | 報酬カテゴリー | `MaxScore`, `Ranking`, `Rank`, `RaidTotalScore`, `Drop` |
```

## NULL許容の表記

- **nullable**: ○
- **not null**: （空白）

### 例

```markdown
| 列名 | 型 | NULL許容 | 説明 |
|------|-----|---------|------|
| **id** | 文字列 |  | 一意キー（必須） |
| **description** | 文字列 | ○ | 説明（任意） |
```

## 除外フィールド

以下のフィールドはドキュメントに含めない：

- `ENABLE`
- `release_key`
- `created_at`
- `updated_at`

これらはシステム管理用のフィールドであり、プランナーが設定する必要がない。

## i18n（多言語）フィールド

### 仕組み

- `.ja`, `.en` などのサフィックス付きフィールドは自動的に `_i18n` テーブルに格納される
- sheet_schemaには `_i18n` 用の独立したCSVは存在しない
- 1つのシートから複数のDBテーブルに分割される

### 記載方法

```markdown
| 列名 | 型 | NULL許容 | 説明 | 設定例 |
|------|-----|---------|------|--------|
| **name.ja** | 文字列 |  | 名前（日本語） | `夏の試練！灼熱の竜` |
| **name.en** | 文字列 |  | 名前（英語） | `Summer Trial! Blazing Dragon` |
```

**注意**: DBテーブルとして `mst_xxx_i18n` が存在する場合でも、プランナーは親テーブルのCSVに `.ja`, `.en` サフィックスで記載するだけで良い。

## 設定例の表記

### 必須ルール

1. **CSV形式ではなく、Markdownテーブル形式**で記載
2. 各列に対応する値が一目で分かるように
3. `ENABLE`, `release_key` 列は除外
4. 実際の設定を想定した具体例を記載

### 良い例

```markdown
### MstAdventBattle

| id | mst_event_id | mst_in_game_id | advent_battle_type | time_limit_seconds | start_at | end_at | name.ja |
|-----|--------------|----------------|-------------------|-------------------|----------|--------|---------|
| advent_battle_summer_2025 | event_summer_2025 | advent_summer_stage | ScoreChallenge | 180 | 2025-07-01 15:00:00 | 2025-07-15 14:59:59 | 夏の試練！灼熱の竜 |
```

### 悪い例（避けるべき）

```csv
id,mst_event_id,mst_in_game_id,ENABLE,release_key
advent_001,event_001,stage_001,1,global
```

理由:
- CSV形式で読みにくい
- `ENABLE`, `release_key` が含まれている
- 列の意味が分かりにくい

## 外部キー参照の記載

外部キーは明示的に「（外部キー）」または「（外部参照）」と記載：

```markdown
| 列名 | 型 | NULL許容 | 説明 | 設定例 |
|------|-----|---------|------|--------|
| **mst_event_id** | 文字列 |  | イベントID（外部参照） | `event_summer_2025` |
| **mst_advent_battle_id** | 文字列 |  | 降臨バトルID（外部キー） | `advent_battle_summer_2025` |
```

## 日時フォーマット

日時は常にJSTで記載：

```
YYYY-MM-DD HH:MM:SS
```

### 例

```markdown
| start_at | end_at |
|----------|--------|
| 2025-07-01 15:00:00 | 2025-07-15 14:59:59 |
```

## 注意事項セクションの構成

チェックリスト形式で記載：

```markdown
## 注意事項とチェックポイント

### 必須確認事項

#### 1. IDの一意性

- [ ] すべてのテーブルで `id` フィールドは一意であること
- [ ] 他のイベント・コンテンツとIDが重複しないこと

#### 2. 外部キーの整合性

- [ ] `mst_event_id` がMstEventの `id` と一致すること
- [ ] `resource_id` が対応するマスタテーブルに存在すること
```
