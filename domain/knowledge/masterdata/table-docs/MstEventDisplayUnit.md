# MstEventDisplayUnit 詳細説明

> CSVパス: `projects/glow-masterdata/MstEventDisplayUnit.csv`
> i18n CSVパス: `projects/glow-masterdata/MstEventDisplayUnitI18n.csv`

---

## 概要

`MstEventDisplayUnit` は**イベントTOP画面に表示するキャラクター（ユニット）とそのセリフを定義するテーブル**。

イベントクエストの各クエスト画面（1日クエスト・キャラゲット・チャレンジ・サベージなど）では、クエスト名の下にそのクエストに関連するユニット（キャラクター）のサムネイルとセリフ吹き出しを表示する。このテーブルでは「どのクエストにどのキャラを表示するか」と対応するセリフを管理する。

`mst_event_display_units_i18n` テーブルでセリフの多言語対応を行う。

CSVの行数は107件（本体・i18nともに同数。2026年3月現在）。

### ゲームプレイへの影響

- **クエスト画面のビジュアル**: イベントTOP画面でプレイヤーがクエストを選ぶ際に、各クエストを象徴するキャラクターとそのセリフが表示される
- **複数ユニット表示**: 1つのクエストに複数のユニットを設定することができ（`mst_quest_id` が同一の複数レコード）、クエストの内容に応じた複数キャラを並べて表示できる
- **`speech_balloon_text1〜3`**: 最大3つの吹き出しセリフを設定可能。実データではほぼ `text1` のみ使用

### 関連テーブルとの構造図

```
MstEventDisplayUnit（イベント表示ユニット）
  ├─ mst_quest_id → MstQuest.id（表示先クエスト）
  ├─ mst_unit_id → MstUnit.id（表示するユニット）
  └─ id → MstEventDisplayUnitI18n.mst_event_display_unit_id（多言語セリフ）

MstQuest（クエスト）
  └─ クエストID でイベントを特定できる（例: quest_event_kai1_charaget01 → 怪獣8号イベント第1回 キャラゲットクエスト01）
```

---

## 全カラム一覧

### mst_event_display_units カラム一覧

| カラム名 | 型 | NULL許容 | デフォルト値 | 説明 |
|---------|----|----|------|------|
| `ENABLE` | varchar | - | - | CSVの有効フラグ。`e` = 有効 |
| `id` | varchar(255) | 不可 | - | 主キー。`{mst_quest_id}{連番}` 形式 |
| `release_key` | bigint | 不可 | 1 | リリースキー。マスタデータのバージョン管理に使用 |
| `mst_quest_id` | varchar(255) | 不可 | "" | 参照先クエストID（`mst_quests.id`）。1クエストに複数ユニットを設定可能 |
| `mst_unit_id` | varchar(255) | 不可 | "" | 参照先ユニットID（`mst_units.id`）。表示するキャラクター |

### MstEventDisplayUnitI18n カラム一覧

| カラム名 | 型 | NULL許容 | デフォルト値 | 説明 |
|---------|----|----|------|------|
| `ENABLE` | varchar | - | - | CSVの有効フラグ。`e` = 有効 |
| `id` | varchar(255) | 不可 | - | 主キー |
| `release_key` | bigint | 不可 | 1 | リリースキー |
| `mst_event_display_unit_id` | varchar(255) | 不可 | "" | 参照先表示ユニットID（`mst_event_display_units.id`） |
| `language` | enum | 不可 | - | 言語コード。`ja` のみ対応 |
| `speech_balloon_text1` | varchar(255) | 不可 | "" | 吹き出しセリフ1（改行は `\n`）。ほぼ必ず設定 |
| `speech_balloon_text2` | varchar(255) | 不可 | "" | 吹き出しセリフ2（省略時は空文字またはNULL） |
| `speech_balloon_text3` | varchar(255) | 不可 | "" | 吹き出しセリフ3（省略時は空文字またはNULL） |

---

## 命名規則 / IDの生成ルール

| 項目 | 規則 | 例 |
|------|------|----|
| `id` | `{mst_quest_id}{1始まり連番（同一クエスト内）}` | `quest_event_kai1_charaget011`, `quest_event_kai1_charaget012` |
| `mst_quest_id` | `quest_event_{作品略称}{回数}_{種別}` | `quest_event_kai1_charaget01`, `quest_event_spy1_1day` |
| i18n の `id` | `{display_unit_id}_ja` | `quest_event_kai1_charaget011_ja` |

### クエスト種別パターン

| 種別文字列 | 意味 |
|-----------|------|
| `_1day` | 1日クエスト |
| `_charaget01`, `_charaget02` | キャラゲットクエスト（番号は難易度段階） |
| `_challenge01` | チャレンジクエスト |
| `_savage` | サベージクエスト（高難易度） |

---

## 他テーブルとの連携

| テーブル | 参照方向 | 用途 |
|---------|---------|------|
| `mst_event_display_units_i18n` | `id` ← `mst_event_display_unit_id` | セリフの多言語テキスト |
| `mst_quests` | `mst_quest_id` → `id` | 表示先のクエスト |
| `mst_units` | `mst_unit_id` → `id` | 表示するユニットの定義 |

---

## 実データ例

### パターン1: 1クエストに複数ユニットを表示（怪獣8号キャラゲット01）

```
[MstEventDisplayUnit.csv]
ENABLE, id,                            mst_quest_id,               mst_unit_id,     release_key
e,      quest_event_kai1_charaget011,  quest_event_kai1_charaget01, chara_kai_00101, 202509010
e,      quest_event_kai1_charaget012,  quest_event_kai1_charaget01, chara_kai_00301, 202509010
e,      quest_event_kai1_charaget013,  quest_event_kai1_charaget01, chara_kai_00601, 202509010

[MstEventDisplayUnitI18n.csv]
ENABLE, release_key, id,                               mst_event_display_unit_id,    language, speech_balloon_text1
e,      202509010,   quest_event_kai1_charaget011_ja,  quest_event_kai1_charaget011, ja,       やれるだけ\nやりましょう！
e,      202509010,   quest_event_kai1_charaget012_ja,  quest_event_kai1_charaget012, ja,       犠牲者なんて\n出させない
e,      202509010,   quest_event_kai1_charaget013_ja,  quest_event_kai1_charaget013, ja,       よく見ろや俺の\nこの上腕二頭筋！
```

1つのキャラゲットクエストに3人のキャラクターを表示し、それぞれ異なるセリフを設定。

### パターン2: 1日クエスト（怪獣8号 1日クエスト）

```
[MstEventDisplayUnit.csv]
ENABLE, id,                    mst_quest_id,          mst_unit_id,     release_key
e,      quest_event_kai1_1day, quest_event_kai1_1day, chara_kai_00001, 202509010

[MstEventDisplayUnitI18n.csv]
ENABLE, release_key, id,                       mst_event_display_unit_id, language, speech_balloon_text1
e,      202509010,   quest_event_kai1_1day_ja, quest_event_kai1_1day,     ja,       内臓まで\nお見通しだぜ!!
```

1日クエストは1つのクエストに対してユニット1体のシンプルな設定。

---

## 設定時のポイント

1. **`id` は `{mst_quest_id}{連番}` 形式を使用する**。同一 `mst_quest_id` 内の連番は1から始まり、1クエストに1つのユニットしかない場合でも末尾に `1` を付ける（例: `quest_event_kai1_1day1` ではなく `quest_event_kai1_1day` が実データのパターンで例外もある）。実データのID命名を参照して整合性を保つこと。

2. **同一 `mst_quest_id` に複数ユニットを設定する場合はIDの連番を使い分ける**。`quest_event_kai1_charaget011`, `quest_event_kai1_charaget012` のように `mst_quest_id` の末尾に `1`, `2` と連番を足す。

3. **i18n レコードは `MstEventDisplayUnit` の各レコードに対して1対1で作成する**。`id`と`mst_event_display_unit_id`の対応に注意し、`MstEventDisplayUnit.id` = `i18n.mst_event_display_unit_id` となる必要がある。

4. **`speech_balloon_text1` は必ず設定し、`text2`・`text3` は省略可能**。実データではほぼ全件 `text1` のみ使用しており、`text2`・`text3` は未使用（NULL）。

5. **セリフの改行は `\n` で表現する**。1〜2行で収まる短いセリフを設定する。キャラクターの台詞らしい口調を維持することが重要。

6. **イベント追加時は全クエスト種別分のユニット設定が必要**。1日クエスト・キャラゲット・チャレンジ・サベージなど、イベントに含まれる全クエストに対して設定を行うこと。
