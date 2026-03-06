# MstQuest 詳細説明

> CSVパス: `projects/glow-masterdata/MstQuest.csv`
> i18n CSVパス: `projects/glow-masterdata/MstQuestI18n.csv`

---

## 概要

`MstQuest` は**ゲーム内のクエスト（コンテンツ枠）の定義テーブル**。クエストは1つ以上のステージ（`MstStage`）を持つ容れ物であり、クエストの種類・難易度・公開期間・グループ化を管理する。

`mst_quests_i18n` はクエスト名・カテゴリ名・フレーバーテキストの多言語対応テーブル。

### ゲームへの影響

- **クエスト種別** (`quest_type`) によってゲームの進行フローが分岐する。メインストーリー系のクエストは `Normal`、期間限定イベントは `Event`、強化クエスト（コイン・EXP獲得）は `Enhance`、チュートリアルは `Tutorial`。
- **難易度** (`difficulty`) は `Normal` / `Hard` / `Extra` の3段階。同一 `quest_group` に属するクエストが同じ漫画作品のノーマル・ハード・エクストラとして表示される。
- **公開期間** (`start_date` / `end_date`) でプレイ可能な期間を制御する。期間外はロックされる。
- **`quest_group`** により、同一漫画作品の複数難易度クエストをまとめてUI上で1グループとして表示できる。

### テーブル連携図

```
MstQuest（クエスト枠）
  ├─ id → MstQuestI18n.mst_quest_id（多言語テキスト）
  ├─ id → MstStage.mst_quest_id（1:N、ステージ一覧）
  ├─ id → MstQuestBonusUnit.mst_quest_id（ボーナスユニット設定）
  ├─ id → MstQuestEventBonusSchedule.mst_quest_id（イベントボーナス設定）
  └─ mst_event_id → MstEvent.id（イベントへの参照、Event型のみ）
```

---

## 全カラム一覧

### mst_quests（本体テーブル）

| カラム名 | 型 | NULL許容 | デフォルト値 | 説明 |
|---------|----|---------|-----------|----|
| `ENABLE` | varchar | - | - | CSVの有効フラグ。`e` = 有効 |
| `id` | varchar(255) | 不可 | - | 主キー（UUID形式または意味のある文字列） |
| `quest_type` | enum | 不可 | - | クエスト種別。`Normal` / `Event` / `Enhance` / `Tutorial` |
| `mst_event_id` | varchar(255) | 可（NULL） | - | 紐づくイベントID（`mst_events.id`）。Event種別のみ使用 |
| `mst_series_id` | varchar(255) | 不可 | `""` | 紐づくシリーズID（`mst_series.id`） |
| `sort_order` | int | 不可 | 0 | クエスト一覧の表示順 |
| `asset_key` | varchar(255) | 不可 | - | クエストのアセットキー |
| `start_date` | timestamp | 不可 | - | クエスト公開開始日時 |
| `end_date` | timestamp | 不可 | - | クエスト公開終了日時 |
| `release_key` | bigint | 不可 | 1 | リリースキー |
| `quest_group` | varchar(255) | 可（NULL） | - | 難易度グループのまとめキー。同一作品の複数難易度をグループ化 |
| `difficulty` | enum | 不可 | `Normal` | 難易度。`Normal` / `Hard` / `Extra` |

### MstQuestI18n カラム一覧

| カラム名 | 型 | NULL許容 | デフォルト値 | 説明 |
|---------|----|---------|-----------|----|
| `ENABLE` | varchar | - | - | CSVの有効フラグ。`e` = 有効 |
| `id` | varchar(255) | 不可 | - | 主キー |
| `mst_quest_id` | varchar(255) | 不可 | - | 対応するクエストID（`mst_quests.id`） |
| `language` | varchar(255) | 不可 | - | 言語コード（例: `ja`） |
| `name` | varchar(255) | 不可 | - | クエスト名（例: `SPY×FAMILY`） |
| `category_name` | varchar(255) | 不可 | `""` | カテゴリ名（UI上の補足表示） |
| `flavor_text` | varchar(255) | 不可 | - | クエスト説明文・フレーバーテキスト |
| `release_key` | int | 不可 | 1 | リリースキー |

---

## QuestType（enum）

| 値 | 説明 |
|----|------|
| `Normal` | 通常のメインクエスト（漫画作品ごとの常設クエスト） |
| `Event` | 期間限定イベントクエスト。`mst_event_id` が必須 |
| `Enhance` | 強化クエスト（コイン獲得・EXP獲得など） |
| `Tutorial` | チュートリアル用クエスト |

## Difficulty（enum）

| 値 | 説明 |
|----|------|
| `Normal` | 通常難易度 |
| `Hard` | ハード難易度 |
| `Extra` | エクストラ難易度（最高難易度） |

---

## 命名規則 / IDの生成ルール

`MstQuest.id` は以下のパターンで命名する:

```
quest_{種別}_{作品略称}_{難易度}_{連番}
```

例:
- `quest_main_spy_normal_1` → SPY×FAMILY のノーマル難易度クエスト1番
- `quest_main_spy_hard_1` → SPY×FAMILY のハード難易度クエスト1番
- `quest_enhance_00001` → 強化クエスト1番
- `tutorial` → チュートリアル

`MstQuestI18n.id` は `{mst_quest_id}_{language}` で命名する。

---

## 他テーブルとの連携

| 連携先テーブル | 結合キー | 用途 |
|-------------|--------|------|
| `MstQuestI18n` | `MstQuestI18n.mst_quest_id = MstQuest.id` | クエスト名・説明文の多言語テキストを取得 |
| `MstStage` | `MstStage.mst_quest_id = MstQuest.id` | クエスト内のステージ一覧を取得 |
| `MstQuestBonusUnit` | `MstQuestBonusUnit.mst_quest_id = MstQuest.id` | コインボーナスユニット設定を取得 |
| `MstQuestEventBonusSchedule` | `MstQuestEventBonusSchedule.mst_quest_id = MstQuest.id` | イベントボーナス期間設定を取得 |
| `MstEvent` | `MstQuest.mst_event_id = MstEvent.id` | イベント情報を参照（Event種別のみ） |
| `MstSeries` | `MstQuest.mst_series_id = MstSeries.id` | 漫画シリーズ情報を取得 |

---

## 実データ例

### パターン1: 通常メインクエスト（SPY×FAMILY、3難易度）

```csv
ENABLE,id,quest_type,mst_event_id,sort_order,asset_key,start_date,end_date,quest_group,difficulty,release_key
e,quest_main_spy_normal_1,normal,,1,spy_1,2025-05-01 12:00:00,2037-12-31 23:59:59,spy1,Normal,202509010
e,quest_main_spy_hard_1,normal,,1,spy_1,2025-05-01 12:00:00,2037-12-31 23:59:59,spy1,Hard,202509010
e,quest_main_spy_veryhard_1,normal,,1,spy_1,2025-05-01 12:00:00,2037-12-31 23:59:59,spy1,Extra,202509010
```

- `quest_group = spy1` で3つの難易度クエストを1グループとしてUI表示
- `sort_order = 1` で一覧の並び順を制御

### パターン2: チュートリアルクエスト

```csv
ENABLE,id,quest_type,mst_event_id,sort_order,asset_key,start_date,end_date,quest_group,difficulty,release_key
e,tutorial,Tutorial,,1,tutorial_1,2024-01-01 00:00:00,2037-12-31 23:59:59,tutorial,Normal,202509010
```

- `quest_type = Tutorial` はチュートリアルフロー専用

### パターン3: i18nレコード（SPY×FAMILY ノーマルクエスト）

```csv
ENABLE,release_key,id,mst_quest_id,language,name,category_name,flavor_text
e,202509010,quest_main_spy_normal_1_ja,quest_main_spy_normal_1,ja,SPY×FAMILY,,『SPY×FAMILY』のクエスト。\n\nこのクエストでは、青属性の敵と攻撃UPコマが登場。
```

---

## 設定時のポイント

1. **`quest_group` は同一作品・複数難易度のクエストに同じ値を設定する**。これによりUI上で1カードにまとまって表示される。
2. **`difficulty` の組み合わせは `quest_group` 内でユニークにする**。同じグループに同一難易度を2つ設定するとUI表示が崩れる。
3. **`mst_event_id` は `quest_type = Event` の場合のみ設定する**。`Normal` / `Enhance` / `Tutorial` では NULL のままにする。
4. **`start_date` / `end_date` は公開期間の管理に使う**。常設クエストは `end_date = 2037-12-31 23:59:59` などの遠い未来日時を設定するのが慣例。
5. **`mst_series_id` は必須カラム**（デフォルト空文字）。クエストが属する漫画作品のシリーズIDを設定する。
6. **i18nレコードは `mst_quest_id_{language}` の命名規則でIDを生成する**。クエスト本体と言語ごとに必ずセットで作成する。
7. **`flavor_text` の改行は `\n` で記述する**。CSV上の実際の改行文字は使用しない。
8. **`sort_order` は作品追加の順番に連番で管理する**。同一 `sort_order` が複数存在すると表示順が不定になる。
